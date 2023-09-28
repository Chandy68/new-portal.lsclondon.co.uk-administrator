<?php
require_once('../../config.php');
require_once("../classes/database.class.php");
require_once("../classes/query.class.php");
$sid = $USER->idnumber;
$query = new Query();
$database = new Database();
$results = $database->executeQuery($query->getAttendanceFromStudentID($sid));
$termId = "";
echo "<table  class='table table-striped table-sm'>";
echo "<tr><th>Term</th><th>Course</th><th>Result</th><th>Marks</th><th></th></tr>";
foreach ($results as $result) {
    echo "<tr>";
    if ($result['Result'] != "") {
        $termId = $result['termid'];
        echo "<td>" . $result['term'] . "</td>";
        echo "<td><strong>" . $result['Code'] . "</strong></td>";
        echo "<td>" . $result['Result'] . "</td>";
        echo "<td>" . $result['avgmarks'] . "%</td>";
        echo '<td><a href="../administrator/my/result.php"><svg class="bi bi-arrow-right" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M10.146 4.646a.5.5 0 01.708 0l3 3a.5.5 0 010 .708l-3 3a.5.5 0 01-.708-.708L12.793 8l-2.647-2.646a.5.5 0 010-.708z" clip-rule="evenodd"/>
  <path fill-rule="evenodd" d="M2 8a.5.5 0 01.5-.5H13a.5.5 0 010 1H2.5A.5.5 0 012 8z" clip-rule="evenodd"/>
</svg></a></td>';
    }
    echo "</tr>";
}
echo "</table>";

$results = $database->executeQuery($query->getProgressionPath($termId, $sid));
foreach ($results as $result) {
    echo "<strong>Progress path: </strong>" . $result['ProgressPath'];
}