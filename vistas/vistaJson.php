<?php
require_once "vistaApi.php";
/**
 * clase para mostrar respuestas con formato JSON.
 */
class vistaJson extends vistaApi
{
    
    
public function imprimir($contenido){
    if($this->estado){
        http_response_code($this->estado);
    }
    header('Content-Type: application/json; charset=utf8');
    echo json_encode($contenido,JSON_PRETTY_PRINT);
    exit;
}    
}

?>