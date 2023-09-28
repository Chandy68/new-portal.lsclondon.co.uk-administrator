<?php
require_once('../../config.php');
require_once("../classes/database.class.php");
require_once("../classes/query.class.php");

$student = $USER->username;
if (strpos($student, "student") !== false) {
  $sid = explode("@", $student)[0];
$query = new Query();
$database = new Database();
$results = $database->executeQuery($query->getTimetableLink($sid));
echo "<table  class='table table-striped table-sm table-hover'>";
echo "<tr><th>Term</th><th>Link</th></tr>";
foreach ($results as $result) {
echo "<tr>";
echo "<td>" . $result['Term'] . "</td>";
echo "<td><a href='" . $result['timetable_link'] . "' target='_blank'>Click here</a></td>";
echo "</tr>";
}
echo "</table>";
}