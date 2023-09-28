<?php

require_once "../lib/MoodleRest.php";

if (isset($_GET['student_id']) && isset($_GET['course_id']) && isset($_GET['group_name'])) {
    $studentId = $_GET['student_id'];
    $courseId = $_GET['course_id'];
    $groupName = $_GET['group_name'];
    $rest = new MoodleRest('http://new-portal.lsclondon.co.uk/webservice/rest/server.php', 'aa42fd94fc941ba61b8e684014678484');
    $students = $rest->request('core_user_get_users', array("criteria" => array(array("key" => "idnumber", "value" => $studentId))));
    $courses = $rest->request('core_course_get_courses_by_field', array("field" => "idnumber", "value" => $courseId));
    $groups = $rest->request('core_group_get_course_groups', array("courseid" => $courses['courses'][0]["id"]));
    $group_id='';
    foreach ($groups as $group) {
        if ($group["name"] == $groupName) {
            $group_id = $group["id"];
        }
    }
    $groupArray = array();
    $groupArray['groupid'] = $group_id;
    $groupArray['userid'] = $students["users"][0]['id'];
    $responses = $this->rest->request('core_group_delete_group_members', array("members" => array($groupArray)));
    print_r($responses);
} else {
    echo 'false';
}
