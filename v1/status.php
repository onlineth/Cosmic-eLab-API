<?php
# Author: Thomas Hein

# The purpose of this file is to display the status of the API
# It will do a basic check of the connection to the database
# as well as check if important files exsists. It only will
# show the first error it finds and then die.

# Disable error reporting from PHP
error_reporting(0);

# Check the file before loading it
if (!file_exists("functions/start.php")) {
	die("Server-Side Error: Unable to find API start script file\n");
}

# Get the start script to connect to database and execute possible other functions
require_once "functions/start.php";

# Check the file before loading it
if (!file_exists("functions/filefunc.php")) {
	die("Server-Side Error: Unable to find file functions script file\n");
}

# Get the start script to connect to database and execute possible other functions
require_once "functions/filefunc.php";

# Check if PHP had an error
if (error_get_last()) {
	show_error("Internal PHP error\n", "Server Side Processing");
}

# Basic checks have been done
$reponseArray = array("request" => array("pass" => "true"), "main" => array("status" => "operational", "message" => "A basic test shows the API fully operational"));
print json_encode($reponseArray);
