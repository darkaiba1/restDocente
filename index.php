<?php
require 'datos/conexionbd.php';
require 'controladores/alumnos.php';
require 'controladores/materias.php';
require 'controladores/docente.php';
require 'vistas/vistaJson.php';
require 'utiles/ExceptionApi.php';
$vista=new vistaJson();
set_exception_handler(function ($exception) use ($vista){
    $cuerpo = array(
        "estado" => $exception->estado=400,
        "mensaje" => $exception->getMessage()
    );
    if($exception->getCode()){
        $vista->estado = $exception->getCode();
    }else{
        $vista->estado = 500;
    }
    $vista->imprimir($cuerpo);
});

if(isset($_GET['PATH_INFO'])){
$peticion=explode('/',$_GET['PATH_INFO']);    
}else{
    throw new ExceptionApi(ESTADO_URL_INCORRECTA,"Solicitud incorrecta",400);
}
//print_r($peticion);
/*
http://localhost:8080/restCalificaciones/materias/4
Array{[0]=>materias,[1]=>4}
*/
//obtener recurso de WS
$recurso= array_shift($peticion);
echo $recurso;
$recursos_disponibles=array('alumnos','calificaciones','docente','materias');

//validamos si el recurso existe    
if (!in_array($recurso,$recursos_disponibles)) {
    echo "error";
    http_response_code(400);
    throw new ExceptionApi("ESTADO_RECURSO_INEXISTENTE","No se encuentra el recurso solicitado");
}
$metodo=strtolower($_SERVER['REQUEST_METHOD']);
//GET POST PUT O DELETE
    
    switch ($metodo){
    case 'get':
    
    case 'post':
    
    case 'put':
    
    case 'delete':
    if (method_exists($recurso,$metodo)) {
        $res = call_user_func(array($recurso,$metodo),$peticion);
        $vista->imprimir($res); 
        break;
    }
    
    
    default:
    $vista->estado = 405;
    
    $cuerpo = [
        "estado"=>"METODO NO PERMITIDO",
        "mensaje"=>"Metodo no permitido"
    ];
    $vista->imprimir($cuerpo);
 
}
print $_GET['PATH_INFO'];
?>