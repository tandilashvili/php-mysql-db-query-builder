<?php
include 'db.class.php';

include 'config.php';
include 'queries.php';
include 'functions.php';

// Creating db object
$db = new db(DB_SERV, DB_USER, DB_PASS, DB_NAME);
