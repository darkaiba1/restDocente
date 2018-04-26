<?php
/**
 *
 Acceder al recurso materias
 * GET
 * localhost/~instructor/restDocente/materias/
 *
 Registro de materias
 * POST
 * localhost/~instructor/restDocente/materia/registro
 *
 Obtener materia por id
 * GET
 * localhost/~instructor/restDocente/calificaciones/[id]
 Modificar materias
 * PUT
 * localhost/~instructor/restDocente/calificaciones/[id]
 Eliminar materias
 * DELETE
 * localhost/~instructor/restDocente/materia/[id]
 */
/**
 *
 */
include_once(dirname(__FILE__,2) . '\datos\conexionbd.php');

class materias {
    const NOMBRE_TABLA = "calificacion";
    const ID_CALIFICACIONES = "idCalificaciones";
    const ID_MATERIA = "idmateria";
    const ID_ALUMNO = "idAlumno";
    const ID_UNIDAD = "idUnidad";
    const CALIFICACION = "calificacion";
    const ID_DOCENTE = "idDocente";
  
    public static function get($solicitud)
    {
      $idAlumno = alumnos::autorizar();
      if (empty($solicitud)) {
        return self::obtenerCalificaciones($idAlumno);
      } else {
        return self::obtenerCalificaciones($idAlumno, $solicitud[0]);
      }
    }
    public static function post()
    {
      $idDocente = docente::autorizar();
  
      $cuerpo = file_get_contents('php://input');
      $materia = json_decode($cuerpo);
  
      $claveMateria = self::crearCalificacion($idDocente,$materia);
  
      http_response_code(201);
  
      return [
        "estado"=>"Registro exitoso",
        "mensaje"=>"Materia creada",
        "Clave"=>$claveMateria
      ];
    }
    public static function put($solicitud)
    {

      $idDocente=docente::autorizar();
      if (!empty($solicitud)) {
      $cuerpo = file_get_contents('php://input');
      $materia = json_decode($cuerpo);
      if (self::actualizarMateria($idDocente,$materia,$solicitud[0])>0) {
        http_response_code(200);
        return [
          "estado"=>"OK",
          "mensaje"=>"Registro Actualizado"
        ];
      }else{
        throw new ExceptionApi("Materia no actualizada",
        "No se actualizo la materia solicitada",404);
      }
      }
      else{
        throw new ExceptionApi("Parametros incorrectos",
        "Faltan parametros para consulta",422);
      }
    }
    public static function delete($solicitud)
    {
      echo $solicitud[0];
      $idDocente=docente::autorizar();
      if (!empty($solicitud)) {
      if (self::eliminarCalificacion($idDocente,$solicitud[0])>0) {
        http_response_code(200);
        return [
          "estado"=>"OK",
          "mensaje"=>"Registro Eliminado"
        ];
      }else{
        throw new ExceptionApi("Materia no Eliminada",
        "No se actualizo la materia solicitada",404);
      }
      }
      else{
        throw new ExceptionApi("Parametros incorrectos",
        "Faltan parametros para consulta",422);
      }
    

    }
    private function obtenerCalificaciones($idAlumno, $claveMateria = NULL)
    {
      try {
        if (!$claveMateria) {
          $sql = "SELECT * FROM " . self::NOMBRE_TABLA .
                 " WHERE " . self::ID_DOCENTE . "=?";
          $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
          $query = $pdo->prepare($sql);
          $query->bindParam(1,$idAlumno,PDO::PARAM_INT);
        } else {
          $sql = "SELECT * FROM " . self::NOMBRE_TABLA .
                 " WHERE " . self::ID_DOCENTE . "=? AND " .
                 self::CLAVE . "=?";
          $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
          $query = $pdo->prepare($sql);
          $query->bindParam(1,$idAlumno,PDO::PARAM_INT);
          $query->bindParam(2,$claveMateria,PDO::PARAM_STR);
        }
        if ($query->execute()) {
          http_response_code(200);
          return [
            "estado" => "OK",
            "mensaje" => $query->fetchAll(PDO::FETCH_ASSOC)
          ];
        } else {
          throw new ExceptionApi("Error en consulta",
                  "Se ha producido un error al ejecutar la consulta");
        }
      } catch (PDOException $e) {
        throw new ExceptionApi("Error de PDO",
                $e->getMessage());
      }
  
    }
    private function crearCalificacion($idDocente, $materia){
      if ($materia) {
        try {
          $sql = "INSERT INTO " . self::NOMBRE_TABLA . " (" .
            self::ID_CALIFICACIONES . "," .
            self::ID_MATERIA . "," .
            self::ID_ALUMNO . "," .
            self::ID_UNIDAD . "," .
            self::CALIFICACION . "," .
            " VALUES(?,?,?,?,?)";
  
          $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
          $query = $pdo->prepare($sql);
  
          $query->bindParam(1,$materia->calificaciones);
          $query->bindParam(2,$materia->materia);
          $query->bindParam(3,$materia->alumno);
          $query->bindParam(4,$materia->unidad);
          $query->bindParam(5,$materia->calificacion);
        
  
          $query->execute();
          
          return $materia->clave;
  
        } catch (PDOException $e) {
          throw new ExceptionApi("Error de BD",
                  $e->getMessage());
        }
  
      } else {
        throw new ExceptionApi("Error de parametros",
                "Error al pasar la Materia");
      }
    }
    private function actualizarMateria($idDocente, $materia, $claveMateria)
    {
      echo "la clave materia es " . $claveMateria;
      try{
        $sql= "UPDATE " . self::NOMBRE_TABLA .
         " set " . self::NOMBRE ."=?, " .
        self::CREDITOS ."=?, " .
        self::HT ."=?, " .
        self::HP ."=? " .
         "WHERE " . self::CLAVE . "=? and " .
         self::ID_DOCENTE . " =?;";
         
         $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
         $query = $pdo->prepare($sql);
         $query->bindParam(1,$materia->nombre);
         $query->bindParam(2,$materia->creditos);
         $query->bindParam(3,$materia->ht);
         $query->bindParam(4,$materia->hp);
         $query->bindParam(5,$claveMateria);
         $query->bindParam(6,$idDocente);
         $query->execute();
         return $query-> rowCount();

      } catch(PDOException $e){
        throw new ExceptionApi("Error en cosulta",$e->getMessage());
      }
    }
    private function eliminarCalificacion($idDocente, $materia)
    {
      try{
        $sql= "DELETE FROM " . self::NOMBRE_TABLA . " WHERE "
         .self::ID_DOCENTE."=? and " 
         .self::CLAVE ."=?;";
         $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
         $query = $pdo->prepare($sql);
         $query->bindParam(1,$idDocente);
         $query->bindParam(2,$materia);
         echo $sql;
         $query->execute();
         return $query-> rowCount();
      } catch(PDOException $e){
        throw new ExceptionApi("Error en cosulta",$e->getMessage());
      }
    }
  }
?>