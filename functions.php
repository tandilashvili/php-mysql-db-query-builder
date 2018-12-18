<?php

function p($array){

    echo '<pre>';
    print_r($array);
    echo '</pre><br />';
    
}

function pe($array){

		p($array);
		exit;

}

function logErrorInFile($error_text) {

    $log_file_name = SERVICE_LOG_FILE;
	  file_put_contents(SERVICE_LOG_FILE, date('Y-m-d H:i:s') . ' ' . $error_text . "\n\n" . file_get_contents(SERVICE_LOG_FILE));

}
