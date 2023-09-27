<?php
ini_set('memory_limit', '4000M');

require_once "database.class.php";
require_once "query.class.php";
require_once "../lib/MoodleRest.php";

class Ajax extends Database
{
    private $intake, $term, $course, $student, $operation, $resit;
    private $query, $rest;
    private $token, $url, $debug;

    public function __construct($term, $course, $student, $operation, $resit, $group_name)
    {
        parent::__construct();

        if ($this->config['mode'] == "development") {
            $this->token = $this->config['dToken'];
            $this->url = $this->config['dUrl'];
        } elseif ($this->config['mode'] == "production") {
            $this->token = $this->config['pToken'];
            $this->url = $this->config['pUrl'];
        }

        $this->debug = $this->config['debug'];

        $this->query = new Query();
        $this->rest = new MoodleRest($this->url, $this->token);
        //$this->intake = $intake;
        $this->term = $term;
        $this->course = $course;
        $this->student = $student;
        $this->operation = $operation;
        $this->resit = $resit;
        $this->group_name = $group_name;
    }

    public function populate_portal()
    {
        $this->create_parent_categories();
        $this->create_courses();
        $this->transfer_students();
        $this->transfer_admins();
        $this->transfer_lecturers();
        $this->enrol_students();
        $this->enrol_lecturers();
        $this->enrol_admins();
        $this->create_groups();
        $this->assign_students_to_groups();
        $this->assign_lecturers_to_groups();
        $this->assign_admins_to_groups();
        $this->unenrol_students_from_courses();
        $this->unenrol_admins_from_courses();
        exit();
    }

    public function single_student_transfer()
    {
        $this->transfer_students();
        $this->enrol_students();
        $this->assign_students_to_groups();
    }

    public function commandline_groups_population()
    {
        $this->enrol_students();
        $this->assign_students_to_groups();
    }

