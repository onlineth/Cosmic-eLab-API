<?php
# Provide a Unique ID OR the detectorID, year, monthday, and channel number
# This is currently the only file where JSON is NOT given
# This file will use the file system over the database

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

# Define the chunk size
define('CHUNK_SIZE', 1024*1024);

# Function for sending large files
function readfile_chunked($filepath, $retbytes = TRUE) {
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	$filename = trim(substr($filepath, strrpos($filepath, '/') + 1));
	header('Content-Disposition: attachment; filename="'.$filename.'"');
    $buffer = '';
    $cnt    = 0;
    $handle = fopen($filepath, 'rb');

    if ($handle === false) {
        return false;
    }

    while (!feof($handle)) {
        $buffer = fread($handle, CHUNK_SIZE);
        echo $buffer;
        ob_flush();
        flush();

        if ($retbytes) {
            $cnt += strlen($buffer);
        }
    }

    $status = fclose($handle);

    if ($retbytes && $status) {
        return $cnt; // return num. bytes delivered like readfile() does.
    }

    return $status;
}

# Ok, let's lookup the file
if ($filepath = lookUpFile($db, $data_location, $allowed_filetypes)) {
	if (!(readfile_chunked($filepath))) {
		show_error('Could not dump the file.', 'Server Side Error');
	}
} else {
    show_error('Could not find the file', 'getfile');
}

quit($db);
