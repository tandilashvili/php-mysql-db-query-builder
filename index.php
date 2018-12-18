<?php
// Init libraries, classes
include 'init.php';

p($db -> value(GET_VARIABLE_VALUE, array(3)));


p($db -> row(GET_VARIABLE, array(3)));


p($db -> rows(GET_VARIABLES, array(3)));


p($db -> query(DELETE_VARIABLE, array(23)));


p($db -> query(INSERT_VARIABLE, array('ისტორის', 'ნომეp0pრისoi')));


p($db -> query(UPDATE_VARIABLE, array('ისტორjkkkkkkkის ნომერიii0iსoo', 3)));
