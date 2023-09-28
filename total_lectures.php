<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=total_lectures.csv');

require_once 'classes/database.class.php';

$db = new Database();

$lectures = $db->executeQuery("SELECT car.StudentID AS `sid`, GET_TOTAL_LECTURES(s.id, car.TermID) AS `total` FROM CourseAdminReport car INNER JOIN student s ON s.StudentID = car.StudentID");

$fp = fopen('php://output', 'w');
$delimiter = ",";

$fields = array('StudentID', 'Total Lectures');

foreach ($lectures as $lecture) {
    fputcsv($fp, $lecture, $delimiter);
}

fclose($fp);

exit;