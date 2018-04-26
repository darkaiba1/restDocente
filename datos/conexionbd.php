<?php
require_once 'datosbd.php';

class conexionbd{
    private static $bd=null;
    private static $pdo;
    
    function __construct(){
        echo "constructor";
        try{
            self::obtenerbd();
        }catch  (PDOException $e)
        {
            echo "<h2> Error en la conexion con la base de datos </h2>" .$e;
        }
    
    }
 public static function obtenerInstancia()
{
    echo "obtener instancia";
    if (self:: $bd==null) {
        self::$bd=new self();
    }
    return self::$bd;
}

public function obtenerbd()
{   if (self::$pdo ==null) {
        
        self::$pdo=new PDO(
        'mysql:dbname='. BASE_DE_DATOS .
        ';host='.HOST .";" ,
        USUARIO, CONTRASEÑA,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    );
    //habilitamos las excepcions
    self::$pdo-> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    
    }
    
    return self::$pdo;
}

function _destructor()
{
    self::$pdo=null;
}
}
?>