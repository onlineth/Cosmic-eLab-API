<?php
# Author: Thomas Hein

# This file will connect to the database and
# perform any functions that should be executed
# on the start of the API

# Check the current working directory and remove /functions if it's there
if (substr(getcwd(), -9) == "functions") {
	$openDirectory = substr(getcwd(), 0, -13);
} else {
	$openDirectory = substr(getcwd(), 0, -3);
}

# Check the verifyinput file before opening it
if (!file_exists("$openDirectory\\v1\\functions\\verifyinput.php")) {
	die("Server-Side Error: Unable to find verify input file\n");
}

# Get the script to load functions for verifying user input
require_once "$openDirectory\\v1\\functions\\verifyinput.php";

# Check the file before loading it
if (!file_exists("$openDirectory/config.php")) {
	die("Server-Side Error: Unable to find configeration file\n");
}

# Get the config.php file to load important variables
require_once "$openDirectory/config.php";

# Connect to the database
$host = "host=" . $database_host;
$port = "port=" . $database_port;
$dbname = "dbname=" . $database_name;
$credentials = "user=" . $database_user . " password=" . $database_password;

$db = pg_connect("$host $port $dbname $credentials");
global $db;

# Check to see if there was an error
if (!$db) {
	show_error("Unable to open database\n", "Server Side Processing");
}

# List of functions to use over the entire API

# Show an error based in JSON format
function show_error($error_msg, $type = '$$$$$') {
	if ($type = '$$$$$') {
		$type = substr(basename($_SERVER['PHP_SELF']), 0, -4);
	}
	header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
	$reponseArray = array("request" => array("pass" => "false"), "main" => array($type => "false", "message" => $error_msg));

	# Errors MUST KILL
	die(json_encode($reponseArray));
}

# Query for the database
function db_pos_query($sql, $db) {
	# Get the results
	$ret = pg_query($db, $sql);

	# Check for any errors
	if (!$ret) {
		show_error("Database Error: " . pg_last_error($db), "Server Side Processing");
	}

	# It's all good
	return $ret;
}

# Checks for all the GET parameters in the array given
function checkGetSet($givenArray) {
	foreach ($givenArray as $element) {
		if (!isset($_GET[$element])) {
			return 0;
		}
	}
	return 1;
}

# Sanitize a variable/input
function clean($text) {
	# Note: Adds a quote to the beginning and end of the string
	return pg_escape_literal($text);
}

# This should be run when the php connection is about to close
function quit($db) {
	pg_close($db);
}
