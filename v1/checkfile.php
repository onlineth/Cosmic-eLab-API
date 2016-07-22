<?php
# Author: Thomas Hein

# The purpose of this file is to make sure a file exists on the file system before downloading it.
# This file will send a response in JSON (the GetFile script will not, thus the existence of this script).
# It will NOT check the database but will check the file on the system internally.

# Check the file before loading it
if (!file_exists("functions/start.php")) {
	die("Server-Side Error: Unable to find API start script file\n");
}

# Get the start script to connect to database and execute possible other functions
require_once("functions/start.php");

# Check the file before loading it
if (!file_exists("functions/filefunc.php")) {
	die("Server-Side Error: Unable to find file functions script file\n");
}

# Get the start script to connect to database and execute possible other functions
require_once("functions/filefunc.php");

# Ok, let's lookup the file
if (lookUpFile($db, $data_location, $allowed_filetypes)) {
	$reponseArray = array ("request" => array("pass" => "true"), "main" => array("filefound" => "true"));
	print json_encode($reponseArray);
} else {
	$reponseArray = array ("request" => array("pass" => "true"), "main" => array("filefound" => "false"));
	print json_encode($reponseArray);
}

quit($db);
