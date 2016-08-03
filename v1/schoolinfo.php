<?php
# Author: Thomas Hein

# Input: School or ID
# Output: DetectorID's, Teachers, City, State, ResearchGroup ID's

# Check the file before loading it
if (!file_exists("functions/start.php")) {
	die("Server-Side Error: Unable to find API start script file\n");
}

# Get the start script to connect to database and execute possible other functions
require_once "functions/start.php";

# Either ID or Name
if (!((checkGetSet(array('schoolid'))) or (checkGetSet(array('schoolname'))))) {
	show_error('You need to specify either a SchoolID or a SchoolName.');
}

if (isset($_GET['schoolid'])) {
	# We were given the schoolID, let's use that
	$arg_schoolid = chk_schoolid($_GET['schoolid']);
	$return_query = pg_fetch_row(db_pos_query("SELECT * FROM school WHERE id=$arg_schoolid", $db));
	$arg_schoolname = $return_query[1];
} else {
	# The name was given
	# Note: school Names are not required to be unique in the db table
	$arg_schoolname = chk_schoolname($_GET['schoolname']);
	$return_query = pg_fetch_row(db_pos_query("SELECT * FROM school WHERE name=$arg_schoolname", $db));
	$arg_schoolid = $return_query[0];
}

# Check if there's nothing
if (!$return_query) {
	$reponseArray = array("request" => array("pass" => "true"), "main" => array("school" => "not-found"));
	print json_encode($reponseArray);
	die();
}

# Find the City, State, School, and Teachers
$return_query = (db_pos_query("SELECT *
FROM research_group rg
  INNER JOIN teacher tc
    on tc.id = rg.teacher_id
INNER JOIN school sc
    on sc.id = tc.school_id
INNER JOIN city ct
    on ct.id = sc.city_id
INNER JOIN state st
  on st.id = ct.state_id
WHERE sc.id=$arg_schoolid", $db));

# Get research groups
$research_groups = array_unique(pg_fetch_all_columns($return_query, 1));
$teacher_ids = array_values(array_unique(pg_fetch_all_columns($return_query, 2)));

# Get City & State
$return_row = pg_fetch_row($return_query);
$city_id = $return_row[8];
$city_name = $return_row[10];
$state_id = $return_row[12];
$state_name = $return_row[13];
$state_abb = $return_row[14];

# Find Detectors
$return_query = pg_fetch_all(db_pos_query("SELECT distinct dt.detectorid
FROM (select research_group_id, detectorid from (SELECT ROW_NUMBER() OVER (PARTITION BY detectorid ORDER BY rnum2 DESC) as rnum1, research_group_id, detectorid FROM
  (SELECT ROW_NUMBER() OVER () as rnum2, research_group_id, detectorid FROM research_group_detectorid)
    as temp ORDER BY detectorid) as temp2 where temp2.rnum1 = 1) as dt
INNER JOIN research_group rg
    on rg.id = dt.research_group_id
INNER JOIN teacher tc
    on tc.id = rg.teacher_id
INNER JOIN school sc
    on sc.id = tc.school_id
WHERE sc.id = $arg_schoolid", $db));

$reponseArray = array("request" => array("pass" => "true"), "main" => array("school" => "found", "schoolinfo" => array("id" => $arg_schoolid, "name" => $arg_schoolname), "city" => array("id" => $city_id, "name" => $city_name), "stateinfo" => array("id" => $state_id, "name" => $state_name, "abbreviation" => $state_abb), "research_groups" => $research_groups, "teacher_ids" => $teacher_ids, "detectors" => $return_query));
print json_encode($reponseArray);
