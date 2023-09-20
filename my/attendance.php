<?php

require_once('../../config.php');
require_once('../classes/database.class.php');
require_once('../classes/query.class.php');
$PAGE->set_title("My Attendance");
$PAGE->set_heading("My Attendance");
echo $OUTPUT->header();
$database = new Database();
$query = new Query();
$sid = $USER->idnumber;
//echo $query->getAttendanceFromStudentID($sid);
$results = $database->executeQuery($query->getAttendanceFromStudentID($sid));
foreach ($results as $result) {
    echo "<table  class='table table-striped'>";
    echo "<tr><th>Term</th><th>Course</th><th>Attendance</th></tr>";
    echo "<tr>";
    echo "<th>" . $result['term'] . "</th>";
    echo "<th>" . $result['description'] . " (<strong>" . $result['Code'] . "</strong>)</th>";
    echo "<th>" . $result['attendance'] . "%</th>";
    echo "</tr>";
    echo "</table>";
    //$attendance_q=$query->getAttendanceFromScsid($result['scsid']);
    //echo $attendance_q;
    $attendances = $database->executeQuery($query->getAttendanceFromScsid($result['scsid']));
    echo "<table  class='table table-striped table-sm'>";
    echo "<tr><th>Day, Date</th><th>Start Time</th><th>End Time</th><th>Location</th><th>Attendance Time</th><th>Attendance</th></tr>";
    foreach ($attendances as $attendance) {
        echo "<tr>";
        echo "<td>" . $attendance['mydate'] . "</td>";
        echo "<td>" . $attendance['starttime'] . "</td>";
        echo "<td>" . $attendance['endtime'] . "</td>";
        echo "<td>" . $attendance['location'] . "</td>";
        echo "<td>" . $attendance['attendancetime'] . "</td>";
        echo "<td>" . $attendance['atten'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br><br>";
} 
echo $OUTPUT->footer();
