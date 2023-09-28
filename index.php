<?php

require_once '../config.php';
require_once 'classes/database.class.php';
require_once 'classes/query.class.php';
$PAGE->set_title("LSC Administrator");
$PAGE->set_heading("LSC Administrator");
echo $OUTPUT->header();

$database = new Database();
$query = new Query();

?>
<div class="container-fluid">
    <form method="post" onsubmit="return FormSubmit()">
        <div class="row">
            <!--
            <div class="col form-group">
                <label for="intake">Intake Date(MBAEOnly-201910)</label>
                <select class="form-control" id="intake" name="intake" class="custom-select">
                    <option selected disabled value="-1">Select an Intake</option>
                    <?php /*$intakes = $database->executeQuery($query->GetIntakesForDropdown());
                    foreach ($intakes as $intake) {
                        echo "<option value='" . $intake["intake"] . "'>" . $intake["intake"] . "</option>";
                    }*/?>
                </select>
            </div>
            -->
            <div class="col form-group">
                <label for="terms">Terms</label>
                <select class="form-control" id="term" name="term" class="custom-select" onChange="ChangeTerms()">
                    <option selected disabled value="-1">Select the term</option>
                    <?php $intakes = $database->executeQuery($query->GetIntakesForDropdown());
                        foreach ($intakes as $intake) {
                            echo "<option value='" . $intake["id"] . "'>" . $intake["intake"] . "</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="col form-group">
                <label for="course">Course</label>
                <select class="form-control" id="course" name="course" class="custom-select">
                    <option selected disabled value="-1">Select the course</option>
                    <?php $courses = $database->executeQuery($query->GetCoursesForDropdown());
                        foreach ($courses as $course) {
                            echo "<option value='" . $course["ocid"] . "'>" . $course["name"] . "</option>";
                        }?>
                </select>
            </div>
            <div class="col form-group">
                <label for="resit">Resit</label>
                <select class="form-control" id="resit" name="resit" class="custom-select">
                    <option value="0" selected>Regular</option>
                    <option value="1">Resit 1</option>
                    <option value="2">Resit 2</option>
                    <option value="A">Academic Offense</option>
                </select>
            </div>

            <div class="col form-group">
                <label for="operation">Operation</label>
                <select class="form-control" id="operation" name="operation" class="custom-select">
                    <option selected disabled value="-1">Select an Operation</option>
                    <option value='create_parent_categories'>Create Parent Categories</option>
                    <option value='create_courses'>Create Courses</option>
                    <option value='transfer_students'>Transfer Students</option>
                    <option value='transfer_lecturers'>Transfer Lecturers</option>
                    <option value='transfer_admins'>Transfer Admins</option>
                    <option value='enrol_students'>Enrol Students</option>
                    <option value='enrol_lecturers'>Enrol Lecturers</option>
                    <option value='enrol_admins'>Enrol Admins</option>
                    <option value='create_groups'>Create Groups</option>
                    <option value='assign_students_to_groups'>Assign Students to Groups</option>
                    <option value='assign_admins_to_groups'>Assign Admins to Groups</option>
                    <option value='assign_lecturers_to_groups'>Assign Lecturers to Groups</option>
                    <option value='unenrol_students_from_courses'>Unenrol Students from Courses</option>
                    <option value='unenrol_admins_from_courses'>Unenrol Admins from Courses</option>
                    <option value='single_student_transfer'>Single Student Transfer</option>
                    <option value="transfer_grades_to_database">Transfer Grades to Database</option>
                </select>
            </div>
            <div class="col form-group">
                <label for="student">Student ID</label>
                <input type="text" id="student" name="student" class="custom-select" placeholder="Enter Student ID"/>
            </div>
            <div class="col form-group">
                <label for="group_name">Group name</label>
                <select class="form-control" id="group_name" name="group_name" class="custom-select">
                    <option selected disabled value="-1">Select the course</option>
                </select>
            </div>
            <div class="col form-group">
                <label>Action</label><br />
                <button type="submit" class="btn btn-outline-primary">Fetch Data</button>
            </div>
        </div>
    </form>
</div>
<div class="container" id="container">

</div>
<script src="assets/js/default.js"></script>
<?php
echo $OUTPUT->footer();
