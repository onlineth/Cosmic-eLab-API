<?php
# Author: Thomas Hein

# File Functions
# I hate writting the same piece of code twice, so I create functions in files that will be used twice.

# Check the file before loading it
if (!file_exists("functions/start.php")) {
	die("Server-Side Error: Unable to find API start script file\n");
}

# Get the start script to connect to database and execute possible other functions
require_once("functions/start.php");

function lookUpFile($db, $data_location, $allowed_filetypes) {
	# This file will check to see if the arguments given in the GET request look ok
	# This file will return 1 if it has completed. It really shouldn't return anything
	# if it has failded as it will call "show_error".

	# Check to see if needed arguments are given
	if (((checkGetSet(array("detectorid", "filetype"))) or !(checkGetSet(array("fileid"))))) {
		if (!checkGetSet(array("detectorid", "filetype"))) {
			show_error("You do not have proper arguments setup for this request.", "filefound");
		}
		if (!(checkGetSet(array("detectorid", "year", "monthday", "index", "filetype"))) && $_GET['filetype'] != "geo") {
			show_error("You do not have proper arguments setup for this request.", "filefound");
		}

	}

	# Validate Credentials & set argument variables
	if (isset($_GET['fileid'])) {
		# Get the GET parameters into their respective variables
		$arg_fileid = $_GET['fileid'];

		# FileID should be an integer
		if (!is_numeric($arg_fileid)) {
		show_error("The FileID given is not numerical.", "filefound");
		}

		# Lookup the fileid in the database
		$search_query = "SELECT * FROM api_files WHERE fileid=$arg_fileid";

		# Perform the query and fetch the rows to an array
		$query_result = pg_fetch_row(db_pos_query($search_query, $db));

		# Check to see if there's something actually there
		if (!($query_result[0])) {
		show_error("No file has been found with the given FileID.", "filefound");
		}

		# Set the other arguments
		$arg_detectorid =  zFix($query_result[1]);
		$arg_year =  zFix($query_result[2]);
		$arg_monthday =  zFix($query_result[3]);
		$arg_index =  zFix($query_result[4]);
		$arg_filetype =  $query_result[5];



	} elseif ($_GET['filetype'] != "geo") {

		# Get the GET parameters into their respective variables
		$arg_detectorid =  zFix($_GET['detectorid']);
		$arg_year =  zFix($_GET['year']);
		$arg_monthday =  zFix($_GET['monthday']);
		$arg_index =  zFix($_GET['index']);
		$arg_filetype =  $_GET['filetype'];

		# detector id should be a 4 digit integer
		if (!(is_numeric($arg_detectorid)) or !(strlen($arg_detectorid) == 4)) {
		show_error("The DetectorID is inncorrect.", "filefound");
		}

		# year should be a 4 digit integer
		if (!(is_numeric($arg_year)) or !(strlen($arg_year) == 4)) {
		show_error("The year given is inncorrect.", "filefound");
		}

		# monthday should be a 4 digit integer
		if (!(is_numeric($arg_monthday)) or !(strlen($arg_monthday) == 4)) {
		show_error("The MonthDay is incorrect.", "filefound");
		}

		# FileType should be an integer
		if (!in_array($arg_filetype, $allowed_filetypes)) {
		show_error("The file type is not on the accepted file type list.", "filefound");
		}

		# Index should be an integer
		if (!(is_numeric($arg_index))) {
		show_error("The Index should be a number.", "filefound");
		}

	} else {
		# The filetype is for sure geo, just check the detectorid
		
		# Get the GET parameters into their respective variables
		$arg_detectorid =  zFix($_GET['detectorid']);
		$arg_filetype =  $_GET['filetype'];

		# detector id should be a 4 digit integer
		if (!(is_numeric($arg_detectorid)) or !(strlen($arg_detectorid) == 4)) {
		show_error("The DetectorID is inncorrect.", "filefound");
		}
	}

	# I don't know why there are spaces in here, but there are so...
	$arg_filetype = trim($arg_filetype);

	# Parse the arguments into a filename
	if ($arg_filetype == "geo") {
		$filename = "$arg_detectorid.$arg_filetype";
	} else {
		$filename = "$arg_detectorid.$arg_year.$arg_monthday.$arg_index";

		if ((($arg_filetype != "raw"))) {
			$filename = "$filename.$arg_filetype";
		}
	}

	# Real file path
	$realFilePath = $data_location.$arg_detectorid.'/'.$filename;

	# Check to see if the file is in the file system
	if (!file_exists($realFilePath)) {
		return 0;
	}

	# Finally, all done, return the actual file path
	return $realFilePath;
}