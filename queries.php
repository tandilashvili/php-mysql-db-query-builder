<?php

// API QUERIES
define('GET_VARIABLE_VALUE',  "SELECT value FROM `system_variable` WHERE id = ?");
define('GET_VARIABLE',  "SELECT * FROM `system_variable` WHERE id = ?");
define('GET_VARIABLES',  "SELECT * FROM `system_variable` WHERE id <= ?");
define('DELETE_VARIABLE', "DELETE FROM `system_variable` WHERE `id` = ?");
define('INSERT_VARIABLE', "INSERT INTO `system_variable` (`name`, `value`) VALUES (?, ?)");
define('UPDATE_VARIABLE', "UPDATE `system_variable` SET `value` = ? WHERE `id` = ?");
