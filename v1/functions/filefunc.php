<?php
# Author: Thomas Hein

# File Functions
# I hate writing the same piece of code twice, so I create functions in files that will be used twice.

# Check the file before loading it
if (!file_exists("functions/start.php")) {
	die("Server-Side Error: Unable to find API start script file\n");
}

# Get the start script to connect to database and execute possible other functions
require_once "functions/start.php";

function lookUpFile($db, $data_location, $allowed_filetypes) {
	# This file will check to see if the arguments given in the GET request look ok
	# This file will return 1 if it has completed. It really shouldn't return anything
	# if it has fielded as it will call "show_error".

	# Check to see if needed arguments are given
	if (((checkGetSet(array("detectorid", "filetype"))) or !(checkGetSet(array("fileid"))))) {
		if (!checkGetSet(array("detectorid", "filetype"))) {
			show_error("You do not have proper arguments setup for this request.");
		}
		if (!(checkGetSet(array("detectorid", "year", "monthday", "index", "filetype"))) && $_GET['filetype'] != "geo") {
			show_error("You do not have proper arguments setup for this request.");
		}
	}

	# Validate Credentials & set argument variables
	if (isset($_GET['fileid'])) {
		# Get the GET parameters into their respective variables
		$arg_fileid = chk_fileid($_GET['fileid']);

		# Lookup the fileid in the database
		$search_query = "SELECT * FROM api_files WHERE fileid=$arg_fileid";

		# Perform the query and fetch the rows to an array
		$query_result = pg_fetch_row(db_pos_query($search_query, $db));

		# Check to see if there's something actually there
		if (!($query_result[0])) {
			show_error("No file has been found with the given FileID.");
		}

		# Set the other arguments
		$arg_detectorid = $query_result[1];
		$arg_year = zFix($query_result[2]);
		$arg_monthday = zFix($query_result[3]);
		$arg_index = zFix($query_result[4]);
		$arg_filetype = $query_result[5];

	} elseif ($_GET['filetype'] != "geo") {

		# Get the GET parameters into their respective variables
		$arg_detectorid = chk_detectorid($_GET['detectorid']);
		$arg_year = chk_year($_GET['year']);
		$arg_monthday = chk_monthday($_GET['monthday']);
		$arg_index = chk_index($_GET['index']);
		$arg_filetype = chk_filetype($_GET['filetype'], $allowed_filetypes);

		# Now get the FileID
		$search_query = "SELECT fileid FROM api_files WHERE detectorid=$arg_detectorid AND year=$arg_year and monthday=$arg_monthday AND index=$arg_index AND filetype='$arg_filetype'";

		# Perform the query and fetch the rows to an array
		$arg_fileid = pg_fetch_row(db_pos_query($search_query, $db))[0];

	} else {
		# The filetype is for sure geo, just check the detectorid

		# Get the GET parameters into their respective variables
		$arg_detectorid = chk_detectorid($_GET['detectorid']);
		$arg_filetype = chk_filetype($_GET['filetype'], $allowed_filetypes);

		# Set null to all the others
		$arg_year = null;
		$arg_monthday = null;
		$arg_index = null;
		$arg_fileid = pg_fetch_row(db_pos_query("SELECT fileid FROM api_files WHERE detectorid=$arg_detectorid and filetype='geo'", $db))[0];
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
	$realFilePath = $data_location . $arg_detectorid . '/' . $filename;

	# Check to see if the file is in the file system
	if (!file_exists($realFilePath)) {
		return 0;
	}

	# Finally, all done, return the actual file path
	return array($realFilePath, $arg_fileid, $arg_detectorid, $arg_year, $arg_monthday, $arg_index, $arg_filetype);
}