    //Creating parent categories for all courses
    public function create_parent_categories()
    {
        $query = $this->query->create_parent_categories($this->term, $this->student,$this->course);
        echo "<pre>" . $query . "</pre>";
        //exit();
        $entries = $this->executeQuery($query);
        $flag = 0;
        foreach ($entries as $entry) {
            $flag++;
            $category = array();
            $category['name'] = $entry['parentCategory'];
            $category['parent'] = 0;
            $category['idnumber'] = $entry['parentCategory'];
            $category['description'] = $entry['parentCategory'];
            $category['descriptionformat'] = 1;
            $responses = $this->rest->request('core_course_create_categories', array("categories" => array($category)));
            if (array_key_exists("exception", $responses)) {
                echo '<div class="alert alert-danger" role="alert">Error ' . $category['idnumber'] . ' (' . $responses['errorcode'] . '): ' . $responses['message'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div>';
            } else {
                foreach ($responses as $response) {
                    echo '<div class="alert alert-primary" role="alert">Parent category created with id ' . $response['id'] . ' and name ' . $response['name'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div>';
                }
            }
            echo '<div class="collapse" id="collapse' . $flag . '"><div class="card card-body"><pre>' . print_r($entry, true) . '</pre></div></div>';
        }
    }

    //To create courses
    public function create_courses()
    {
        $query = $this->query->create_courses($this->term, $this->student, $this->course, $this->resit);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        //exit();

        $flag = 0;
        foreach ($entries as $entry) {
            $flag++;
            //print_r($entry);
            $category = $this->rest->request('core_course_get_categories', array("criteria" => array(array("key" => "idnumber", "value" => $entry['Level2']))));
            $course = array();
            $course['fullname'] = $entry['Fullname'];
            $course['shortname'] = $entry['Shortname'];
            $course['categoryid'] = $category[0]["id"];
            $course['idnumber'] = $entry['CourseID'];
            $responses = $this->rest->request('core_course_create_courses', array("courses" => array($course)));
            print_r($responses);
            //exit();
            if (array_key_exists("exception", $responses)) {
                $courses = $this->rest->request('core_course_get_courses_by_field', array("field" => "idnumber", "value" => $entry['CourseID']));
                //echo $this->query->moodle_course_created($entry['tslid'], $courses['courses'][0]["id"]);

                $sql=$this->query->moodle_courseId_created_SQL();
                $data=[
                    'mdl_course_id' =>$courses['courses'][0]["id"],
                    'id'            =>$entry['tslid'],
                ];
                $this->execute_update($sql,$data);

                //$this->executeSqlQuery($this->query->moodle_course_created($entry['tslid'], $courses['courses'][0]["id"]));
                echo '<div class="alert alert-danger" role="alert">Error ' . $course['shortname'] . ' (' . $responses['errorcode'] . '): ' . $responses['message'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
            } else {
                //exit();
                foreach ($responses as $response) {
                    //$this->executeQuery($this->query->moodle_course_created($entry['tslid'], $response['id']));
                    $sql=$this->query->moodle_courseId_created_SQL();
                    $data=[
                        'mdl_course_id' =>$response['courses'][0]["id"],
                        'id'            =>$entry['tslid'],
                    ];
                    $this->execute_update($sql,$data);

                    echo '<div class="alert alert-primary" role="alert">Parent category created with id ' . $response['id'] . ' and name ' . $response['shortname'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                }
            }
            echo '<div class="collapse" id="collapse' . $flag . '"><div class="card card-body"><pre>' . print_r($entry, true) . '</pre><hr><pre>' . print_r($category, true) . '</pre></div></div>';
        }
    }

    public function transfer_students()
    {
        $query = $this->query->transfer_students($this->term, $this->student, $this->course, $this->resit);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        $flag = 0;
        foreach ($entries as $entry) {
            if ($entry['Username'] != '') {
                $flag++;
                //print_r($entry);
                $students = $this->rest->request('core_user_get_users', array("criteria" => array(array("key" => "idnumber", "value" => $entry['Studentid']))));
                //print_r($students);
                if (count($students['users']) == 0) {   //If user DOES NOT exist on the Portal
                    $student = array();
                    $student['username'] = $entry['Username'];
                    $student['auth'] = 'oidc';
                    $student['firstname'] = $entry['Firstname'];
                    $student['lastname'] = $entry['Lastname'];
                    $student['email'] = $entry['Email'];
                    $student['idnumber'] = $entry['Studentid'];
                    $student['institution'] = $entry['city'];
                    $student['department'] = $entry['department'];
                    //print_r($student);
                    $responses = $this->rest->request('core_user_create_users', array("users" => array($student)));  // Create a new user on the portal
                    //print_r($responses);
                    foreach ($responses as $response) {
                        //$this->executeSqlQuery($this->query->moodle_user_created($entry['Studentid'], $response['id']));

                        $sql=$this->query->moodle_userId_created_SQL();
                        $data=[
                            'mdl_user_id'   =>$response['id'],
                            'id'            =>$entry['Studentid'],
                        ];
                        $this->execute_update($sql,$data);   //Update the mdl_user_id on the Student table

                        echo '<div class="alert alert-primary" role="alert">Student created on Moodle with id ' . $response['id'] . ' and username ' . $response['username'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                    }
                } else { // If user already exists, then just update the mdl_user_id on the Student table. This is required for old students whose mdl_user_id is NULL in the database

                    $sql=$this->query->moodle_userId_created_SQL();
                    $data=[
                        'mdl_user_id'   =>$students['users'][0]['id'],
                        'id'            =>$entry['Studentid'],
                    ];
                    $this->execute_update($sql,$data);

                    echo '<div class="alert alert-danger" role="alert">Student with id ' . $students['users'][0]['id'] . ' and username ' . $students['users'][0]['username'] . ' already exist on the Moodle database, updated the mdl_user_id on Student Table <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                }
                echo '<div class="collapse" id="collapse' . $flag . '"><div class="card card-body"><pre>' . print_r($entry, true) . '</pre><hr><pre>' . print_r($students, true) . '</pre></div></div>';
            }
        }
        echo $flag . " students have been transferred";
    }

    public function transfer_lecturers()
    {
        $query = $this->query->transfer_lecturers($this->term, $this->student, $this->course);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        //echo("Entries:");
        //print_r($entries);
        $flag = 0;
        foreach ($entries as $entry) {
            $flag++;
            $lecturers = $this->rest->request('core_user_get_users', array("criteria" => array(array("key" => "idnumber", "value" => $entry['LecturerID']))));
            //echo('Lecturers');
            //print_r($lecturers);
            if (count($lecturers['users']) == 0) {
                $lecturer = array();
                $lecturer['username'] = $entry['Username'];
                $lecturer['auth'] = 'oidc';
                $lecturer['firstname'] = $entry['Firstname'];
                $lecturer['lastname'] = $entry['Lastname'];
                $lecturer['email'] = $entry['Email'];
                $lecturer['idnumber'] = $entry['LecturerID'];
                $responses = $this->rest->request('core_user_create_users', array("users" => array($lecturer)));
                //print_r($responses);
                foreach ($responses as $response) {
                    //echo '<div class="alert alert-primary" role="alert">Lecturer created on Moodle with id ' . $response['id'] . ' and username ' . $response['username'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                    echo '<div class="alert alert-primary" role="alert">Lecturer created on Moodle with id ' . $lecturer['idnumber'] . ' and username ' . $entry['Username'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                }
            } else {
                echo '<div class="alert alert-danger" role="alert">Error: Lecturer with id ' . $lecturers['users'][0]['id'] . ' and username ' . $lecturers['users'][0]['username'] . ' already exist on the Moodle database. <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
            }
            echo '<div class="collapse" id="collapse' . $flag . '"><div class="card card-body">' . print_r($entry) . '</div></div>';
        }
    }

    public function transfer_admins()
    {
        $query = $this->query->transfer_admins($this->term, $this->student, $this->course);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        $flag = 0;
        foreach ($entries as $entry) {
            $flag++;
            $admins = $this->rest->request('core_user_get_users', array("criteria" => array(array("key" => "idnumber", "value" => $entry['AdminID']))));
            if (count($admins['users']) == 0) {
                $admin = array();
                $admin['username'] = $entry['Username'];
                $admin['auth'] = 'oidc';
                $admin['firstname'] = $entry['AdminName'];
                $admin['lastname'] = $entry['AdminSurname'];
                $admin['email'] = $entry['AdminEmail'];
                $admin['idnumber'] = $entry['AdminID'];
                $responses = $this->rest->request('core_user_create_users', array("users" => array($admin)));
                foreach ($responses as $response) {
                    echo '<div class="alert alert-primary" role="alert">Admin created on Moodle with id ' . $response['id'] . ' and username ' . $response['username'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                }
            } else {
                echo '<div class="alert alert-danger" role="alert">Error: Admin with id ' . $admins['users'][0]['id'] . ' and username ' . $admins['users'][0]['username'] . ' already exist on the Moodle database. <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
            }
            echo '<div class="collapse" id="collapse' . $flag . '"><div class="card card-body">' . print_r($entry) . '</div></div>';
        }
    }

    public function enrol_students()
    {
        echo 'testing 2';
        $query = $this->query->enrol_students($this->term, $this->student, $this->course, $this->resit,$this->group_name);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        $flag = 0;
        foreach ($entries as $entry) {
            $flag++;
            $mdl_user_id = $entry['mdl_user_id'];
            if ($mdl_user_id == "") {
                $students = $this->rest->request('core_user_get_users', array("criteria" => array(array("key" => "idnumber", "value" => $entry['StudentID']))));
                print_r($students);
                $mdl_user_id = $students['users'][0]['id'];
            }
            $student = array();
            $student["roleid"] = '5';
            $student["userid"] = $mdl_user_id;
            $student["courseid"] = $entry['mdl_course_id'];
            //print_r($student);
            $responses = $this->rest->request('enrol_manual_enrol_users', array("enrolments" => array($student)));
            print_r($responses);
            if (array_key_exists("exception", $responses)) {
                echo '<div class="alert alert-danger" role="alert">Error ' . $mdl_user_id . ' (' . $responses['errorcode'] . '): ' . $responses['message'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
            } else {
                if ($responses == "") {
                    echo '<div class="alert alert-primary" role="alert">Student with mdl_id ' . $mdl_user_id . 'StudentID:'.$entry['StudentID'].' enrolled for the course with id  ' . $entry['mdl_course_id'] . ' and Course Name ' . $entry['CourseID'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                }
            }
        }
        echo $flag . " students have been enrolled for the courses";

    }

    public function enrol_lecturers()
    {
        $query = $this->query->enrol_lecturers($this->term, $this->student, $this->course, $this->resit, $this->group_name);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        $flag = 0;
        foreach ($entries as $entry) {
            $flag++;
            $lecturers = $this->rest->request('core_user_get_users', array("criteria" => array(array("key" => "idnumber", "value" => $entry['LecturerID']))));
            $courses = $this->rest->request('core_course_get_courses_by_field', array("field" => "idnumber", "value" => $entry['CourseID']));
            $lecturer = array();
            $lecturer["roleid"] = '3';
            $lecturer["userid"] = $lecturers['users'][0]['id'];
            $lecturer["courseid"] = $courses['courses'][0]['id'];
            $responses = $this->rest->request('enrol_manual_enrol_users', array("enrolments" => array($lecturer)));
            if (array_key_exists("exception", $responses)) {
                echo '<div class="alert alert-danger" role="alert">Error ' . $lecturers['users'][0]['id'] . ' (' . $responses['errorcode'] . '): ' . $responses['message'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
            } else {
                if ($responses == "") {
                    echo '<div class="alert alert-primary" role="alert">Lecturer with id ' . $lecturers['users'][0]['id'] . ' and username ' . $lecturers['users'][0]['username'] . ' enrolled for the course with id  ' . $courses['courses'][0]['id'] . ' and username ' . $courses['courses'][0]['shortname'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                }
            }
            echo '<div class="collapse" id="collapse' . $flag . '"><div class="card card-body">' . print_r($entry) . '</div></div>';
        }
    }

    public function enrol_admins()
    {
        $admins_array = array('MAIN' => 10, 'GENERAL' => 10, 'TA' => 9, 'MARKER' => 11, 'EXAMS' => 13);
        $query = $this->query->enrol_admins($this->term, $this->student, $this->course, $this->resit);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        $flag = 0;
        if (sizeof($entries) > 0) {
            foreach ($entries as $entry) {
                $flag++;
                $user_type = $entry['AdminID'];
                $lecturers = $this->query->getLecturers($entry['AdminEmail']);
                $lecturers_entries = $this->executeQuery($lecturers);
                if (count($lecturers_entries) > 0) {
                    $user_type = str_replace("admin", "-Lecturer", $entry['AdminID']);
                }
                $admins = $this->rest->request('core_user_get_users', array("criteria" => array(array("key" => "idnumber", "value" => $user_type))));
                //print_r($admins);
                $courses = $this->rest->request('core_course_get_courses_by_field', array("field" => "idnumber", "value" => $entry['CourseID']));
                $admin = array();
                $admin["roleid"] = $admins_array[$entry['AdminType']];
                $admin["userid"] = $admins['users'][0]['id'];
                $admin["courseid"] = $courses['courses'][0]['id'];
                $responses = $this->rest->request('enrol_manual_enrol_users', array("enrolments" => array($admin)));

                if (!empty($responses['exception'])) {
                    print_r(array('Error querying enroll', $responses));
                    echo '<div class="alert alert-danger" role="alert">Error ' . $admins['users'][0]['id'] . ' (' . $responses['errorcode'] . '): ' . $responses['message'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                    die();
                }else {
                    echo '<div class="alert alert-primary" role="alert">Admin with id ' . $admins['users'][0]['id'] . ' and username ' . $admins['users'][0]['username'] . ' enrolled for the course with id  ' . $courses['courses'][0]['id'] . ' and coursename ' . $courses['courses'][0]['shortname'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                }

                /*if (array_key_exists("exception", $responses)) {
                    echo '<div class="alert alert-danger" role="alert">Error ' . $admins['users'][0]['id'] . ' (' . $responses['errorcode'] . '): ' . $responses['message'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                } else {
                    if ($responses == "") {
                        echo '<div class="alert alert-primary" role="alert">Admin with id ' . $admins['users'][0]['id'] . ' and username ' . $admins['users'][0]['username'] . ' enrolled for the course with id  ' . $courses['courses'][0]['id'] . ' and username ' . $courses['courses'][0]['shortname'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                    }
                }*/
                echo '<div class="collapse" id="collapse' . $flag . '"><div class="card card-body">' . print_r($entry) . '</div></div>';
            }
        } else {
            echo '<div class="alert alert-primary" role="alert">No Data Found</div>';
        }
    }

    public function create_groups()
    {
        $query = $this->query->create_groups($this->term, $this->student, $this->course);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        $flag = 0;
        foreach ($entries as $entry) {
            $flag++;
            $group = array();
            $group['name'] = $entry['GroupName'];
            $group['description'] = $entry['GroupName'];
            $group['courseid'] = $entry["mdl_course_id"];
            $responses = $this->rest->request('core_group_create_groups', array("groups" => array($group)));
            if (array_key_exists("exception", $responses)) {
                echo '<div class="alert alert-danger" role="alert">Error: Group with ' . $entry['GroupName'] . ' and Course id ' . $entry["mdl_course_id"] . ' already exist (' . $responses['errorcode'] . '): ' . $responses['message'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
            } else {
                foreach ($responses as $response) {
                    echo '<div class="alert alert-primary" role="alert">Group created on Moodle with id ' . $response['id'] . ' and name ' . $response['name'] . ' for Course id ' . $entry["mdl_course_id"] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                }
            }
            echo '<div class="collapse" id="collapse' . $flag . '"><div class="card card-body">' . print_r($entry) . '</div></div>';
        }
    }

    public function assign_students_to_groups()
    {
        $query = $this->query->assign_group_members($this->term, $this->student, $this->course, $this->group_name);
        echo "<pre>" . $query . "</pre>";
        //exit();
        $entries = $this->executeQuery($query);
        $group_id = null;
        $flag = 0;
        foreach ($entries as $entry) {
            $flag++;
            $groups = $this->executeMdlQuery("SELECT * FROM mdl_groups where courseid='" . $entry["mdl_course_id"] . "'");
            foreach ($groups as $group) {
                if ($this->student != "") {
                    $groupArray = array();
                    $groupArray['groupid'] = $group['id'];
                    $groupArray['userid'] = $entry["mdl_user_id"];
                    $responses = $this->rest->request('core_group_delete_group_members', array("members" => array($groupArray)));
                }
                if ($group['name'] == $entry['GroupName']) {
                    $group_id = $group["id"];
                }
            }
            $group = array();
            $group['groupid'] = $group_id;
            $group['userid'] = $entry["mdl_user_id"];
            $responses = $this->rest->request('core_group_add_group_members', array("members" => array($group)));
            echo $entry['GroupName'] . '(' . $group['groupid'] . ") - " . $group['userid']."\n";
            if (array_key_exists("exception", $responses)) {
                echo "<br>Error " . $group['userid'] . "<br>";
                print_r($responses);
                echo "<br><br>";
            } else {
                if ($responses == "") {
                    echo "-Done " . $group['userid']."<br/>";
                }
            }
        }
        /*
        $query = $this->query->assign_group_members($this->term, $this->student, $this->intake, $this->course, $this->group_name);
        //echo "<pre>$query</pre>";
        //exit();
        $entries = $this->executeQuery($query);
        $group_id = null;
        $flag = 0;
        //print_r(count($entries));
        foreach ($entries as $entry) {
            $flag++;
            $groups = $this->rest->request('core_group_get_course_groups', array("courseid" => $entry["mdl_course_id"]));
            foreach ($groups as $group) {
                if ($this->student != "") {
                    $groupArray = array();
                    $groupArray['groupid'] = $group['id'];
                    //$groupArray['userid'] = $students["users"][0]['id'];
                    $groupArray['userid'] = $entry["mdl_user_id"];
                    $responses = $this->rest->request('core_group_delete_group_members', array("members" => array($groupArray)));
                    print_r($responses);
                }
                if ($group['name'] == $entry['GroupName']) {
                    $group_id = $group["id"];
                }
            }
            $group = array();
            $group['groupid'] = $group_id;
            $group['userid'] = $entry["mdl_user_id"];
            $responses = $this->rest->request('core_group_add_group_members', array("members" => array($group)));
            if (array_key_exists("exception", $responses)) {
                echo '<div class="alert alert-danger" role="alert">Error: Student with mdl_user_id ' . $entry["mdl_user_id"] . ' added to the group ' . $entry['GroupName'] . '  on the course with id  ' . $entry["mdl_course_id"] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
            } else {
                if ($responses == "") {
                    echo '<div class="alert alert-primary" role="alert">Student with mdl_user_id ' . $entry["mdl_user_id"] . ' added to the group ' . $group['name'] . '  on the course with id  ' . $entry["mdl_course_id"] . ' and name ' . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                }
            }
            echo '<div class="collapse" id="collapse' . $flag . '"><div class="card card-body">' . print_r($entry) . '</div></div>';
        }
        echo $flag." entries have been updated to the groups";
        */
    }

    public function assign_lecturers_to_groups()
    {
        $query = $this->query->assign_lecturers_to_groups($this->term, $this->student, $this->course);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        $group_id = null;
        $flag = 0;
        foreach ($entries as $entry) {
            $flag++;
            $lecturers = $this->rest->request('core_user_get_users', array("criteria" => array(array("key" => "idnumber", "value" => $entry['LecturerID']))));
            $courses = $this->rest->request('core_course_get_courses_by_field', array("field" => "idnumber", "value" => $entry['CourseID']));
            $groups = $this->rest->request('core_group_get_course_groups', array("courseid" => $courses['courses'][0]["id"]));
            foreach ($groups as $group) {
                if ($group['name'] == $entry['GroupName']) {
                    $group_id = $group["id"];
                }
            }
            $group = array();
            $group['groupid'] = $group_id;
            $group['userid'] = $lecturers["users"][0]['id'];
            $responses = $this->rest->request('core_group_add_group_members', array("members" => array($group)));
            if (array_key_exists("exception", $responses)) {
                echo '<div class="alert alert-danger" role="alert">Error: Lecturer with id ' . $lecturers['users'][0]['id'] . ' and username ' . $lecturers['users'][0]['username'] . ' added to the group ' . $entry['GroupName'] . '  on the course with id  ' . $courses['courses'][0]['id'] . ' and name ' . $courses['courses'][0]['shortname'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
            } else {
                if ($responses == "") {
                    echo '<div class="alert alert-primary" role="alert">Lecturer with id ' . $lecturers['users'][0]['id'] . ' and username ' . $lecturers['users'][0]['username'] . ' added to the group ' . $entry['GroupName'] . '  on the course with id  ' . $courses['courses'][0]['id'] . ' and name ' . $courses['courses'][0]['shortname'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                }
            }
            echo '<div class="collapse" id="collapse' . $flag . '"><div class="card card-body">' . print_r($entry) . '</div></div>';
        }
    }

    public function assign_admins_to_groups()
    {
        $query = $this->query->assign_admins_to_groups($this->term, $this->student, $this->course);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        $group_id = null;
        $flag = 0;
        if (sizeof($entries) > 0) {
            foreach ($entries as $entry) {
                $flag++;
                $admins = $this->rest->request('core_user_get_users', array("criteria" => array(array("key" => "idnumber", "value" => $entry['AdminID']))));
                $courses = $this->rest->request('core_course_get_courses_by_field', array("field" => "idnumber", "value" => $entry['CourseID']));
                $groups = $this->rest->request('core_group_get_course_groups', array("courseid" => $courses['courses'][0]["id"]));
                foreach ($groups as $group) {
                    if ($group['name'] == $entry['GroupName']) {
                        $group_id = $group["id"];
                    }
                }
                $group = array();
                $group['groupid'] = $group_id;
                $group['userid'] = $admins["users"][0]['id'];
                $responses = $this->rest->request('core_group_add_group_members', array("members" => array($group)));
                if (array_key_exists("exception", $responses)) {
                    echo '<div class="alert alert-danger" role="alert">Error: Admin with id ' . $admins['users'][0]['id'] . ' and username ' . $admins['users'][0]['username'] . ' added to the group ' . $entry['GroupName'] . '  on the course with id  ' . $courses['courses'][0]['id'] . ' and name ' . $courses['courses'][0]['shortname'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                } else {
                    if ($responses == "") {
                        echo '<div class="alert alert-primary" role="alert">Admin with id ' . $admins['users'][0]['id'] . ' and username ' . $admins['users'][0]['username'] . ' added to the group ' . $entry['GroupName'] . '  on the course with id  ' . $courses['courses'][0]['id'] . ' and name ' . $courses['courses'][0]['shortname'] . ' <a data-toggle="collapse" href="#collapse' . $flag . '" role="button" aria-expanded="false" aria-controls="collapse' . $flag . '">Know More</a></div></div>';
                    }
                }
                echo '<div class="collapse" id="collapse' . $flag . '"><div class="card card-body">' . print_r($entry) . '</div></div>';
            }
        } else {
            echo '<div class="alert alert-primary" role="alert">No Data Found</div>';
        }

    }

    public function unenrol_students_from_courses()
    {
        $query = $this->query->unenrol_students_from_courses($this->term, $this->student, $this->course);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        if (sizeof($entries) > 0) {
            foreach ($entries as $entry) {
                $students = $this->rest->request('core_user_get_users', array("criteria" => array(array("key" => "idnumber", "value" => $entry['StudentID']))));
                $courses = $this->rest->request('core_course_get_courses_by_field', array("field" => "idnumber", "value" => $entry['CourseID']));
                $un_enroll = array();
                $un_enroll['userid'] = $students["users"][0]['id'];
                $un_enroll['courseid'] = $courses['courses'][0]["id"];
                print_r($this->rest->request('enrol_manual_unenrol_users', array("enrolments" => array($un_enroll))));
            }
            print_r($entries);
        } else {
            echo '<div class="alert alert-primary" role="alert">No Data Found</div>';
        }
    }

    public function unenrol_admins_from_courses()
    {
        $query = $this->query->unenrol_admins_from_courses($this->term, $this->student, $this->course);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        if (sizeof($entries) > 0) {
            foreach ($entries as $entry) {
                $admins = $this->rest->request('core_user_get_users', array("criteria" => array(array("key" => "idnumber", "value" => $entry['AdminID']))));
                $courses = $this->rest->request('core_course_get_courses_by_field', array("field" => "idnumber", "value" => $entry['CourseID']));
                $un_enroll = array();
                $un_enroll['userid'] = $admins["users"][0]['id'];
                $un_enroll['courseid'] = $courses['courses'][0]["id"];
                print_r($this->rest->request('enrol_manual_unenrol_users', array("enrolments" => array($un_enroll))));
            }
            print_r($entries);
        } else {
            echo '<div class="alert alert-primary" role="alert">No Data Found</div>';
        }

    }

    public function transfer_grades_to_database()
    {
        $query = $this->query->getCourseShortName($this->term, $this->student, $this->course, $this->resit);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        if (sizeof($entries) > 0) {
            $courseArray = array();
            $marksArray = array();
            foreach ($entries as $entry) {
                $query_1 = $this->query->get_mdl_students_course_marks($entry["Shortname"]);
                //echo "<pre>" . $query_1 . "</pre>";
                $mdlEntries = $this->executeMdlQuery($query_1);
                //exit();
                if (sizeof($mdlEntries) > 0) {
                    foreach ($mdlEntries as $mdlEntrie) {
                        if (!in_array($mdlEntrie['itemname'], $courseArray) && $mdlEntrie['itemname'] != "") {
                            array_push($courseArray, $mdlEntrie['itemname']);
                        }
                    }

                    foreach ($mdlEntries as $mdlEntrie) {
                        if ($mdlEntrie['itemname'] != "" and $mdlEntrie['finalgrade'] != "") {
                            $scid_query = $this->query->get_enrolled_students_scs_id($mdlEntrie['mdl_course_id'], $mdlEntrie['studentid'], $this->resit);
                            //echo "<pre>" . $scid_query . "</pre>";
                            //exit();
                            $scs_id = $this->executeQuery($scid_query);
                            array_push($marksArray, array("user" => $mdlEntrie['studentid'], "course" => $mdlEntrie['itemname'], "scsid" => $scs_id[0]['scsid'], "marks" => $mdlEntrie['finalgrade']));
                        }
                    }
                }

            }

            echo "<table class='table'>";
            foreach ($courseArray as $course) {
                echo "<tr>";
                echo "<td>" . $course . "</td>";
                echo "<td>";
                echo '<select class="form-control" id="' . $course . '" name="' . $course . '" class="custom-select" onChange="changeAtid(this)">';
                echo '<option selected disabled value="-1">Select the ATID</option>';
                $atids = $this->executeQuery($this->query->GetAtidForDropdown());
                foreach ($atids as $atid) {
                    echo "<option value='" . $atid["id"] . "'>" . $atid["atype"] . " - " . $atid["Weighage"] . "</option>";
                }
                echo '</select>';
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";

            echo "<form name='results' id='results' method='POST' action='resultPost.php' onsubmit='return resultOnSubmit()'>";
            //echo "<form name='results' id='results' method='POST' action='resultPost.php' onsubmit='return resultOnPreparedSubmit()'>";
            echo "<table class='table'>";
            echo "<tr>";
            echo "<th>User</th><th width='200'>Course</th><th>SCSID</th><th>Marks</th><th>ATID</th><th>Comments</th>";
            echo "</tr>";
            $flag = 0;
            foreach ($marksArray as $key => $marks) {
                echo "<tr>";
                echo "<td>" . $marks['user'] . "</td>";
                echo "<td>" . $marks['course'] . "</td>";
                echo "<td><input type='text' id='scsid_" . $key . "' name='scsid_" . $key . "' readonly value='" . $marks['scsid'] . "'></td>";
                echo "<td><input type='text' id='marks_" . $key . "' name='marks_" . $key . "' readonly value='" . $marks['marks'] . "'></td>";
                echo "<td><input type='text' id='atid_" . $key . "' name='atid_" . $key . "' class='" . trim($marks['course']) . "'  readonly></td>";
                echo "<td><input type='text' id='comments_" . $key . "' name='comments_" . $key . "'></td>";
                echo "</tr>";
                $flag = $key;
            }
            echo "</table>";
            echo "<td><input type='hidden' name='keyLength' id='keyLength' value='" . $flag . "'></td>";
            echo "<textarea id='query' name='query'></textarea>";
            echo '<button type="submit" class="btn btn-primary btn-lg">Submit</button>';
            echo "</form>";

        }
    }

    public function transfer_grades_to_database_backup()
    {
        $query = $this->query->create_courses($this->term, $this->student, $this->course, $this->resit);
        echo "<pre>" . $query . "</pre>";
        $entries = $this->executeQuery($query);
        if (sizeof($entries) > 0) {
            $courseArray = array();
            $marksArray = array();
            foreach ($entries as $entry) {
                $grades = $this->rest->request('gradereport_user_get_grade_items', array("courseid" => $entry["mdl_course_id"]));
                foreach ($grades['usergrades'] as $grade) {
                    foreach ($grade['gradeitems'] as $item) {
                        if (!in_array($item['itemname'], $courseArray) && $item['itemname'] != "") {
                            array_push($courseArray, $item['itemname']);
                        }
                    }
                }
                foreach ($grades['usergrades'] as $grade) {
                    $students = $this->rest->request('core_user_get_users', array("criteria" => array(array("key" => "id", "value" => $grade['userid']))));
                    foreach ($grade['gradeitems'] as $item) {
                        if ($item['itemname'] != "" and $item['graderaw'] != "") {
                            $query = $this->query->get_enrol_students_SCSIDs($this->term, $students["users"][0]['idnumber'], $item['itemname']);
                            //echo "<pre>2. ".$query."</pre>";
                            $scsid = $this->executeQuery($query)[0]['ScsID'];
                            array_push($marksArray, array("user" => $students["users"][0]['username'], "course" => $item['itemname'], "scsid" => $scsid, "marks" => $item['graderaw']));
                        }
                    }
                }
            }

            echo "<table class='table'>";
            foreach ($courseArray as $course) {
                echo "<tr>";
                echo "<td>" . $course . "</td>";
                echo "<td>";
                echo '<select class="form-control" id="' . $course . '" name="' . $course . '" class="custom-select" onChange="changeAtid(this)">';
                echo '<option selected disabled value="-1">Select the ATID</option>';
                $atids = $this->executeQuery($this->query->GetAtidForDropdown());
                foreach ($atids as $atid) {
                    echo "<option value='" . $atid["id"] . "'>" . $atid["atype"] . " - " . $atid["Weighage"] . "</option>";
                }
                echo '</select>';
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";

            echo "<form name='results' id='results' method='POST' action='resultPost.php' onsubmit='return resultOnSubmit()'>";
            echo "<table class='table'>";
            echo "<tr>";
            echo "<th>User</th><th>Course</th><th>SCSID</th><th>Marks</th><th>ATID</th><th>Comments</th>";
            echo "</tr>";
            $flag = 0;
            foreach ($marksArray as $key => $marks) {
                echo "<tr>";
                echo "<td>  " . substr($marks['user'], 0, strpos($marks['user'], '@')) . "</td>";
                echo "<td>" . $marks['course'] . "</td>";
                echo "<td><input type='text' id='scsid_" . $key . "' name='scsid_" . $key . "' readonly value='" . $marks['scsid'] . "'></td>";
                echo "<td><input type='text' id='marks_" . $key . "' name='marks_" . $key . "' readonly value='" . $marks['marks'] . "'></td>";
                echo "<td><input type='text' id='atid_" . $key . "' name='atid_" . $key . "' class='" . trim($marks['course']) . "'  readonly></td>";
                echo "<td><input type='text' id='comments_" . $key . "' name='comments_" . $key . "'></td>";
                echo "</tr>";
                $flag = $key;
            }
            echo "</table>";
            echo "<td><input type='hidden' name='keyLength' id='keyLength' value='" . $flag . "'></td>";
            echo "<textarea id='query' name='query'></textarea>";
            echo '<button type="submit" class="btn btn-primary btn-lg">Submit</button>';
            echo "</form>";

            // print_r($courseArray);
            // print_r($marksArray);
        }
    }
}

$intake = "";
$term = "";
$course = "";
$student = "";
$resit = "";
$operation = $_GET['operation'];
$group_name = "";

/*if (isset($_GET['intake'])) {
    $intake = $_GET['intake'];
} else {
    $intake = null;
}
*/

if (isset($_GET['resit'])) {
    $resit = $_GET['resit'];
} else {
    $resit = null;
}

if (isset($_GET['term'])) {
    $term = $_GET['term'];
} else {
    $term = null;
}

if (isset($_GET['course'])) {
    $course = $_GET['course'];
} else {
    $course = null;
}

if (isset($_GET['student'])) {
    $student = $_GET['student'];
} else {
    $student = null;
}

if (isset($_GET['group_name'])) {
    $group_name = $_GET['group_name'];
} else {
    $group_name = null;
}

$ajax = new Ajax($term, $course, $student, $operation, $resit, $group_name);
$ajax->$operation();
