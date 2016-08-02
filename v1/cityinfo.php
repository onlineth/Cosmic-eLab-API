<?php
# Author: Thomas Hein

# Input: City or it's ID
# Output: State, Schools, Detetors

# Check the file before loading it
if (!file_exists("functions/start.php")) {
	die("Server-Side Error: Unable to find API start script file\n");
}

# Get the start script to connect to database and execute possible other functions
require_once "functions/start.php";

# Either ID or Name
if ((checkGetSet(array('cityid'))) and (checkGetSet(array('cityname')))) {
	show_error('You need to specify either a CityID or a CityName.');
}

if (isset($_GET['cityid'])) {
	# We were given the CityID, let's use that
	$arg_cityid = chk_cityid($_GET['cityid']);
	$return_query = pg_fetch_row(db_pos_query("SELECT * FROM city WHERE id=$arg_cityid", $db));
	$arg_cityname = $return_query[1];
} else {
	# The name was given
	# Note: City Names are not required to be unique in the db table
	$arg_cityname = chk_cityname($_GET['cityname']);
	$return_query = pg_fetch_row(db_pos_query("SELECT * FROM city WHERE name=$arg_cityname", $db));
	$arg_cityid = $return_query[0];
}

# Check if there's nothing
if (!$return_query) {
	$reponseArray = array("request" => array("pass" => "true"), "main" => array("city" => "not-found"));
	print json_encode($reponseArray);
	die();
}

# Find the State
$state_id = $return_query[2];

$return_query = pg_fetch_row(db_pos_query("SELECT * FROM state WHERE id=$state_id", $db));

if ($return_query) {
	$state_name = $return_query[1];
	$state_abb = $return_query[2];
} else {
	$state_name = '';
	$state_abb = '';
}

# Find Research Groups
$return_query = pg_fetch_all(db_pos_query("SELECT distinct dt.detectorid
FROM (select research_group_id, detectorid from (SELECT ROW_NUMBER() OVER (PARTITION BY detectorid ORDER BY rnum2) as rnum1, research_group_id, detectorid FROM
  (SELECT ROW_NUMBER() OVER () as rnum2, research_group_id, detectorid FROM research_group_detectorid)
    as temp ORDER BY detectorid) as temp2 where temp2.rnum1 = 1) as dt
INNER JOIN research_group rg
    on rg.id = dt.research_group_id
INNER JOIN teacher tc
    on tc.id = rg.teacher_id
INNER JOIN school sc
    on sc.id = tc.school_id
INNER JOIN city ct
    on sc.city_id = ct.id
WHERE ct.id = $arg_cityid", $db));

$reponseArray = array("request" => array("pass" => "true"), "main" => array("city" => "found", "cityinfo" => array("id" => $arg_cityid, "name" => $arg_cityname), "stateinfo" => array("name" => $state_name, "abbreviation" => $state_abb), "detectors" => $return_query));
print json_encode($reponseArray);