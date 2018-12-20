<?php
// Init libraries, classes
include 'init.php';

pe($db -> select('name') -> from('system_variable') -> orderBy('id') -> select('value') -> orderBy('value', 2) -> orderBy('name', 1) -> limit(2, 4) -> get());


p($db -> value(GET_VARIABLE_VALUE, array('id' => 3)));


p($db -> row(GET_VARIABLE, array(3)));


p($db -> all('errors'));


p($db -> rows(GET_VARIABLES, array(3)));


p($db -> query(DELETE_VARIABLE, array(58)));


p($db -> query(INSERT_VARIABLE, array(
	'value' => 'ის 5', 
	'name' => 'ნო 55'
)));


p($db -> query(UPDATE_VARIABLE, array('ი ნომ', 3)));



p($db -> rows(GET_ALL_VARIABLES));