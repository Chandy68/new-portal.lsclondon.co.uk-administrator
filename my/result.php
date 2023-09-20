<?php

require_once('../../config.php');
require_once('../classes/database.class.php');
require_once('../classes/query.class.php');
$PAGE->set_title("My Results");
$PAGE->set_heading("My Results");
echo $OUTPUT->header();
$database = new Database();
$query = new Query();
$sid = $USER->idnumber;
$results = $database->executeQuery($query->getAttendanceFromStudentID($sid));
$termId = "";
echo "<table  class='table table-striped'>";
echo "<tr><th>Term</th><th>Course</th><th>Cohort</th><th>Result</th><th>Marks</th><th>Description</th></tr>";

$results_temp = $database->executeQuery($query->getAttendanceFromStudentID($sid));
$term_name = array();
foreach ($results_temp as $result){
    array_push($term_name, $result['termid']);
    break;
}

foreach ($results as $result) {
    if ($result['Result'] != "") {
        echo "<tr>";
        $termId = $result['termid'];
        echo "<td>" . $result['term'] . "</td>";
        echo "<td>" . $result['description'] . " <strong>(" . $result['Code'] . ")</strong></td>";
        echo "<td>" . $result['cohort'] . "</td>";
        echo "<td>" . $result['Result'] . "</td>";
        echo "<td>" . $result['avgmarks'] . "%</td>";
        echo "<td>" . $result['assesmentdesc'] . "</td>";
        echo "</tr>";

        if (in_array($result['termid'], $term_name)) {
            $results2 = $database->executeQuery($query->getProgressionPath($termId, $sid));
            foreach ($results2 as $result2) {
                if ($result2['ProgressPath'] != "") {
                    echo "<tr class='bg-danger'>";
                    echo "<td colspan='6' style='color:#fff !important;'>";
                } else {
                    echo "<tr>";
                    echo "<td colspan='6'>";
                }
                echo "<strong>Progress path: </strong>" . $result2['ProgressPath'];
                echo "</td>";
                echo "</tr>";
            }
        }
        array_push($term_name, $result['termid']);
    }
}
echo "</table>";


echo $OUTPUT->footer();
