<?php

# This file is for verifying varius types of inputs given from the user

# This function fixes GET arguments where if the the leading number if 0 is removed,
# this will add it back as a string.
function zFix($num) {
	if (strlen($num) == 3) {
		return "0$num";
	}
	return $num;
}

# Detector ID
function chk_detectorid($arg_detectorid) {
	if (!(is_numeric($arg_detectorid)) or !(strlen($arg_detectorid) <= 4)) {
		show_error("The DetectorID is incorrect.", "searchfiles");
	} else {
		return $arg_detectorid;
	}
}

# Year
function chk_year($arg_year) {
	$arg_year = zFix($arg_year);
	if (!(is_numeric($arg_year)) or !(strlen($arg_year) == 4)) {
		show_error("The year given is incorrect.", "searchfiles");
	} else {
		return $arg_year;
	}
}

# MonthDay
function chk_monthday($arg_monthday, $specific_name = "MonthDay") {
	# Specific name for Start & End MonthDay in error message
	$arg_monthday = zFix($arg_monthday);
	if (!(is_numeric($arg_monthday)) or !(strlen($arg_monthday) == 4)) {
		show_error("The $specific_name is incorrect.", "searchfiles");
	} else {
		return $arg_monthday;
	}
}

# Index
function chk_index($arg_index) {
	$arg_index = zFix($arg_index);
	if (!(is_numeric($arg_index))) {
		show_error("The Index should be a number.", "searchfiless");
	} else {
		return $arg_index;
	}
}

# FileID
function chk_fileid($arg_fileid) {
	if (!is_numeric($arg_fileid)) {
		show_error("The FileID given is not numerical.");
	} else {
		return $arg_fileid;
	}
}

# FileType
function chk_filetype($arg_filetype, $allowed_filetypes) {
	if (!in_array($arg_filetype, $allowed_filetypes)) {
		show_error("The file type is not on the accepted file type list.");
	} else {
		return $arg_filetype;
	}
}