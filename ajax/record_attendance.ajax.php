<?php

require_once('../classes/database.class.php');
require_once('../classes/query.class.php');

$query = new Query();
$database = new Database();
$insert = $query->markAttendance($_GET['studentid'], $_GET['lecturer']);
$database->executeSqlQuery($insert);

print_r($insert);

