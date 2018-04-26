<?php
/**
 * heredamos la calse excpecion para poder mostrar los errores
 * que se generanen nuestro WS
 */
class ExceptionApi extends Exception{
    public $estado;
    //throw new ExceptionApi(2,"Error con estado 2",400);
    function __construct($estado, $mensaje, $codigo = 400){
        $this->estado = $estado;
        $this->message = $mensaje;
        $this->code = $codigo;
    }
}
?>