<?php
// Init libraries, classes
include 'init.php';

p(	$db -> table('system_variable') -> sum('id'));

p(	$db -> select('name') 
		-> from('system_variable') 
		-> first());
		
pe(	$db -> select('id, name') 
		-> from('system_variable') 
		-> orderBy('name') 
		-> orderBy('id', 2)
		-> limit(2, 10)
		-> select('value') 
		-> get());


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