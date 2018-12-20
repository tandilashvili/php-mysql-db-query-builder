<?php

class db {

    var $host   = ""; //database server
	var $user     = ""; //database login name
	var $pass     = ""; //database login password
    var $database = ""; //database name

    var $error = false; //database status
    var $status_code = 1; //database status code
    var $status_text = "OK"; //database status text

    var $query = array();

    public $link;

    function all($table) {

        return $this -> from($table) -> get();

    }

    function select($field) {

        array_push($this -> query['fields'], $field);

        return $this;

    }

    function count() {

        return $this -> aggregate('COUNT(*)');

    }

    function min($field) {

        return $this -> aggregate("MIN({$field})");

    }

    function max($field) {

        return $this -> aggregate("MAX({$field})");

    }

    function avg($field) {

        return $this -> aggregate("AVG({$field})");

    }

    function sum($field) {

        return $this -> aggregate("SUM({$field})");

    }

    function aggregate($aggregate) {

        return $this -> value($this -> select($aggregate) -> getQuery());

    }

    function from($from) {

        $this -> query['table'] .= $from;

        return $this;

    }

    function table($table) {

        return $this -> from($table);

    }

    function orderBy($order_by, $order=1) {

        $order_str = $order == 1 ? 'ASC' : 'DESC';

        $order_by .= ' ' . $order_str;

        if(empty($this -> query['order_by']))
            $this -> query['order_by'] = 'ORDER BY ' . $order_by;
        else
            $this -> query['order_by'] .= ', ' . $order_by;

        return $this;

    }

    function limit() {

        $skip = $take = 0;
            
        // Get number of params
        $num_args = func_num_args();

        if($num_args == 1) {
            $take = func_get_arg(0);
        }
        else if($num_args == 2) {
            $skip = func_get_arg(0);
            $take = func_get_arg(1);
        }

        $this -> query['limit'] = "LIMIT $skip, $take";

        return $this;

    }

    function first() {
        
        return $this -> limit(0, 1) -> get();

    }

    function get() {
        p($this -> getQuery());
        if(!count($this -> query['fields']))
            array_push($this -> query['fields'], '*');

        $result = $this -> rows($this -> getQuery());
        
        $this -> resetQuery();

        return $result;

    }

    function getQuery() {

        return $this -> concatQuery();

    }

    function concatQuery() {
        
        $str = '';
        foreach($this -> query as $item) {

            if(is_array($item)) {

                $count = count($item);

                if($count == 1)
                    $str_item = $item[0];
                else if($count > 1)
                    $str_item = implode(', ', $item);
                else 
                    $str_item = '';

            }                
            else
                $str_item = $item;
            
            $str .= ' ' . $str_item;

        }
        
        $this -> resetQuery();

        return $str;

    }

    function resetQuery() {
        
        $this -> query = array(

            'action' => 'SELECT',
            'fields' => array(),
            'table' => 'FROM ',
            'where' => '',
            'order_by' => '',
            'limit' => '',
    
        );

    }

    function __construct ($host, $user, $pass, $database) {

        $this -> host = $host;
		$this -> user = $user;
		$this -> pass = $pass;
        $this -> database = $database;
        
        $this -> connect();

        // $this -> createErrorsTable();

        $this -> resetQuery();

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
