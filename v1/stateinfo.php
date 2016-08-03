<?php
# Author: Thomas Hein

# Input: State or its ID
# Output: Cities, Schools, Detectors

# Check the file before loading it
if (!file_exists("functions/start.php")) {
	die("Server-Side Error: Unable to find API start script file\n");
}

# Get the start script to connect to database and execute possible other functions
require_once "functions/start.php";

# Either ID or Name
if (!((checkGetSet(array('stateid'))) or (checkGetSet(array('statename'))))) {
	show_error('You need to specify either a StateID or a StateName.');
}

if (isset($_GET['stateid'])) {
	# We were given the stateID, let's use that
	$arg_stateid = chk_stateid($_GET['stateid']);
	$return_query = pg_fetch_row(db_pos_query("SELECT * FROM state WHERE id=$arg_stateid", $db));
	$arg_statename = $return_query[1];
} else {
	# The name was given
	# Note: state Names are not required to be unique in the db table
	$arg_statename = chk_statename($_GET['statename']);
	$return_query = pg_fetch_row(db_pos_query("SELECT * FROM state WHERE name=$arg_statename", $db));
	$arg_stateid = $return_query[0];
}
$state_abb = $return_query[2];

# Check if there's nothing
if (!$return_query) {
	$reponseArray = array("request" => array("pass" => "true"), "main" => array("state" => "not-found"));
	print json_encode($reponseArray);
	die();
}

# Schools and Cites
$return_query = db_pos_query("SELECT *
FROM school sc
INNER JOIN city ct
    on ct.id = sc.city_id
INNER JOIN state st
  on st.id = ct.state_id
WHERE st.id = $arg_stateid", $db);

$schools = array_combine(array_unique(pg_fetch_all_columns($return_query, 0)), array_unique(pg_fetch_all_columns($return_query, 1)));
$cities = array_combine(array_unique(pg_fetch_all_columns($return_query, 3)), array_unique(pg_fetch_all_columns($return_query, 4)));

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
INNER JOIN city ct
    on sc.city_id = ct.id
INNER JOIN state st
    on st.id = ct.state_id
WHERE st.id = $arg_stateid", $db));

$reponseArray = array("request" => array("pass" => "true"), "main" => array("state" => "found", "stateinfo" => array("id" => $arg_stateid, "name" => $arg_statename, "abbreviation" => $state_abb), "cities" => $cities, "schools" => $schools, "detectors" => $return_query));
print json_encode($reponseArray);