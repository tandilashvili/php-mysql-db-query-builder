<?php

class db {

    var $host   = ""; //database server
	var $user     = ""; //database login name
	var $pass     = ""; //database login password
    var $database = ""; //database name

    var $error = false; //database status
    var $status_code = 1; //database status code
    var $status_text = "OK"; //database status text

    // SQL Query Builder Variables
    var $one_row = false;
    var $one_field = false;
    var $query = array();
    var $params = array();

    public $link;

    function resetQuery() {
        
        $this -> one_row = false;
        
        $this -> one_field = false;

        $this -> params = array();

        $this -> query = array(

            'action' => 'SELECT',
            'fields' => '',
            'pre_table' => 'FROM',
            'table' => '',
            'set' => '',
            'where' => '',
            'join' => '',
            'group_by' => '',
            'order_by' => '',
            'limit' => '',
    
        );

    }

    function all($table) {

        return $this -> from($table) -> get();

    }

    function select($field) {

        $this -> generateQueryPart('fields', $field);

        return $this;

    }

    private function generateQueryPart($field, $field_sql, $field_pre='', $field_sep=', ') {

        if(!empty($field_pre))
            $field_pre .= ' ';

        if(empty($this -> query[$field]))
            $this -> query[$field] = $field_pre . $field_sql;
        else
            $this -> query[$field] .= $field_sep . $field_sql;

        return $this;

    }

    function exists() {

        return $this -> count();

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

        $query = $this -> select($aggregate) -> getQuery();

        return $this -> value($query, $this -> params);

    }

    function from($from) {

        $this -> query['table'] .= $from;

        return $this;

    }

    function table($table) {

        return $this -> from($table);

    }

    function insert($params) {

        $this -> query['action'] = 'INSERT';
        $this -> query['pre_table'] = 'INTO';
        foreach($params as $key => $value) {

            $set = $key  . ' = :' . $key;
            
            // Add param to params array for prepared statement
            $this -> params[$key] = $value;
            
            $this -> generateQueryPart('set', $set, 'SET');
            
        }

        $result = $this -> query($this -> getQuery(), $this -> params);

        return $this -> link -> lastInsertId();

    }

    function update($params) {

        $this -> query['action'] = 'UPDATE';
        $this -> query['pre_table'] = '';

        foreach($params as $key => $value) {
            
            // Add param to params array for prepared statement
            $key_unique = $this -> getUniqueKey($key);
            $this -> params[$key_unique] = $value;

            $set = $key  . ' = :' . $key_unique;
            
            $this -> generateQueryPart('set', $set, 'SET');
            
        }

        p($this -> getQuery());
        
        //pe($this -> params);
        $result = $this -> query($this -> getQuery(), $this -> params);

        return $result -> rowCount();

    }

    private function getUniqueKey($key) {

        while(array_key_exists($key, $this -> params))
            $key .= '1';

        return $key;

    }

    function delete() {

        $this -> query['action'] = 'DELETE';
        $this -> query['pre_table'] = 'FROM';

        // pe($this -> getQuery());
        $result = $this -> query($this -> getQuery(), $this -> params);

        return $result -> rowCount();

    }

    function truncate() {

        $this -> query['action'] = 'TRUNCATE';
        $this -> query['pre_table'] = 'TABLE';

        // pe($this -> getQuery());
        $result = $this -> query($this -> getQuery(), $this -> params);

        return $result;

    }

    function join($table, $expr1, $expr2) {

        $this -> query['join'] .= 'JOIN ' . $table . " ON $expr1 = $expr2 \n";

        return $this;

    }

    function leftJoin($table, $expr1, $expr2) {

        $this -> query['join'] .= 'LEFT JOIN ' . $table . " ON $expr1 = $expr2 \n";

        return $this;

    }

    function where() {

        $comparison = '=';
        $key = $value = '';

        // Get number of params
        $num_args = func_num_args();

        if($num_args == 2) {
            $key = func_get_arg(0);
            $value = func_get_arg(1);
        }
        else if($num_args == 3) {
            $key = func_get_arg(0);
            $comparison = func_get_arg(1);
            $value = func_get_arg(2);
        }

        return $this -> wherePart($key, $value, $comparison, ' AND ');

    }

    private function wherePart($key, $value, $comparison, $operator) {

        // Add param to params array for prepared statement
        $key_unique = $this -> getUniqueKey($key);
        $this -> params[$key_unique] = $value;

        $where =  $key . ' ' . $comparison . ' ' . ":$key_unique";

        $this -> generateQueryPart('where', $where, 'WHERE', $operator);

        return $this;

    }

    function orWhere() {

        $comparison = '=';
        $key = $value = '';

        // Get number of params
        $num_args = func_num_args();

        if($num_args == 2) {
            $key = func_get_arg(0);
            $value = func_get_arg(1);
        }
        else if($num_args == 3) {
            $key = func_get_arg(0);
            $comparison = func_get_arg(1);
            $value = func_get_arg(2);
        }

        return $this -> wherePart($key, $value, $comparison, ' OR ');

    }

    function groupBy($group_by) {

        $this -> generateQueryPart('group_by', $group_by, 'GROUP BY');

        return $this;

    }

    function orderBy($order_by, $order=1) {

        $order_str = $order == 1 ? 'ASC' : 'DESC';

        $order_by .= ' ' . $order_str;

        $this -> generateQueryPart('order_by', $order_by, 'ORDER BY');

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
        
        $this -> one_row = true;
        return $this -> limit(0, 1) -> get();

    }

    function get($field = '') {

        if($field)
        {
            $this -> one_field = true;
            $this -> select($field) -> limit(0, 1);
        }
        //p($this -> getQuery());
        if(empty($this -> query['fields']))
            array_push($this -> query['fields'], '*');
        
        if($this -> one_field)
            $result = $this -> value($this -> getQuery(), $this -> params);
        else if($this -> one_row)
            $result = $this -> row($this -> getQuery(), $this -> params);
        else
            $result = $this -> rows($this -> getQuery(), $this -> params);
        
        return $result;

    }

    function getQuery() {

        return $this -> concatQuery();

    }

    function concatQuery() {
        
        return implode(" \n", $this -> query);

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
        p($this -> getQuery());
        $this -> resetQuery();

        $stmt = $result = null;
        
        try {

            if(!$this -> error) {

                # create a prepared statement
                $stmt = $this -> link -> prepare($query);

                # execute query 
                if($params)
                    $result = $stmt -> execute($params);
                else
                    $result = $stmt -> execute();

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
