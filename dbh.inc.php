<?php

class Dbh {
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $charset;

    public function connect () {
        // $this->servername = "sql208.epizy.com";
        // $this->username = "epiz_23895090";
        // $this->password = "zYbHwof9kG";
        // $this->dbname = "epiz_23895090_riasec_test";

        $this->servername = "localhost";
        $this->username = "root";
        $this->password = "";
        $this->dbname = "data_dm4";

        $this->charset = 'utf8mb4';
        try {

            //dsn = data source name
            $dsn = "mysql:host=". $this->servername. ";dbname=" . $this->dbname. ";charset=" . $this->charset;
            $pdo = new PDO ($dsn, $this->username, $this->password);

            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

            return $pdo;
            
        } catch (PDOException $e) {
            echo "Connection failed: ". $e->getMessage();
        }
        

    }
}