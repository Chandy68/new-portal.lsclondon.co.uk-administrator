<?php
error_reporting(-1);
require_once('../../config.php');
require_once("../classes/database.class.php");
require_once("../classes/query.class.php");
$sid = $USER->idnumber;
$student = explode('@', $USER->username)[0];
$query = new Query();
$database = new Database();
$get_attendance_from_student_id = $database->executeQuery($query->getAttendanceFromStudentID($sid));
echo "<table  class='table table-striped table-sm'>";
echo "<tr><th>Sub</th><th>Date & Time</th><th></th></tr>";
foreach ($get_attendance_from_student_id as $attendance) {
    $lectures = $database->executeQuery($query->getTimetable($attendance['scsid']));
    foreach ($lectures as $lecture) {
        echo "<tr>";
        echo "<td>" . $lecture['sub'] . "</td>";
        echo "<td><strong>" . $lecture['mydate'] . ", " .substr($lecture['starttime'], 0, -3)  . "</strong></td>";

        echo '<td><button class="btn btn-primary" onClick=\'StoreAttendance("' . $student . '", "' . $lecture['lecturerCode'] . '", "' . $lecture['zoom_link'] . '" , "' . $lecture['lecturedate'] . '", "' . $lecture['starttime'] . '")\'>Join</button></td>';
        echo "</tr>";
    }
}
echo "</table>";