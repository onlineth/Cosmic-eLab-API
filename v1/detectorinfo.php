<?php
# Author: Thomas Hein

# Input: DetectorID
# Output: Research Group, Teacher, School, City, and State

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

# Kind of essential
if (!(checkGetSet(array('detectorid')))) {
	show_error('You need to specify a DetectorID');
}

$detectorid = chk_detectorid($_GET['detectorid']);

# Lookup Detector
$return_query = pg_fetch_row(db_pos_query("SELECT *
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
WHERE dt.detectorid = $detectorid", $db));

$research_group_id = $return_query[2];
$research_group_name = $return_query[3];
$teacher_id = $return_query[4];
$school_id = $return_query[8];
$school_name = $return_query[9];
$city_id = $return_query[11];
$city_name = $return_query[12];
$state_id = $return_query[14];
$state_name = $return_query[15];
$state_abb = $return_query[16];

$reponseArray = array("request" => array("pass" => "true"), "main" => array("detector" => "found", "detectorid" => $detectorid, "school" => array("id" => $school_id, "name" => $school_name), "city" => array("id" => $city_id, "name" => $city_name), "state" => array("id" => $state_id, "name" => $state_name, "abbreviation" => $state_abb), "research_group" => array("id" => $research_group_id, "name" => $research_group_name)));
print json_encode($reponseArray);
