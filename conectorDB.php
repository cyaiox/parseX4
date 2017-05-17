<?php

namespace CallCenter;
use PDO;
use PDOException;
/**
* 
*/
class ConectorDB{
    private $database;
    private $host;
    private $dbuser;
    private $dbpasswd;
    
    private $pdo;
    private $baseConectada;

    private $pQuery;
    
    /**
    *
    *
    */
    function __construct($user, $pass, $db, $host = "localhost"){
        $this->baseConectada    = FALSE;
        $this->dbuser           = $user;
        $this->dbpasswd         = $pass;
        $this->host             = $host;
        $this->database         = $db;

        $this->Conectar();
    }

    /**
    *
    *
    */
    private function Conectar(){
        try {
            # Crear el string de conexion
            $dsn = "mysql:host=$this->host;dbname=$this->database;";

            # Intentar la conexion
            //$this->pdo = new PDO($dsn, $this->dbuser, $this->dbpasswd ,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $this->pdo = new PDO($dsn, $this->dbuser, $this->dbpasswd);

            # Loggear cualquier excepcion 
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            # Deshabilitar emulacion de prepared statemens
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            # Conectado :)
            $this->baseConectada = TRUE;            
        }
        catch(PDOException $e) {
            # Escribir el log en caso de excepcion en la conexion
            echo $this->ExceptionLog($e->getMessage());
        }
    }


    /**
    *
    *
    */
    private function Init($query){
        # Conectar a la DB en caso de desconectado
        if(!$this->baseConectada) { 
            $this->Conectar(); 
        }
        try {
            # Preparar query
            $this->pQuery = $this->pdo->prepare($query);
            
            # Ejecutar SQL 
            $this->pQuery->execute();
        }
        catch(PDOException $e){
            # Write into log and display Exception
            echo $this->ExceptionLog($e->getMessage(), $query);
        }
    }

    
    /**
    *
    *
    */  
    private function modoFetch ($tipo = 0) {
            
        if ($tipo == 0){
            $tipoFetch = PDO::FETCH_ASSOC;
        }
        elseif ($tipo == 1){
            $tipoFetch = PDO::FETCH_NUM;
        }
        elseif ($tipo == 2){
            $tipoFetch = PDO::FETCH_BOTH;
        }
        elseif ($tipo == 3){
            $tipoFetch = PDO::FETCH_OBJ;
        }
        
        return $tipoFetch;
    }

    /**
    *
    *
    */          
    public function query($query, $tipoRespuesta = 0) {
        $query = trim($query);

        $this->Init($query);

        $rawStatement = explode(" ", $query);
        
        # Cual sentencia SQL se esta usando
        $statement = strtolower($rawStatement[0]);
        
        if ($statement === 'select' || $statement === 'show') {
            //$this->pQuery->closeCursor();
            return $this->pQuery->fetchAll($this->modoFetch($tipoRespuesta));
        }
        elseif ( $statement === 'insert' ||  $statement === 'update' || $statement === 'delete' ) {
            //$this->pQuery->closeCursor();
            return $this->pQuery->rowCount();   
        }   
        else {
            return NULL;
        }
    }
    
    /**
    *
    *
    */
    public function CerrarConexion(){
        # Setear el objeto PDO a NULL para cerrar la conexion
        # http://www.php.net/manual/en/pdo.connections.php
        $this->baseConectada = FALSE;
        $this->pdo = null;
    }

    /**
    *
    *
    *
    */   
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
    *
    *
    *
    */   
    private function ExceptionLog($message , $query = ""){
        $exception = "Excepcion: $message \n <br />";
        $exception.= "Query: $query \n <br />";
        
        $b= debug_backtrace();
        $archivo= $b[1]['file'];
        $nro_linea=$b[1]['line'];
        
        //$sql = "INSERT into asterisk.errores SET query='$query',error='$message',archivo='$archivo', nro_linea='$nro_linea',backtrace='$b', fecha=Now(), usuario=User()";
        //$this->query($sql);
        /*if (!$result){
            $CREATE="CREATE TABLE asterisk.errores (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            query VARCHAR( 350 ) NULL ,
            error VARCHAR( 200 ) NULL ,
            archivo VARCHAR(200) DEFAULT NULL,
            nro_linea int(11) DEFAULT NULL,
            backtrace text,
            fecha DATETIME NOT NULL ,
            usuario VARCHAR( 15 ) NULL
            ) ENGINE = MYISAM COMMENT = 'errores de mysql detectados'";
        }*/
     
        
        $exception.= "Archivo: $archivo \n <br />";
        $exception.= "Linea: $nro_linea \n <br />";
        
        return $exception;
    }
}



?>