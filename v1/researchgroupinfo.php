<?php
# Author: Thomas Hein

# Input: ResearchGroupID or Name
# Output: School, Teacher, Detectors

# Check the file before loading it
if (!file_exists("functions/start.php")) {
	die("Server-Side Error: Unable to find API start script file\n");
}

# Get the start script to connect to database and execute possible other functions
require_once "functions/start.php";

# Either ID or Name
if (!(checkGetSet(array('researchgroupid'))) and (!(checkGetSet(array('researchgroupname'))))) {
	show_error('You need to specify either a ResearchGroupID or a ResearchGroupName.');
}

if (isset($_GET['researchgroupid'])) {
	# We were given the researchgroupID, let's use that
	$arg_researchgroupid = chk_researchgroupid($_GET['researchgroupid']);
	$return_query = pg_fetch_row(db_pos_query("SELECT * FROM research_group WHERE id=$arg_researchgroupid", $db));
	$arg_researchgroupname = $return_query[1];
} else {
	# The name was given
	$arg_researchgroupname = chk_researchgroupname($_GET['researchgroupname']);
	$return_query = pg_fetch_row(db_pos_query("SELECT * FROM research_group WHERE name=$arg_researchgroupname", $db));
	$arg_researchgroupid = $return_query[0];
}

# Check if there's nothing
if (!$return_query) {
	$reponseArray = array("request" => array("pass" => "true"), "main" => array("researchgroup" => "not-found"));
	print json_encode($reponseArray);
	die();
}

# Find the Teacher and School information
$return_query = pg_fetch_row(db_pos_query("SELECT *
FROM research_group rg
  INNER JOIN teacher tc
    on tc.id = rg.teacher_id
INNER JOIN school sc
    on sc.id = tc.school_id
WHERE rg.id = $arg_researchgroupid", $db));

$teacher_id = $return_query[2];
$school_name = $return_query[7];
$school_id = $return_query[5];

# Find Detectors
$return_query = pg_fetch_all(db_pos_query("SELECT distinct dt.detectorid
FROM (select research_group_id, detectorid from (SELECT ROW_NUMBER() OVER (PARTITION BY detectorid ORDER BY rnum2 DESC) as rnum1, research_group_id, detectorid FROM
  (SELECT ROW_NUMBER() OVER () as rnum2, research_group_id, detectorid FROM research_group_detectorid)
    as temp ORDER BY detectorid) as temp2 where temp2.rnum1 = 1) as dt
INNER JOIN research_group rg
    on rg.id = dt.research_group_id
WHERE rg.id = $arg_researchgroupid", $db));

# Print out the JSON
$reponseArray = array("request" => array("pass" => "true"), "main" => array("researchgroup" => "found", "researchgroupinfo" => array("id" => $arg_researchgroupid, "name" => $arg_researchgroupname), "schoolinfo" => array('id' => $school_id, 'name' => $school_name), 'teacherid' => $teacher_id, "detectors" => $return_query));
print json_encode($reponseArray);
