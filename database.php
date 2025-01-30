<?php

    class Database {
        private static $host = 'localhost';
        private static $db_name = 'finance_db'; // Ensure this matches database name in MySQL
        private static $username = 'root';
        private static $password = 'root';
        private static $conn = null;
    
        public static function connect() {
            $dsn = "mysql:host=" . self::$host . "; dbname=" . self::$db_name;
            $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
            if (self::$conn === null) {
                try {
                    self::$conn = new PDO($dsn, self::$username, self::$password, $options);
                } catch (PDOException $e) {
                    die("Database connection failed: " . $e->getMessage());
                }
            }
            return self::$conn;
        }
    
        public static function disconnect() {
            self::$conn = null;
        }
    

    function bindValue() {
        //A method to bind parameters
        if (is_null($type))  {
            switch(true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }

            $this->stmt->bindValue($param, $value, $type);

        }
    }//End of Method


}//End of Class


?>
               