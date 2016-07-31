<?php
# Author: Thomas Hein

# This file will help search for files. It requires a DetectorID, Year, and start & finish monthday.
# Someone may specify a the specific monthday, specific wanted files, and specific indexes.
# This will be limited to 500 entries. Increase the NEXT parameter to get more results.
# This does not search the file system but only the database. Use the checkfile script to be
# absolutely sure it's on the file system - although I don't know why files would go missing.
# Note: StartMonthDay & EndMonthDay are including in the results (like NOT up to the beginning of
#    EndMonthDay)

# Check the file before loading it
if (!file_exists("functions/start.php")) {
	die("Server-Side Error: Unable to find API start script file\n");
}

# Get the start script to connect to database and execute possible other functions
require_once "functions/start.php";

# Let's see what's given

# We need these
if (!(checkGetSet(array('detectorid', 'year')))) {
	show_error('You need to specify a DetectorID and a Year', 'searchfiles');
}

# Need either an exact monthday or a start monthday and finish monthday
if (!((checkGetSet(array('startmonthday', 'endmonthday'))) or (checkGetSet(array('monthday'))))) {
	show_error('You need to specify a StartMonthDay & an EndMonthDay OR simply give a MonthDay', 'searchfiles');
}

$arg_detectorid = $_GET['detectorid'];
$arg_year = zFix($_GET['year']);

# detector id should be a 4 or less digit integer
if (!(is_numeric($arg_detectorid)) or !(strlen($arg_detectorid) <= 4)) {
	show_error("The DetectorID is incorrect.", "searchfiles");
}

# year should be a 4 digit integer
if (!(is_numeric($arg_year)) or !(strlen($arg_year) == 4)) {
	show_error("The year given is incorrect.", "searchfiles");
}

if (checkGetSet(array('monthday'))) {
	$arg_monthday = zFix($_GET['monthday']);

	# monthday should be a 4 digit integer
	if (!(is_numeric($arg_monthday)) or !(strlen($arg_monthday) == 4)) {
		show_error("The MonthDay is incorrect.", "searchfiles");
	}
} else {
	$arg_startmonthday = zFix($_GET['startmonthday']);
	$arg_endmonthday = zFix($_GET['endmonthday']);

	# monthday should be a 4 digit integer
	if (!(is_numeric($arg_startmonthday)) or !(strlen($arg_startmonthday) == 4)) {
		show_error("The Start MonthDay is incorrect.", "searchfiles");
	}

	# monthday should be a 4 digit integer
	if (!(is_numeric($arg_endmonthday)) or !(strlen($arg_endmonthday) == 4)) {
		show_error("The End MonthDay is incorrect.", "searchfiles");
	}

	if ($arg_startmonthday > $arg_endmonthday) {
		show_error("The Start MonthDay cannot be greater than the End MonthDay.", "searchfiles");
	}
}

# At this point, we technically have everything we need to work with
# Let's first build the initial WHERE query and then add to it as we go along
$query_where = "WHERE active=true AND detectorid=$arg_detectorid AND year=$arg_year";

# Add monthday
if (isset($arg_monthday)) {
	$query_where = "$query_where AND monthday=$arg_monthday";
} else {
	$query_where = "$query_where AND monthday >= $arg_startmonthday AND monthday <= $arg_endmonthday";
}

# Add index if any
if (isset($_GET['index'])) {

	$arg_index = zFix($_GET['index']);

	# Index should be an integer
	if (!(is_numeric($arg_index))) {
		show_error("The Index should be a number.", "searchfiless");
	}

	$query_where = "$query_where AND index=$arg_index";
}

# Check if any filetypes are set
# Go through each filetype in the allowed list
foreach ($allowed_filetypes as $current_filetype) {

	# If the filetype has been set by the user
	if (isset($_GET[$current_filetype])) {

		$current_filetype_arg = $_GET[$current_filetype];

		# Check if it's a boolean (1 or 0)
		if (!($current_filetype_arg == '1' or $current_filetype_arg == '0')) {
			show_error("The FileType: $current_filetype must be an boolean.", "searchfiless");
		}
		# It's a boolean, add it to the where query
		# If it's not, remove it (all filetypes are shown by default & design)
		if (!($current_filetype_arg)) {
			# We don't want this filetype
			$query_where = "$query_where AND filetype!='$current_filetype'";

		}
	}
}

# ok, finish the rest of the query
$search_query = "SELECT * FROM api_files $query_where LIMIT 500";

# ok, we have our query done, let's get the results
# Perform the query and fetch the rows to an array
$query_result = pg_fetch_all(db_pos_query($search_query, $db));

# Check if any
if (!($query_result)) {
	# There aren't any
	$reponseArray = array("request" => array("pass" => "true"), "main" => array("searchfiles" => "true"), "filelist" => '', "numberOfFiles" => '0', 'limit' => 500);
	print json_encode($reponseArray);

} else {
	# There are file(s)
	# Setup our file list
	$file_list = array();

	# Run through each file
	foreach ($query_result as $cfr) {
		# $cfr means current file result (short to save space)
		$current_file_result_array = array('fileid' => $cfr['fileid'], 'detectorid' => $cfr['detectorid'], 'year' => $cfr['year'], 'monthday' => $cfr['monthday'], 'index' => $cfr['index'], 'filetype' => $cfr['filetype']);
		array_push($file_list, $current_file_result_array);
	}

	# All done, spit this out
	$reponseArray = array("request" => array("pass" => "true"), "main" => array("searchfiles" => "true"), "filelist" => $file_list, "numberOfFiles" => count($file_list), 'limit' => 500);
	print json_encode($reponseArray);
}

# Quitting time
quit($db);
