<?php
ob_start();
require_once('../config.php');
require_once 'classes/database.class.php';
$database = new Database();
$user = $USER->username;
//echo $sql_replace="REPLACE INTO assessment SET scsid=?, atid=?, MarksObtained=?, Comments=?, user=?;";
//print_r($_POST['query']);
if(isset($_POST['query'])){
    $query = str_replace("######",$user,$_POST['query']);
    //print_r($_POST['query']);
    $database->executeSqlQuery($query);
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Result Post</title>
</head>
<body>
Loading... Please Wait...<br><br><a href="https://new-portal.lsclondon.co.uk/administrator/">Click here</a> if you don't redirect automatically
</body>
</html>
<?php
ob_flush();
