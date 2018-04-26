<?php
echo dirname(__FILE__,2) . '\datos\conexionbd.php';
include_once(dirname(__FILE__,2) . '\datos\conexionbd.php');
//require_once "/../utiles/ExceptionApi.php";
/**
 * acceder al recuerso docente
 * http://localhost:8080/restDocente
 * 
 * registro del docente
 * POST 
 * http://localhost:8080/restDocente/registro
 * 
 * Acceder al WS
 * POST
 * http://localhost:8080/restDocentes/login
 */
class docente
{
    //datos de la tabla docentes.
    const NOMBRE_TABLA="docente";
    const ID_DOCENTE= "id";
    const NOMBRE= "nombre";
    const APELLIDOS= "apellido";
    const CORREO= "correo";
    const CLAVEAPI= "claveapi";
    const PASSWORD= "password";
    const ESTADO_CREACION_OK= 200;
    const ESTADO_CREACION_ERROR= 403;

    const ESTADO_ERROR_DB=500;
    const ESTADO_NO_CLAVE_API=406;
    const ESTADO_CLAVE_NO_AUTORIZADA=401;
    const ESTADO_URL_INCORRECTA=404;
    const ESTADO_FALLA_DESCONOCIDA=504;
    const ESTADO_DATOS_INCORRECTOS=432;

