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

        $this -> link = new mysqli($this->host, $this->user, $this->pass, $this->database);

        if ($this -> link -> connect_errno) {
            
            logErrorInFile($this -> link -> connect_errno . ' -> ' . $this -> link -> connect_error);

            $this -> registerError(1100);

            return null;

        }
        
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $this -> link -> set_charset("utf8");
        
        return $this -> link;
            
    }

    public function query($query, $params = null)
    {
        
        $stmt = null;
        try {

            if(!$this -> error) {

                # create a prepared statement
                $stmt = $this -> link -> prepare($query);

                if ($params != null)
                {
                    // Generate the Type String (eg: 'issisd')
                    $types = $this -> types($params);
            
                    // Add the Type String as the first Param
                    $bind_names[] = $types;
            
                    // Loop through the given Params
                    for ($i=0; $i<count($params);$i++)
                    {
                        // Create a variable Name
                        $bind_name = 'bind' . $i;
                        // Add the Parameter to the variable Variable
                        $$bind_name = $params[$i];
                        // Associate the Variable as an Element in the Array
                        $bind_names[] = &$$bind_name;
                    }

                    // Call the Function bind_param with dynamic Parameters
                    call_user_func_array(array($stmt,'bind_param'), $bind_names);
                }

                # execute query 
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

        $result = $stmt -> get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_row();
            return $row[0];
        }
        return '';

    }

    function row($sql, $params = null) {

        $stmt = $this -> query($sql, $params);

        $result = $stmt -> get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row;
        }
        return '';

    }

    function rows($sql, $params = null) {

        $arr = array();
        $stmt = $this -> query($sql, $params);

        $result = $stmt -> get_result();

        if ($result -> num_rows > 0)
            while($row = $result -> fetch_assoc())
                $arr[] = $row;
        
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

            $params = implode('; ', $params);
            
            $message = $e -> getMessage();
            $code = $e -> getCode();
            $file = $e -> getFile();
            $line = $e -> getLine();
            $trace = $e -> getTraceAsString();

            $stmt1 -> bind_param("sssssiss", $query, $params, $message, $code, $file, $line, $trace, $comment);

            $stmt1 -> execute();

        }
        catch (Exception $ex) {

            logErrorInFile($ex -> getMessage());

        }        

    }

    function createErrorsTable() {

        $exists_query ="SELECT COUNT(table_name) 
                        FROM information_schema.tables 
                        WHERE table_schema = '" . $this->database . "' 
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
