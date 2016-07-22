<?php
# Author: Thomas Hein

# This file will (quickly) run through every file in each detector's folder.
# Depending on how PHP is setup, this may not working in the production version.
# If this is the case, I can write a Python script to supplument.

# You may want to have this file get launched with a cron every say 15 minutes.
# Currently, this file can be executed by the public. This may have to change.

# Define some functions that will help but not really in any other script
function chk4n($test) {
	# This function will check to see if $test is 4 numbers
	if (is_numeric($test) && strlen($test) == 4) {
		return 1;
	} else {
		return 0;
	}
}

# Check the file before loading it
if (!file_exists("start.php")) {
	die("Server-Side Error: Unable to find API start script file\n");
}

# Get the start script to connect to database and execute possible other functions
require_once "start.php";

# Scan the directory
$data_location_results = scandir($data_location);

# Initiate a counter
$counter = 0;

# Set all files to inactive and then reactivate them as you go along.
# This script acounts for deleted files
db_pos_query("UPDATE api_files SET active=false", $db);

# The loop for directorys
foreach ($data_location_results as $current_detector_dir) {
	# Skip this stuff
	if ($current_detector_dir === '.' or $current_detector_dir === '..') {
		continue;
	}

	if (is_dir($data_location . '/' . $current_detector_dir)) {
		# It's a directory, let's check to make sure it's a detector

		if (chk4n($current_detector_dir)) {
			# It's a directory that's name has 4 numbers (looks like a detector's folder)

			# Let's loop over the files in that folder
			$current_detector_dir_results = scandir($data_location . '/' . $current_detector_dir);

			# The loop for individual files
			foreach ($current_detector_dir_results as $current_file) {
				# Skip this stuff
				if ($current_file === '.' or $current_file === '..') {
					continue;
				}

				# Let's pick the current file apart and check if
				# if (!$last_pos_per = strrpos($current_file, '.')) continue;
				$current_file_detectorID = substr($current_file, 0, 4);

				# Check if it's a geo file
				if ($current_file == "$current_file_detectorID.geo") {
					# It is a geo file
					$current_file_year = '0';
					$current_file_monthday = '0';
					$current_file_index = '0';
					$current_file_type = 'geo';
				} else {
					# It is not a geo file
					$current_file_year = substr($current_file, 5, 4);
					$current_file_monthday = substr($current_file, 10, 4);

					# Check to see if the initial part we have matches
					$current_file_firstPart = "$current_file_detectorID.$current_file_year.$current_file_monthday.";
					if (!(substr($current_file, 0, 15) == $current_file_firstPart)) {
						continue;
					}

					$current_file_lastPart = substr($current_file, 15);

					# Check to see if it's raw
					if (strrpos($current_file_lastPart, '.')) {
						# It's not raw
						$current_file_type = substr($current_file_lastPart, strpos($current_file_lastPart, '.') + 1);
						$current_file_index = substr($current_file_lastPart, 0, strpos($current_file_lastPart, '.'));
					} else {
						# It's raw
						$current_file_type = 'raw';
						$current_file_index = $current_file_lastPart;
						# print "Raw Index: $current_file -- $current_file_detectorID.$current_file_year.$current_file_monthday.$current_file_index<br>";
					}

					# Check to see if what we have is the real thing
					if ($current_file_type == 'raw') {
						if ($current_file != "$current_file_detectorID.$current_file_year.$current_file_monthday.$current_file_index") {
							continue;
						}

					} else {
						if ($current_file != "$current_file_detectorID.$current_file_year.$current_file_monthday.$current_file_index.$current_file_type") {
							continue;
						}

					}
				}
				# Ok, we have everything ready to go - let's add this stuff to the db
				# Check if it's not in there already

				$search_query = "SELECT * FROM api_files WHERE detectorid=$current_file_detectorID AND year=$current_file_year AND monthday=$current_file_monthday AND index=$current_file_index AND filetype='$current_file_type'";

				if (!(pg_fetch_row(db_pos_query($search_query, $db))[0])) {
					# It's not in there, let's add it - finally
					db_pos_query("INSERT INTO api_files (detectorid,year,monthday,index,filetype) VALUES ($current_file_detectorID, $current_file_year, $current_file_monthday, $current_file_index, '$current_file_type' );", $db);
					$counter++;
				} else {
					# It's in there, so let's reactivate it
					db_pos_query("UPDATE api_files SET active=true WHERE detectorid=$current_file_detectorID AND year=$current_file_year AND monthday=$current_file_monthday AND index=$current_file_index AND filetype='$current_file_type'", $db);
				}
			}
		}
	}
}

# Quit
quit($db);

print "This build has added $counter files and has run successfully assuming no errors has been seen above or below this message.";