    public static function post($solicitud)
    {
        echo "entrando a post docente..";
    if($solicitud[0]=="registro"){
        echo "entra a registro";
        return self::registrar();
    }
    else if($solicitud[0]=="login"){
        return self::ingresar();
    }
    else{
        throw new ExceptionApi(self::ESTADO_URL_INCORRECTA,"URL INCORRECTA",400);
    }
    }
    private function registrar(){
        echo "registrar";
        $cuerpo=file_get_contents('php://input');
        $docente = json_decode($cuerpo);

    
       //pasos siguientes
       // validar datos
       // crear docenten
       // imprimir respuesta
       echo "el resultado que lleva es " . $cuerpo;
        $resultado=self::crear($docente);
        switch ($resultado) {
            case self::ESTADO_CREACION_OK:
                http_response_code(200);
        
              //  return [
              //      "estado"->self::ESTADO_CREACION_OK,
              //       "mensaje"->utf8_encode("!REGISTRO EXITOSO!")];
                break;
                case self::ESTADO_CREACION_ERROR:
                throw new ExceptionApi(self::ESTADO_CREACION_ERROR, "Error al crear docente");
                break;
            default:
            throw new ExceptionApi(self::ESTADO_FALLA_DESCONOCIDA, "Error desconocido");
                break;
        }
       return $cuerpo;
    }
    private function ingresar(){
    $respuesta=array();
    $cuerpo=file_get_contents('php://input');
    $docente=json_decode($cuerpo);  
    $correo=$docente->correo;
    $password=$docente->password;
    if (self::autenticar($correo,$password)) {
    
        $docenteDatos=self::getDocentePorCorreo($correo);

        if ($docenteDatos!=null) {
        
            http_response_code(200);
            return ["estado"=>1, "docente"=>$docenteDatos];
        } else{
            
            throw new ExceptionApi(self::ESTADO_FALLA_DESCONOCIDA, "Error desconocido");
        }
    }
    else{
        throw new ExceptionApi(self::ESTADO_DATOS_INCORRECTOS, "Correo o password incorrectos");
    }  
    }
    private function crear($datosDocente){ 
     $nombre= $datosDocente->nombre;
     echo "arreglo: ";
     print_r($datosDocente);
     $password= $datosDocente->password;  
     $passwordEnc=self::encriptarPassword($password); 
     $correo=$datosDocente->correo;
     $claveApi=self::generarClaveAPI();
    
     try{
        $pdo=conexionbd::obtenerInstancia()->obtenerbd();
        $sql= "INSERT INTO " . self::NOMBRE_TABLA . "(" 
            . self::NOMBRE . "," . self::APELLIDOS . "," 
            . self::CORREO . "," . self::CLAVEAPI . "," . self::PASSWORD .
            ") values (?,?,?,?,?)";
        $query = $pdo->prepare($sql);
        $query->bindParam(1,$nombre);
        $query->bindParam(2,$datosDocente-> apellido);
        $query->bindParam(3,$correo);
        $query->bindParam(4,$claveApi);
        $query->bindParam(5,$passwordEnc);
        
        $query->execute();
        if($query){
            return self::ESTADO_CREACION_OK;
            
        }
        else{
            return self::ESTADO_CREACION_ERROR;
            
        }

     } catch (PDOException $pdoe){
        throw new ExceptionApi(self::ESTADO_ERROR_DB,$pdoe->getMessage());
     }
    }
    private function encriptarPassword($password){
        if($password){
            return password_hash($password,PASSWORD_DEFAULT);
        }else {
            return null;
        }
    }
    private function validarPassword($passwordClaro, $passwordEncriptado){
        $passwordEnc;
        return password_verify($passwordClaro,$passwordEncriptado);
    }
    private function autenticar($correo, $password){
        /**
         * consulta a la base de datos para ver si existe el correo
         * crear la conexion
         * enalzar los parametos q se necesiten 
         * ejecutar la consulta
         * validar la contrasena password
         * regresar true si existe o false caso contrario.
         * mandar una exceocion si nos se oeude conectar a la base de datos 
         * 
         */
        $sql= "SELECT password FROM " .  self::NOMBRE_TABLA  .
         " WHERE " . self::CORREO . " = ?";    
        try{
        $pdo=conexionbd::obtenerInstancia()->obtenerbd();
        $query = $pdo->prepare($sql);
        $query->bindParam(1,$correo);
        $query->execute();
        if($query){
            $resultado= $query->fetch();
            //echo $resultado['password'];
            if(self::validarPassword($password, $resultado['password'])){
            
            return true;
        }
        else{
            return false;
        }
        }
        else{
            return false;
        }
       
        } catch (PDOException $pdoe){
            throw new ExceptionApi(self::ESTADO_ERROR_DB,$pdoe->getMessage());
         }
        
      

    }
    private function autorizar(){
       $cabeceras=apache_request_headers(); 
       if (isset($cabeceras["authorization"])) {
           $claveApi=$cabeceras["autorization"];
       if (docente::validarClaveApi($claveApi)) {
           return docente::getIdDocente($claveApi);
       }
       else{
        throw new ExceptionApi(self::ESTADO_CLAVE_NO_AUTORIZADA,"clave API no autorizada");
       }
        }else{
        throw new ExceptionApi(self::ESTADO_NO_CLAVE_API,"se requiere clave Api para autorizar");
        }
    }   
    private function getDocentePorCorreo($correo){
    
        $sql= "SELECT " . 
        self::NOMBRE . "," .
        self::APELLIDOS . "," .
        self::CORREO . "," .
        self::CLAVEAPI . "," .
        self::PASSWORD . "," .
        " FROM " .  self::NOMBRE_TABLA  .
        " WHERE " . self::CORREO . " = ?";    
       try{
       $pdo=conexionbd::obtenerInstancia()->obtenerbd();
       $query = $pdo->prepare($sql);
       $query->bindParam(1,$correo);
       $query->execute();
       if($query){
           $resultado= $query->fetch(PDO::FETCH_ASSOC);
           return $resultado;
       }
       else{
           return null;
       }
       }
     catch (PDOException $pdoe){
           throw new ExceptionApi(self::ESTADO_ERROR_DB,$pdoe->getMessage());
        }
       
    }
    private function generarClaveAPI()
    {
        $micro=microtime().rand();
        echo ".    Microtime:  ". $micro."<br>";
        return md5($micro);
    }    
    private function validarClaveApi($claveApi){
        
    }
    private function getIdDocente($claveApi){
        
    }
}

?>