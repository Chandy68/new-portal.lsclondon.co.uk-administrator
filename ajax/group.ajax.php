<?php

require_once "../classes/database.class.php";
require_once "../classes/query.class.php";

$database = new Database();
$query = new Query();

$groups = $database->executeQuery($query->GetGroupNames($_GET['term_id']));
echo "<option value='-1'>Select a group</option>";
foreach ($groups as $group) {
    echo "<option value='" . $group["group_name"] . "'>" . $group["group_name"] . "</option>";
}
