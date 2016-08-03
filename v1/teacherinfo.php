<?php
# Author: Thomas Hein

# Input: Teacher's ID
# Output: Research Groups, Schools, Detetors

# Check the file before loading it
if (!file_exists("functions/start.php")) {
	die("Server-Side Error: Unable to find API start script file\n");
}

# Get the start script to connect to database and execute possible other functions
require_once "functions/start.php";

# Just the ID (do not have access to names)
if (!(checkGetSet(array('teacherid')))) {
	show_error('You need to specify either a TeacherID.');
}

$arg_teacherid = chk_teacherid($_GET['teacherid']);

# Find Schools & Research Groups
$return_query = db_pos_query("SELECT *
FROM teacher tc
INNER JOIN school sc
  on sc.id = tc.school_id
INNER JOIN city ct
    on ct.id = sc.city_id
INNER JOIN state st
  on st.id = ct.state_id
INNER JOIN research_group rg
  on rg.teacher_id = tc.id
WHERE tc.id = $arg_teacherid", $db);

$schools = array_combine(array_unique(pg_fetch_all_columns($return_query, 2)), array_unique(pg_fetch_all_columns($return_query, 3)));
$research_groups = array_combine(array_unique(pg_fetch_all_columns($return_query, 12)), array_unique(pg_fetch_all_columns($return_query, 13)));

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
WHERE tc.id = $arg_teacherid", $db));

$reponseArray = array("request" => array("pass" => "true"), "main" => array("teacher" => "found", "teacherinfo" => array("id" => $arg_teacherid), "schools" => $schools, "research_groups" => $research_groups, "detectors" => $return_query));
print json_encode($reponseArray);