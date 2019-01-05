<?php
// Init libraries, classes
include 'init.php';

// p(	$db -> table('word') 
// 		-> where('word', 'smoothly')
// 		-> count());

// Get the highest id from the table
p(	$db -> table('system_variable') -> max('id'));

// Get a single field of the first row
p(	$db -> table('system_variable') 
		-> where('id', 32)
		-> get('value')
);

p(	$db -> table('system_variable') -> where('id', '>', '5') -> exists());

p(	$db -> table('system_variable') -> sum('id'));

p(	$db -> table('system_variable') -> min('id'));

p(	$db -> select('name') -> from('system_variable') -> first());
		
// p(	$db -> select('id, name') 
// 		-> select('value') 
// 		-> from('system_variable')
// 		-> where('id', '<', '50')
// 		-> orderBy('name') 
// 		-> orderBy('id', 2)
// 		-> limit(5, 5)
// 		-> get());

p(	$db -> table('system_variable') 
		-> insert([
			'value' => '54', 
			'name' => '14'
		])
);

//p($db -> table('system_variable') -> where('id', '>', 119) -> delete());

//pe($db -> table('system_variable') -> truncate());


pe(	$db -> select('id, name') 
		-> select('value') 
		-> from('system_variable') 
		-> where('id', '<', '50')
		-> orderBy('id', 2)
		-> limit(2, 10)
		-> get()
	);

pe(	$db -> table('system_variable') 
		-> where('name', '25')
		-> orWhere('id', '>', '113') 
		-> update(array(
			'value' => 'new-value - 54', 
			'name' => '27'
		)));

pe(	$db -> table('word') 
		-> join('source', 'word.source_id', 'source.id')
		-> select('word, sentence, word.comment, source.name') 
		-> orderBy('word.id')
		-> limit(10)
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