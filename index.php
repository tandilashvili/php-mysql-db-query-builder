<?php
// Init libraries, classes
include 'init.php';

/*
Using first('field1, field2, ...') with several fields will result in 
returning one row with corresponding fields 
*/
p(	$db -> table('word')
        // -> select('sentence, comment, id')
        -> where('id', '>', 630)
        -> orderBy('id')
        // -> limit(1)
        -> first('sentence, comment, id') );


// Using first('field') function to retrieve only one value. 
p(	$db -> table('word')
        // -> select('sentence, comment, id')
        -> where('id', '>', 630)
        -> orderBy('id')
        // -> limit(1)
        -> first('sentence') );



// Using whereIn and whereNotIn functions
p(	$db -> table('word')
		-> where("word IN ('oneblah', 'onebluh')")
		-> where("word", 'smoothly')
		-> whereIn("word", "'blah', 'bluh'")
		-> whereNotIn("word", "'smoothly', 'bravely'")
		-> where("id", '<', 55)
		-> leftJoin('lefttable lt', 'lt.id', 'word.lefttable_id')
		-> count());


echo '__';

// Using where with only one parameter
p(	$db -> table('word') 
		-> where("word", 'smoothly')
		// -> join('jointable jt', 'jt.id', 'word.jointable_id') // commented because there are not such tables to join
		// -> rightJoin('blahtable bt', 'bt.id', 'word.blahtable_id')
		-> where("word IN ('smoothly', 'bravely')")
		-> where("id", '<', 55)
		// -> leftJoin('lefttable lt', 'lt.id', 'word.lefttable_id')
		-> count());


echo '__';

p(	$db -> table('word') 
		-> where("word", 'smoothly')
		-> where("word IN ('smoothly', 'bravely')")
		-> where("id", '<', 55)
		-> count());


echo '__';
// p(	$db -> table('word') 
// 		-> where('word', 'smoothly')
// 		-> count());

// 	
p(	$db -> table('system_variable')
		-> select('id, value, name')
		-> where('id', '>', '20')
		-> limit(7, 10)
		-> rowsWithCount());


// get distinct value count
p(	$db -> table('system_variable')
		-> count('DISTINCT value'));


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

p(	$db -> select('name') -> select('value, id') -> from('system_variable') -> first());
		
// p(	$db -> select('id, name') 
// 		-> select('value') 
// 		-> from('system_variable')
// 		-> where('id', '<', '50')
// 		-> orderBy('name') 
// 		-> orderBy('id', 2)
// 		-> limit(5, 5)
// 		-> get());

// Insert a new row into system_variable table
p(	$db -> table('system_variable') 
		-> insert([
			'value' => '54', 
			'name' => '14'
		])
);

//p($db -> table('system_variable') -> where('id', '>', 119) -> delete());

//pe($db -> table('system_variable') -> truncate());


pe(	$db -> select('value') 
		-> select('COUNT(id) cnt')
		-> from('system_variable') 
		-> where('id', '<', '190')
		-> groupBy('value')
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