<?php

class Db
{

    private $conn = null;
    private static $_instance = null;

    private function __construct()
    {
        try {
            $dsn = 'mysql:host=localhost;dbname=neo;charset=utf8mb4';
            $username = 'root';
            $password = 'crimson';
            $this->conn = new PDO($dsn, $username, $password, array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ));

        } catch (PDOException $e) {
            trigger_error('Could not connect to database:' . $e->getMessage(), E_USER_ERROR);
            exit;
        }
    }

    // Devolvemos la conexión
    private function getConnection()
    {
        return $this->conn;
    }

    // Magic method clone is empty to prevent duplication of connection
    private function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    public function __wakeup()
    {
        trigger_error('Deserializing is not allowed.', E_USER_ERROR);
    }

    // close db connection
    public function __destruct()
    {
        $this->conn = null;
    }

    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self;
        }
        return self::$_instance->getConnection();
    }

    /**
     * func_get_args ( void ) : array, Obtiene un array de la lista de argumentos de una función.
     * array_slice — Extraer una parte de un array
     */
    public static function query(/* $sql [, ... ] */)
    {
        // SQL statement
        $sql = func_get_arg(0);

        // parameters, if any
        $parameters = array_slice(func_get_args(), 1);

        $stmt = self::getInstance()->prepare($sql);

        if ($stmt === false) {
            // trigger (big, orange) error
            trigger_error(self::getInstance()->errorInfo()[2], E_USER_ERROR);
            exit;
        }

        $result = $stmt->execute($parameters);

        if ($result === false) return false;

        if ($stmt->columnCount() > 0) {
            // return result set's rows
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        // if query was DELETE, INSERT, or UPDATE
        else {
            // return number of rows affected
            return ($stmt->rowCount() == 1); // true o false
        }
    }
}
