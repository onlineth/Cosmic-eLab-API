<?php
# Author: Thomas Hein

# The purpose of this file is to Used to retrieve information about a particular file.
# Can also be used to make sure the file is on the disk before actually downloading it.
# The output includes if it was found, and if it was, providing information about the file
# like the DetectorID, Year, MonthDay, Index, and FileType.

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

# Ok, let's lookup the file
$fileinfo = lookUpFile($db, $data_location, $allowed_filetypes);

# And check for a path
if ($fileinfo[0]) {
	$reponseArray = array("request" => array("pass" => "true"), "main" => array("filefound" => "true", "fileinfo" => array("fileid" => $fileinfo[1], "detectorid" => $fileinfo[2], "year" => $fileinfo[3], "monthday" => $fileinfo[4], "index" => $fileinfo[5], "filetype" => $fileinfo[6])));
	print json_encode($reponseArray);
} else {
	$reponseArray = array("request" => array("pass" => "true"), "main" => array("filefound" => "false"));
	print json_encode($reponseArray);
}

quit($db);
