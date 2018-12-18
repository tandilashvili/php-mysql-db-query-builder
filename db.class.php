<?php

class db {

    var $host   = ""; //database server
	var $user     = ""; //database login name
	var $pass     = ""; //database login password
    var $database = ""; //database name

    var $error = false; //database status
    var $status_code = 1; //database status code
    var $status_text = "OK"; //database status text

    public $link;

    function __construct ($host, $user, $pass, $database) {

        $this -> host = $host;
		$this -> user = $user;
		$this -> pass = $pass;
        $this -> database = $database;
        
        $this -> connect();

        $this -> createErrorsTable();

    }

    function connect() {

        try {

            $this -> link = new PDO("mysql:host={$this->host};dbname={$this->database}", $this->user, $this->pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
            
            // set the PDO error mode to exception
            $this -> link -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }
        catch(Exception $e) {

            logErrorInFile($e -> getMessage());

            $this -> registerError(1100);

        }
        
        return $this -> link;
            
    }

    public function query($query, $params = null)
    {
        
        $stmt = null;
        try {

            if(!$this -> error) {

                # create a prepared statement
                $stmt = $this -> link -> prepare($query);

                /*
                $named = strpos($query, ':') !== false;

                foreach ($params as $key => $value) {

                    if($named)
                        $key ++;

                    $stmt -> bindParam($key, $value);

                }
                */

                # execute query 
                if($params)
                    $stmt -> execute($params);
                else
                    $stmt -> execute();

            }            

        }
        catch (Exception $e) {

            $this -> logError($e, $query, $params);

            $this -> registerError(1101);

        }
        
        return $stmt;
    }

    function types($params) {

        $types = '';
        foreach($params as $param)
        {
            if(is_int($param)) {
                // Integer
                $types .= 'i';
            } elseif (is_float($param)) {
                // Double
                $types .= 'd';
            } elseif (is_string($param)) {
                // String
                $types .= 's';
            } else {
                // Blob and Unknown
                $types .= 'b';
            }
        }
        return $types;

    }

    function value($sql, $params = null) {

        $stmt = $this -> query($sql, $params);
        
        if ($stmt -> rowCount() > 0) {

            $row = $stmt -> fetch(3); //PDO::FETCH_NUM
            return $row[0];

        }
        return '';

    }

    function row($sql, $params = null) {

        $stmt = $this -> query($sql, $params);

        if ($stmt -> rowCount() > 0) {

            $row = $stmt -> fetch(2);
            return $row;

        }
        return '';

    }

    function rows($sql, $params = null) {

        $arr = array();
        $stmt = $this -> query($sql, $params);

        if ($stmt -> rowCount() > 0)
            $arr = $stmt -> fetchAll(2);
        
        return $arr;

    }

    function registerError($error_code) {
        
        $this -> error = true;
        $this -> status_code = $error_code;

    }

    function logError($e, $query, $params, $comment = '') {

        try {

            $insert = " INSERT INTO `errors` (
                            `query`, 
                            `params`, 
                            `message`, 
                            `code`, 
                            `file`, 
                            `line`, 
                            `trace`, 
                            `comment`
                        ) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?);";
        
            $stmt1 = $this -> link -> prepare($insert);

            if($params)
                $params = implode('; ', $params);
            else
                $params = '';
            
            $message = $e -> getMessage();
            $code = $e -> getCode();
            $file = $e -> getFile();
            $line = $e -> getLine();
            $trace = $e -> getTraceAsString();

            $stmt1 -> execute(array($query, $params, $message, $code, $file, $line, $trace, $comment));

        }
        catch (Exception $ex) {

            logErrorInFile($ex -> getMessage());

        }        

    }

    function createErrorsTable() {

        $exists_query ="SELECT COUNT(table_name)
                        FROM information_schema.tables 
                        WHERE table_schema = '" . $this -> database . "' 
                            AND table_name = 'errors'";

        if($this -> value($exists_query) == 0) {

            $queries = array(
                'CREATE TABLE `errors` (
                  `id` int(11) NOT NULL,
                  `query` text NOT NULL,
                  `params` text NOT NULL,
                  `message` text NOT NULL,
                  `code` text NOT NULL,
                  `file` text NOT NULL,
                  `line` int(11) NOT NULL,
                  `trace` text NOT NULL,
                  `comment` text NOT NULL,
                  `inserted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
                'ALTER TABLE `errors` ADD PRIMARY KEY (`id`);',
                'ALTER TABLE `errors` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;'
            );

            foreach($queries as $query)
                $this -> query($query);

        }        

    }

}
