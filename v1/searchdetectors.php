<?php
# Author: Thomas Hein

# Input:
#    Required: State
#    Optional: Research Group (id/name), School (id/name),
#              State (id/name), City (id/name), Teacher (id)
# Output: DetectorIDs

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

# Get the required state
# Either ID or Name
if (!((checkGetSet(array('stateid'))) or (checkGetSet(array('statename'))))) {
	show_error('You need to specify either a StateID or a StateName.');
}

if (isset($_GET['stateid'])) {
	# We were given the stateID, let's use that
	$arg_stateid = chk_stateid($_GET['stateid']);
	$return_query = pg_fetch_row(db_pos_query("SELECT * FROM state WHERE id=$arg_stateid", $db));
} else {
	# The name was given
	# Note: state Names are not required to be unique in the db table
	$arg_statename = chk_statename($_GET['statename']);
	$return_query = pg_fetch_row(db_pos_query("SELECT * FROM state WHERE name=$arg_statename", $db));
	$arg_stateid = $return_query[0];
}

# Check if there's nothing
if (!$return_query) {
	show_error("The State could not be found.");
	print json_encode($reponseArray);
	die();
}

# Init the Where
$where = "st.id = $arg_stateid";

# Now, check for any other optional arguments
# Research Groups
if ((checkGetSet(array('researchgroupid'))) or (checkGetSet(array('researchgroupname')))) {
	if ((checkGetSet(array('researchgroupid')))) {
		$arg_researchgroupid = chk_researchgroupid($_GET['researchgroupid']);
		$return_query = pg_fetch_row(db_pos_query("SELECT * FROM research_group WHERE id=$arg_researchgroupid", $db));
	} else {
		$arg_researchgroupname = chk_researchgroupname($_GET['researchgroupname']);
		$return_query = pg_fetch_row(db_pos_query("SELECT * FROM research_group WHERE name=$arg_researchgroupname", $db));
		$arg_researchgroupid = $return_query[0];
	}
	if (!($return_query)) {
		show_error("The ResearchGroupID/Name could not be found.");
		print json_encode($reponseArray);
		die();
	}
	$where = $where . " AND rg.id=$arg_researchgroupid";
}

# School
if ((checkGetSet(array('schoolid'))) or (checkGetSet(array('schoolname')))) {
	if ((checkGetSet(array('schoolid')))) {
		$arg_schoolid = chk_schoolid($_GET['schoolid']);
		$return_query = pg_fetch_row(db_pos_query("SELECT * FROM school WHERE id=$arg_schoolid", $db));
	} else {
		$arg_schoolname = chk_schoolname($_GET['schoolname']);
		$return_query = pg_fetch_row(db_pos_query("SELECT * FROM school WHERE name=$arg_schoolname", $db));
		$arg_schoolid = $return_query[0];
	}
	if (!($return_query)) {
		show_error("The SchoolID/Name could not be found.");
		print json_encode($reponseArray);
		die();
	}
	$where = $where . " AND rg.id=$arg_schoolid";
}

# City
if ((checkGetSet(array('cityid'))) or (checkGetSet(array('cityname')))) {
	if ((checkGetSet(array('cityid')))) {
		$arg_cityid = chk_cityid($_GET['cityid']);
		$return_query = pg_fetch_row(db_pos_query("SELECT * FROM city WHERE id=$arg_cityid", $db));
	} else {
		$arg_cityname = chk_cityname($_GET['cityname']);
		$return_query = pg_fetch_row(db_pos_query("SELECT * FROM city WHERE name=$arg_cityname", $db));
		$arg_cityid = $return_query[0];
	}
	if (!($return_query)) {
		show_error("The CityID/Name could not be found.");
		print json_encode($reponseArray);
		die();
	}
	$where = $where . " AND ct.id=$arg_cityid";
}

# Teacher
if (checkGetSet(array('teacherid'))) {
	$arg_teacherid = chk_teacherid($_GET['teacherid']);
	$return_query = pg_fetch_row(db_pos_query("SELECT * FROM teacher WHERE id=$arg_teacherid", $db));
	if (!($return_query)) {
		show_error("The TeacherID could not be found.");
		print json_encode($reponseArray);
		die();
	}
	$where = $where . " AND tc.id=$arg_teacherid";
}

# Lookup Detector
$return_query = pg_fetch_all(db_pos_query("SELECT detectorid
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
WHERE $where", $db));

if ($return_query) {
	$detectors_f = "found";
} else {
	$detectors_f = "not-found";
}

$reponseArray = array("request" => array("pass" => "true"), "main" => array("detectors" => $detectors_f, "list" => $return_query));
print json_encode($reponseArray);
