<?php

class Query
{
    public function GetIntakesForDropdown()
    {
        return 'SELECT id, date_format(term.term,"%Y%m") as intake from term where date_format(`term`.term,"%Y%m") >= "201806" order by term.term DESC';
    }

    public function GetAtidForDropdown()
    {
        return "select assessmenttype.id ,concat_ws('',assessmenttype.atype,assessmenttype.ano) as atype, concat_ws('',weightage,'/',assessmenttype.`MaxMarks`) as Weighage from `assessmenttype` where `assessmenttype`.`isactive`=b'1' order by assessmenttype.Weightage ASC";
    }

    public function GetGroupNames($term_id)
    {
        return "select DISTINCT tsl.group as group_name from termsubjectlecturer tsl where tsl.termid='$term_id'";
    }

    public function GetCoursesForDropdown()
    {
        return "select course.id ,oc.id as ocid, concat(course.CourseName,'-',institute.Code,'-',university.UnivAcronym) as name from offeredcourse oc
	left join course on course.id=oc.cid
    left join partnership ps on oc.pid=ps.id
    left join university on ps.uid=university.id
    left join institute on institute.id=ps.iid
where oc.`active`='1' and `university`.`UnivAcronym` IN ('UOS','NCD','CCCU')
    order by course.CourseName;";
    }

    public function create_parent_categories($termId, $studentId, $course)
    {
        $query = "SELECT
DISTINCT DATE_FORMAT(term.Term, '%Y%m') AS Level1,
CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-', trim(institute.city_code),'-',TRIM(CONCAT_ws('', university.`UnivAcronym`,'-',course.CourseName))) AS parentCategory
FROM termsubjectlecturer
INNER JOIN term ON term.id = termsubjectlecturer.termid
INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
INNER JOIN course ON course.id = offeredcourse.cid
INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
INNER JOIN studentcourse ON studentcourse.ocid = offeredcourse.id
INNER JOIN partnership ON partnership.id=offeredcourse.pid
INNER JOIN university ON university.id=partnership.`uid`
INNER JOIN institute ON institute.id = partnership.`iid`
inner join student ON student.id=studentcourse.sid
WHERE
(
    termsubjectlecturer.group IS NOT NULL
    AND termsubjectlecturer.group <> ''";

        if (!is_null($termId)) {
            $query .= " AND term.id = '$termId'";
        }
        if (!is_null($course)) {
            $query .= " AND offeredcourse.id='$course'";
        }

        if (!is_null($studentId)) {
            $query .= " AND student.studentid='$studentId'";
        }

        /*if (!is_null($intake)) {
            $query .= " AND date_format(student.intakecourse,'%Y%m')>='$intake'";
        }*/

        $query .= ") GROUP BY 1,2 ORDER BY 1,2";

        return $query;
    }

    public function create_courses($termId, $studentId, $course, $resit)
    {
        $query = "SELECT
           DISTINCT CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-',trim(institute.city_code),'-', TRIM(CONCAT_ws('',university.`UnivAcronym`,'-',course.CourseName)), '-', TRIM(subject.id),if(studentcohortsubject.resitno='0','',concat('-RESIT',studentcohortsubject.resitno))) AS CourseID,
           DATE_FORMAT(term.Term, '%Y%m') AS Level1,
           CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-', trim(institute.city_code),'-' ,TRIM(CONCAT_ws('', university.`UnivAcronym`,'-',course.CourseName))) AS Level2,
           CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-', trim(institute.city_code),'-' ,TRIM(CONCAT_ws('', university.`UnivAcronym`,'-', course.CourseName)), '-', TRIM(subject.code),if(studentcohortsubject.resitno='0','',concat('-RESIT',studentcohortsubject.resitno))) AS Shortname,
           CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-', trim(institute.city_code),'-' ,TRIM(CONCAT_ws('', university.`UnivAcronym`,'-', course.CourseName)),'-',TRIM(subject.code),if(studentcohortsubject.resitno='0','-',concat('-RESIT',studentcohortsubject.resitno,'-')),TRIM(subject.description)) AS Fullname,
           TRIM(subject.description) AS SubjectName,
           termsubjectlecturer.id tslid,
           termsubjectlecturer.mdl_course_id
        FROM termsubjectlecturer
        INNER JOIN term ON term.id = termsubjectlecturer.termid
        INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
        INNER JOIN course ON course.id = offeredcourse.cid
        INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
        INNER JOIN studentcourse ON studentcourse.ocid = offeredcourse.id
        INNER JOIN studentcohortsubject  ON studentcohortsubject.tslid=termsubjectlecturer.id
        INNER JOIN partnership ON partnership.id=offeredcourse.pid
        INNER JOIN university ON university.id=partnership.`uid`
        INNER JOIN institute ON institute.id = partnership.`iid`
        INNER join student ON student.id=studentcourse.sid
        WHERE (
             termsubjectlecturer.group IS NOT NULL
             AND termsubjectlecturer.group <> ''
             AND studentcohortsubject.`resitno`='$resit' ";

        if (!is_null($termId)) {
            $query .= " AND term.id = '$termId' ";
        }
        if (!is_null($course)) {
            $query .= " AND offeredcourse.id='$course' ";
        }

        if (!is_null($studentId)) {
            $query .= " AND student.studentid='$studentId'";
        }

        /*if (!is_null($intake)) {
            $query .= " AND date_format(student.intakecourse,'%Y%m')>='$intake'";
        }
        */
        $query .= ") ORDER BY 1";

        return $query;
    }

    public function transfer_students($termId, $studentId, $course, $resit)
    {
        $query = "SELECT
               DISTINCT student.id AS Studentid,
               student.StudentID AS StudentIDnumber,
               LOWER(CONCAT(TRIM(student.StudentID), '@', student.domain)) AS Username,
               TRIM(person.FirstName) AS Firstname,
               TRIM(person.Surname) AS Lastname,
               CONCAT(TRIM(student.StudentID), '@', student.domain) AS Email,
               student.status AS Status,
               student.studentid as idnumber,
               CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-',trim(institute.city_code),'-', TRIM(CONCAT_ws('',university.UnivAcronym,'-',course.CourseName))) as cohort1,
                  institute.city_code as city,
                  termsubjectlecturer.cohort as department
            FROM termsubjectlecturer
            INNER JOIN term ON term.id = termsubjectlecturer.termid
            INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
            INNER JOIN course ON course.id = offeredcourse.cid
            INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
            INNER JOIN studentcourse ON studentcourse.ocid = offeredcourse.id
            INNER JOIN student ON student.id = studentcourse.sid
            INNER JOIN studentcohort ON studentcohort.scid = studentcourse.id
            INNER JOIN studentcohortsubject ON studentcohortsubject.schid = studentcohort.id AND
                  studentcohortsubject.tslid = termsubjectlecturer.id
            INNER JOIN person ON person.id = student.personid
            INNER JOIN partnership ON partnership.id=offeredcourse.pid
            INNER JOIN university ON university.id=partnership.`uid`
            INNER JOIN institute ON institute.id = partnership.`iid`
            WHERE
            (termsubjectlecturer.group IS NOT NULL AND termsubjectlecturer.group <> '' AND studentcohortsubject.`resitno`='$resit' and student.mdl_user_id IS NULL ) ";

        if (!is_null($termId)) {
            $query .= " AND term.id = '$termId'";
        }

        if (!is_null($course)) {
            $query .= " AND offeredcourse.id='$course'";
        }

        if (!is_null($studentId)) {
            $query .= " AND student.studentid='$studentId'";
        }

       /* if (!is_null($intake)) {
            $query .= " AND date_format(student.intakecourse,'%Y%m')>='$intake'";
        }
        */
        $query .= "  GROUP BY studentcohort.id order by student.mdl_user_id";

        return $query;
    }

    public function moodle_userId_created_SQL()
    {
        return "UPDATE student set mdl_user_id=:mdl_user_id where id=:id";
    }

    public function moodle_courseId_created_SQL()
    {
        return "UPDATE termsubjectlecturer set mdl_course_id=:mdl_course_id where id=:id";
    }

    /*
    public function moodle_user_created($studentId, $moodleID)
    {
        return "UPDATE student set mdl_user_id=" . $moodleID . " where id=" . $studentId;
    }

    public function moodle_course_created($tsl_id, $mdl_course_id)
    {
        return "UPDATE termsubjectlecturer set mdl_course_id=" . $mdl_course_id . " where id=" . $tsl_id;
    }
    */

    public function transfer_lecturers($termId, $course)
    {
        $query = "SELECT
           CONCAT(lecturer.id, '-Lecturer') AS LecturerID,
           TRIM(lecturer.firstname) AS Firstname,
           TRIM(lecturer.surname) AS Lastname,
           TRIM(lecturer.`email`) AS Email,
           TRIM(lecturer.`email`) AS Username
          FROM lecturer
            INNER JOIN tsl_lecturer ON tsl_lecturer.lid = lecturer.id
            INNER JOIN termsubjectlecturer ON termsubjectlecturer.id = tsl_lecturer.tslid
            INNER JOIN term ON term.id=termsubjectlecturer.termid
            WHERE
               (term.id = '$termId') AND
           (lecturer.id <> 0) AND
           (lecturer.firstname IS NOT NULL AND lecturer.firstname <> '') AND
           (lecturer.surname IS NOT NULL AND lecturer.surname <> '') ";

        if (!is_null($course)) {
            $query .= " AND termsubjectlecturer.ocid='$course'";
        }
        $query .= " GROUP BY 1  ORDER BY 2, 3";

        return $query;
    }

    public function transfer_admins($termId, $course)
    {
        $query = "SELECT
					CONCAT(person_peer.id, 'admin') AdminID,
					TRIM(person_peer.FirstName) AS AdminName,
					TRIM(person_peer.Surname) AS AdminSurname,
					TRIM(person_peer.Email) AS AdminEmail,
					TRIM(person_peer.Email) AS Username
				FROM termsubjectlecturer
				INNER JOIN term ON term.id = termsubjectlecturer.termid
				INNER JOIN cohort_admin ON cohort_admin.ocid = termsubjectlecturer.ocid	AND
						cohort_admin.term = termsubjectlecturer.termid AND
						cohort_admin.cohort = termsubjectlecturer.cohort
				INNER JOIN person_peer ON person_peer.id = cohort_admin.admin_person_pid
				WHERE
					(term.id = '$termId') AND
					(cohort_admin.to IS NULL OR cohort_admin.to = '') AND
					(person_peer.FirstName IS NOT NULL AND person_peer.FirstName <> '') AND
					(person_peer.Surname IS NOT NULL AND person_peer.Surname <> '') AND
					(person_peer.Email IS NOT NULL AND person_peer.Email <> '') AND
					(person_peer.status = 'active') ";

        if (!is_null($course)) {
            $query .= " AND termsubjectlecturer.ocid='$course'";
        }

        $query .= " GROUP BY 1 ORDER BY 2, 3";

        return $query;
    }

    public function enrol_students($termId, $studentId, $course, $resit, $group)
    {

        $query = "SELECT
               DISTINCT CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-',trim(institute.city_code),'-',TRIM(CONCAT_ws('',university.`UnivAcronym`,'-', course.CourseName)), '-', TRIM(subject.id),if(studentcohortsubject.resitno='0','',concat('-RESIT',studentcohortsubject.resitno))) AS CourseID,
               student.id AS StudentID,
               student.StudentID AS StudentIDnumber,
               studentcohortsubject.id as ScsID,
               student.mdl_user_id as mdl_user_id,
               term.id AS TermID,
               termsubjectlecturer.mdl_course_id
            FROM termsubjectlecturer
            INNER JOIN term ON term.id = termsubjectlecturer.termid
            INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
            INNER JOIN course ON course.id = offeredcourse.cid
            INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
            INNER JOIN studentcourse ON studentcourse.ocid = offeredcourse.id
            INNER JOIN student ON student.id = studentcourse.sid
            INNER JOIN studentcohort ON studentcohort.scid = studentcourse.id
            INNER JOIN studentcohortsubject ON studentcohortsubject.schid = studentcohort.id AND
                  studentcohortsubject.tslid = termsubjectlecturer.id
            INNER JOIN person ON person.id = student.personid
            INNER JOIN partnership ON partnership.id=offeredcourse.pid
                  INNER JOIN university ON university.id=partnership.`uid`
                  INNER JOIN institute ON institute.id = partnership.`iid`
             WHERE
               (term.id = '$termId') AND
               (term.Term IS NOT NULL AND term.Term <> '') AND
               (course.CourseName IS NOT NULL AND course.CourseName <> '') AND
               (termsubjectlecturer.cohort IS NOT NULL AND termsubjectlecturer.cohort <> '') AND
               (subject.code IS NOT NULL AND subject.code <> '') AND
               (subject.description IS NOT NULL AND subject.description <> '') AND
               (student.StudentID IS NOT NULL AND student.StudentID <> '') AND
               ((person.FirstName IS NOT NULL AND person.FirstName <> '') OR (person.Surname IS NOT NULL AND person.Surname <> '')) AND
               (student.domain IS NOT NULL AND student.domain <> '') AND
               (studentcohortsubject.id IS NOT NULL AND studentcohortsubject.id <> '') AND
               (student.status='Enrolled')  AND studentcohortsubject.`resitno`='$resit' 
               ";


        if (!is_null($course)) {
            $query .= " AND offeredcourse.id='$course'";
        }

        if (!is_null($studentId)) {
            $query .= " AND student.studentid='$studentId'";
        }

        /*if (!is_null($intake)) {
            $query .= " AND date_format(student.intakecourse,'%Y%m')>='$intake'";
        }*/

        if(!is_null($group)){
            $query .= " AND (termsubjectlecturer.group='$group')";
        }

        $query .= " GROUP BY 1, 2 ORDER BY student.id ASC";

        //$query .= " GROUP BY 1, 2 ORDER BY student.id DESC";

        return $query;
    }

    public function enrol_lecturers($termId, $studentId, $course, $resit, $group)
    {
        $query = "SELECT
           DISTINCT CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-',trim(institute.city_code),'-',TRIM(CONCAT_ws('',university.`UnivAcronym`,'-', course.CourseName)), '-', TRIM(subject.id),if(studentcohortsubject.resitno='0','',concat('-RESIT',studentcohortsubject.resitno))) AS CourseID,
           CONCAT(lecturer.id, '-Lecturer') AS LecturerID,
           REPLACE(LOWER(CONCAT(TRIM(lecturer.firstname),'.', TRIM(lecturer.surname) , '@', 'lsclondon.co.uk')), ' ', '') AS Email
        FROM termsubjectlecturer
        INNER JOIN term ON term.id = termsubjectlecturer.termid
        INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
        INNER JOIN course ON course.id = offeredcourse.cid
        INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
        INNER JOIN tsl_lecturer ON tsl_lecturer.tslid = termsubjectlecturer.id
        INNER JOIN lecturer ON lecturer.id = tsl_lecturer.lid
        INNER JOIN partnership ON partnership.id=offeredcourse.pid
              INNER JOIN university ON university.id=partnership.`uid`
        INNER JOIN institute ON institute.id = partnership.`iid`
        INNER JOIN studentcourse ON studentcourse.ocid=offeredcourse.id
        INNER JOIN student ON student.id=studentcourse.sid
        INNER JOIN studentcohortsubject ON studentcohortsubject.tslid=termsubjectlecturer.id
        WHERE
           (term.id = '$termId') AND
           (lecturer.id <> 0) AND
           (lecturer.firstname IS NOT NULL AND lecturer.firstname <> '') AND
           (lecturer.surname IS NOT NULL AND lecturer.surname <> '') AND
           (termsubjectlecturer.group IS NOT NULL AND termsubjectlecturer.group <> '')
           AND studentcohortsubject.`resitno`='$resit'
           ";

        if (!is_null($course)) {
            $query .= " AND offeredcourse.id='$course'";
        }

        if (!is_null($studentId)) {
            $query .= " AND student.studentid='$studentId'";
        }
        /*if (!is_null($intake)) {
            $query .= " AND date_format(student.intakecourse,'%Y%m')>='$intake'";
        }*/

        if(!is_null($group)){
            $query .= " AND termsubjectlecturer.group='$group'";
        }

        //$query .= " GROUP BY 1, 2 ORDER BY 3, 1";
        $query .= " GROUP BY 1, 2";

        return $query;
    }

    public function enrol_admins($termId,$studentId,$course, $resit)
    {
        $query = "SELECT
               DISTINCT CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-',trim(institute.city_code),'-', TRIM(CONCAT_ws('',university.`UnivAcronym`,'-', course.CourseName)), '-', TRIM(subject.id),if(studentcohortsubject.resitno='0','',concat('-RESIT',studentcohortsubject.resitno))) AS CourseID,
               CONCAT(person_peer.id, 'admin') AdminID,
               TRIM(person_peer.Email) AS AdminEmail,
               admin_type as AdminType
            FROM termsubjectlecturer
            INNER JOIN term ON term.id = termsubjectlecturer.termid
            INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
            INNER JOIN course ON course.id = offeredcourse.cid
            INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
            INNER JOIN studentcourse ON studentcourse.ocid = offeredcourse.id
            INNER JOIN cohort_admin ON cohort_admin.ocid = termsubjectlecturer.ocid AND
                  cohort_admin.term = termsubjectlecturer.termid AND
                  cohort_admin.cohort = termsubjectlecturer.cohort
            INNER JOIN person_peer ON person_peer.id = cohort_admin.admin_person_pid
            INNER JOIN partnership ON partnership.id=offeredcourse.pid
                  INNER JOIN university ON university.id=partnership.`uid`
                  INNER JOIN institute ON institute.id = partnership.`iid`
                  INNER JOIN student on student.id=studentcourse.sid
                  INNER JOIN studentcohortsubject ON studentcohortsubject.tslid=termsubjectlecturer.id
            WHERE
               (term.id = '$termId') AND
               (cohort_admin.to IS NULL OR cohort_admin.to = '') AND
               (person_peer.FirstName IS NOT NULL AND person_peer.FirstName <> '') AND
               (person_peer.Surname IS NOT NULL AND person_peer.Surname <> '') AND
               (person_peer.Email IS NOT NULL AND person_peer.Email <> '') AND
               (termsubjectlecturer.group IS NOT NULL AND termsubjectlecturer.group <> '') AND
               (person_peer.status = 'active') AND
               (studentcohortsubject.`resitno`='$resit') ";

        if (!is_null($course)) {
            $query .= " AND offeredcourse.id='$course'";
        }
        if (!is_null($studentId)) {
            $query .= " AND student.studentid='$studentId'";
        }
        /*if (!is_null($intake)) {
            $query .= " AND date_format(student.intakecourse,'%Y%m')>='$intake'";
        }
        */
        $query .= " ORDER BY 3, 1";

        return $query;
    }

    public function create_groups($termId, $studentId, $course)
    {
        $query = "SELECT
               DISTINCT CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-',trim(institute.city_code),'-',TRIM(CONCAT_ws('',university.`UnivAcronym`,'-', course.CourseName)), '-', TRIM(subject.id)) AS CourseID,
               termsubjectlecturer.group AS GroupName,
               termsubjectlecturer.mdl_course_id
            FROM termsubjectlecturer
            INNER JOIN term ON term.id = termsubjectlecturer.termid
            INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
            INNER JOIN course ON course.id = offeredcourse.cid
            INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
            INNER JOIN studentcourse ON studentcourse.ocid = offeredcourse.id
            INNER JOIN student ON student.id = studentcourse.sid
            INNER JOIN studentcohort ON studentcohort.scid = studentcourse.id
            INNER JOIN studentcohortsubject ON studentcohortsubject.schid = studentcohort.id AND
                  studentcohortsubject.tslid = termsubjectlecturer.id
            INNER JOIN person ON person.id = student.personid
            INNER JOIN partnership ON partnership.id=offeredcourse.pid
                  INNER JOIN university ON university.id=partnership.`uid`
                   INNER JOIN institute ON institute.id = partnership.`iid`
            WHERE
               (term.id = '$termId') AND
               (term.Term IS NOT NULL AND term.Term <> '') AND
               (course.CourseName IS NOT NULL AND course.CourseName <> '') AND
               (termsubjectlecturer.cohort IS NOT NULL AND termsubjectlecturer.cohort <> '') AND
               (subject.code IS NOT NULL AND subject.code <> '') AND
               (subject.description IS NOT NULL AND subject.description <> '') AND
               (student.StudentID IS NOT NULL AND student.StudentID <> '') AND
               (person.FirstName IS NOT NULL AND person.FirstName <> '') AND
               (person.Surname IS NOT NULL AND person.Surname <> '') AND
               (termsubjectlecturer.group IS NOT NULL AND termsubjectlecturer.group <> '')";

        if (!is_null($course)) {
            $query .= " AND offeredcourse.id='$course'";
        }

        if (!is_null($studentId)) {
            $query .= " AND student.studentid='$studentId'";
        }

        /*if (!is_null($intake)) {
            $query .= " AND date_format(student.intakecourse,'%Y%m')>='$intake'";
        }*/

        $query .= " ORDER BY 1";

        return $query;
    }

    public function assign_group_members($termId, $studentId, $course, $group_name)
    {
        $query = "SELECT
               DISTINCT CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-',trim(institute.city_code),'-',TRIM(CONCAT_ws('',university.`UnivAcronym`,'-', course.CourseName)), '-', TRIM(subject.id)) AS CourseID,
               student.id AS StudentID,
               student.StudentID AS StudentIDnumber,
               studentcohortsubject.id AS ScsID,
               termsubjectlecturer.group AS GroupName,
               student.mdl_user_id,
               termsubjectlecturer.mdl_course_id
            FROM termsubjectlecturer
            INNER JOIN term ON term.id = termsubjectlecturer.termid
            INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
            INNER JOIN course ON course.id = offeredcourse.cid
            INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
            INNER JOIN studentcourse ON studentcourse.ocid = offeredcourse.id
            INNER JOIN student ON student.id = studentcourse.sid
            INNER JOIN studentcohort ON studentcohort.scid = studentcourse.id
            INNER JOIN studentcohortsubject ON studentcohortsubject.schid = studentcohort.id AND
                  studentcohortsubject.tslid = termsubjectlecturer.id
            INNER JOIN person ON person.id = student.personid
            INNER JOIN partnership ON partnership.id=offeredcourse.pid
                  INNER JOIN university ON university.id=partnership.`uid`
                   INNER JOIN institute ON institute.id = partnership.`iid`

            WHERE
               (term.id = '$termId') AND
               (term.Term IS NOT NULL AND term.Term <> '') AND
               (course.CourseName IS NOT NULL AND course.CourseName <> '') AND
               (termsubjectlecturer.cohort IS NOT NULL AND termsubjectlecturer.cohort <> '') AND
               (subject.code IS NOT NULL AND subject.code <> '') AND
               (subject.description IS NOT NULL AND subject.description <> '') AND
               (student.StudentID IS NOT NULL AND student.StudentID <> '') AND
               ((person.FirstName IS NOT NULL AND person.FirstName <> '') OR (person.Surname IS NOT NULL AND person.Surname <> '')) AND
               (student.domain IS NOT NULL AND student.domain <> '') AND
               (studentcohortsubject.id IS NOT NULL AND studentcohortsubject.id <> '') AND
               (termsubjectlecturer.group IS NOT NULL AND termsubjectlecturer.group <> '')
                and student.status='Enrolled' ";

        if (!is_null($group_name)) {
            $query .= " AND termsubjectlecturer.`group` IN ('$group_name')";
        }

        if (!is_null($course)) {
            $query .= " AND offeredcourse.id='$course'";
        }

        if (!is_null($studentId)) {
            $query .= " AND student.studentid='$studentId'";
        }

        /*if (!is_null($intake)) {
            $query .= " AND date_format(student.intakecourse,'%Y%m')>='$intake'";
        }*/

        //$query .= " ORDER BY student.mdl_user_id ASC";
        //$query .= " ORDER BY student.id DESC";
        $query .= " ORDER BY student.mdl_user_id DESC";

        //print_r("<pre>".$query);

        return $query;
    }

    public function assign_lecturers_to_groups($termId, $studentId, $course)
    {
        $query = "SELECT
					CONCAT(lecturer.id, '-Lecturer') LecturerID,
					TRIM(lecturer.FirstName) AS Firstname,
					CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-',trim(institute.city_code),'-',TRIM(CONCAT_ws('',university.`UnivAcronym`,'-', course.CourseName)), '-', TRIM(subject.id)) AS CourseID,
                    `termsubjectlecturer`.`cohort` as GroupName

                    FROM termsubjectlecturer

                      INNER JOIN tsl_lecturer ON `tsl_lecturer`.tslid = termsubjectlecturer.id
					  INNER JOIN lecturer ON lecturer.id = tsl_lecturer.`lid`
                      INNER JOIN term ON term.id = termsubjectlecturer.termid
                      INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
                      INNER JOIN course ON course.id = offeredcourse.cid
                      INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
                      INNER JOIN studentcourse ON studentcourse.ocid = offeredcourse.id
                      INNER JOIN student ON student.id = studentcourse.sid
                      INNER JOIN studentcohort ON studentcohort.scid = studentcourse.id
                      INNER JOIN studentcohortsubject ON studentcohortsubject.schid = studentcohort.id AND
                            studentcohortsubject.tslid = termsubjectlecturer.id
                      INNER JOIN person ON person.id = student.personid
                      INNER JOIN partnership ON partnership.id=offeredcourse.pid
                      INNER JOIN university ON university.id=partnership.`uid`
                      INNER JOIN institute ON institute.id = partnership.`iid`
				WHERE
					(term.id = '$termId') AND
					(subject.duplicate_code IS NULL) ";
        if (!is_null($course)) {
            $query .= " AND offeredcourse.id='$course'";
        }

        if (!is_null($studentId)) {
            $query .= " AND student.studentid='$studentId'";
        }

        /*if (!is_null($intake)) {
            $query .= " AND date_format(student.intakecourse,'%Y%m')>='$intake'";
        }*/

        $query .= " GROUP BY CourseID,`termsubjectlecturer`.`cohort`,lecturer.id,subject.id
                    ORDER BY 2, 3 ";
        return $query;
    }

    public function assign_admins_to_groups($termId, $studentId, $course)
    {
        $query = "SELECT
					CONCAT(person_peer.id, 'admin') AdminID,
					TRIM(person_peer.FirstName) AS AdminName,
					TRIM(person_peer.Surname) AS AdminSurname,
					TRIM(person_peer.Email) AS Username,
					CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-',trim(institute.city_code),'-',TRIM(CONCAT_ws('',university.`UnivAcronym`,'-', course.CourseName)), '-', TRIM(subject.id)) AS CourseID,
                    cohort_admin.`cohort` as GroupName

                    FROM termsubjectlecturer
                      INNER JOIN cohort_admin ON cohort_admin.ocid = termsubjectlecturer.ocid	AND
						cohort_admin.term = termsubjectlecturer.termid AND
						cohort_admin.cohort = termsubjectlecturer.cohort
					  INNER JOIN person_peer ON person_peer.id = cohort_admin.admin_person_pid
                      INNER JOIN term ON term.id = termsubjectlecturer.termid
                      INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
                      INNER JOIN course ON course.id = offeredcourse.cid
                      INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
                      INNER JOIN studentcourse ON studentcourse.ocid = offeredcourse.id
                      INNER JOIN student ON student.id = studentcourse.sid
                      INNER JOIN studentcohort ON studentcohort.scid = studentcourse.id
                      INNER JOIN studentcohortsubject ON studentcohortsubject.schid = studentcohort.id AND
                            studentcohortsubject.tslid = termsubjectlecturer.id
                      INNER JOIN person ON person.id = student.personid
                      INNER JOIN partnership ON partnership.id=offeredcourse.pid
                            INNER JOIN university ON university.id=partnership.`uid`
                             INNER JOIN institute ON institute.id = partnership.`iid`
				WHERE
					(term.id = '$termId') AND
					(cohort_admin.to IS NULL OR cohort_admin.to = '') AND
					(person_peer.FirstName IS NOT NULL AND person_peer.FirstName <> '') AND
					(person_peer.Surname IS NOT NULL AND person_peer.Surname <> '') AND
					(person_peer.Email IS NOT NULL AND person_peer.Email <> '') AND
					(person_peer.status = 'active') ";

        if (!is_null($course)) {
            $query .= " AND offeredcourse.id='$course'";
        }

        if (!is_null($studentId)) {
            $query .= " AND student.studentid='$studentId'";
        }

        /*if (!is_null($intake)) {
            $query .= " AND date_format(student.intakecourse,'%Y%m')>='$intake'";
        }*/
        $query .= " GROUP BY 5,1
                 ORDER BY 2, 3";

        return $query;
    }

    public function unenrol_students_from_courses($termId, $studentId, $course)
    {
        $query = "SELECT
         DISTINCT CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-',trim(institute.city_code),'-',TRIM(CONCAT_ws('',university.`UnivAcronym`,'-', course.CourseName)), '-', TRIM(subject.id)) AS CourseID,
              student.id AS StudentID,
              student.StudentID AS StudentIDnumber,
              studentcohortsubject.id as ScsID,
              term.id AS TermID
        FROM termsubjectlecturer
        INNER JOIN term ON term.id = termsubjectlecturer.termid
        INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
        INNER JOIN course ON course.id = offeredcourse.cid
        INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
        INNER JOIN studentcourse ON studentcourse.ocid = offeredcourse.id
        INNER JOIN student ON student.id = studentcourse.sid
        INNER JOIN studentcohort ON studentcohort.scid = studentcourse.id
        INNER JOIN studentcohortsubject ON studentcohortsubject.schid = studentcohort.id AND
                 studentcohortsubject.tslid = termsubjectlecturer.id
        INNER JOIN person ON person.id = student.personid
        INNER JOIN partnership ON partnership.id=offeredcourse.pid
           INNER JOIN university ON university.id=partnership.`uid`
           INNER JOIN institute ON institute.id = partnership.`iid`

        WHERE (termsubjectlecturer.termid = '$termId') AND
             (term.Term IS NOT NULL AND term.Term <> '') AND
             (course.CourseName IS NOT NULL AND course.CourseName <> '') AND
             (termsubjectlecturer.cohort IS NOT NULL AND termsubjectlecturer.cohort <> '') AND
             (subject.code IS NOT NULL AND subject.code <> '') AND
             (subject.description IS NOT NULL AND subject.description <> '') AND
             (student.StudentID IS NOT NULL AND student.StudentID <> '') AND
             ((person.FirstName IS NOT NULL AND person.FirstName <> '') OR
             (person.Surname IS NOT NULL AND person.Surname <> '')) AND
             (student.domain IS NOT NULL AND student.domain <> '') AND
             student.status NOT IN ('Enrolled', 'Conditionally Registered', 'Re-Instated', 'Re-Registered', 'Home Study') AND
             (studentcohortsubject.id IS NOT NULL AND studentcohortsubject.id <> '') AND
             (termsubjectlecturer.group IS NOT NULL AND termsubjectlecturer.group <> '')";

        if (!is_null($course)) {
            $query .= " AND offeredcourse.id='$course'";
        }

        if (!is_null($studentId)) {
            $query .= " AND student.studentid='$studentId'";
        }

        /*if (!is_null($intake)) {
            $query .= " AND date_format(student.intakecourse,'%Y%m')>='$intake'";
        }*/

        $query .= " GROUP BY 1, 2 ORDER BY 3, 1, 4";

        return $query;
    }

    public function unenrol_admins_from_courses($termId, $studentId, $course)
    {
        $query = "SELECT
					CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-',trim(institute.city_code),'-',TRIM(CONCAT_ws('',university.`UnivAcronym`,'-', course.CourseName)), '-', TRIM(subject.id)) AS CourseID,
					person_peer.id as AdminID
				FROM termsubjectlecturer
				INNER JOIN term ON term.id = termsubjectlecturer.termid
				INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
				INNER JOIN course ON course.id = offeredcourse.cid
				INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
				INNER JOIN studentcourse ON studentcourse.ocid = offeredcourse.id
				INNER JOIN cohort_admin ON cohort_admin.ocid = termsubjectlecturer.ocid AND
						cohort_admin.term = termsubjectlecturer.termid AND
						cohort_admin.cohort = termsubjectlecturer.cohort
				INNER JOIN person_peer ON person_peer.id = cohort_admin.admin_person_pid
							INNER JOIN partnership ON partnership.id=offeredcourse.pid
		        INNER JOIN university ON university.id=partnership.`uid`
		        INNER JOIN institute ON institute.id=partnership.`iid`

				WHERE
					(term.id = '$termId') AND
					(cohort_admin.to IS NULL OR cohort_admin.to = '') AND
					(person_peer.FirstName IS NOT NULL AND person_peer.FirstName <> '') AND
					(person_peer.Surname IS NOT NULL AND person_peer.Surname <> '') AND
					(person_peer.Email IS NOT NULL AND person_peer.Email <> '') AND
					(termsubjectlecturer.group IS NOT NULL AND termsubjectlecturer.group <> '') AND
					(person_peer.status != 'active') ";
        if (!is_null($course)) {
            $query .= " AND offeredcourse.id='$course'";
        }

        if (!is_null($studentId)) {
            $query .= " AND student.studentid='$studentId'";
        }

        /*if (!is_null($intake)) {
            $query .= " AND date_format(student.intakecourse,'%Y%m')>='$intake'";
        }*/
        $query .= "	GROUP BY 1,2 ORDER BY 2, 1";

        return $query;
    }

    public function get_enrol_students_SCSIDs($termId, $sid, $courseid)
    {
        $query = "SELECT
                   DISTINCT CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-',trim(institute.city_code),'-',TRIM(CONCAT_ws('',university.`UnivAcronym`,'-', course.CourseName)), '-', TRIM(subject.id)) AS CourseID,
                   student.id AS StudentID,
                   studentcohortsubject.id as ScsID
                FROM termsubjectlecturer
                INNER JOIN term ON term.id = termsubjectlecturer.termid
                INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
                INNER JOIN course ON course.id = offeredcourse.cid
                INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
                INNER JOIN studentcourse ON studentcourse.ocid = offeredcourse.id
                INNER JOIN student ON student.id = studentcourse.sid
                INNER JOIN studentcohort ON studentcohort.scid = studentcourse.id
                INNER JOIN studentcohortsubject ON studentcohortsubject.schid = studentcohort.id AND
                      studentcohortsubject.tslid = termsubjectlecturer.id
                INNER JOIN person ON person.id = student.personid
                INNER JOIN partnership ON partnership.id=offeredcourse.pid
                      INNER JOIN university ON university.id=partnership.`uid`
                      INNER JOIN institute ON institute.id = partnership.`iid`
                 WHERE
                   (term.id = '$termId') AND
                   (term.Term IS NOT NULL AND term.Term <> '') AND
                   (course.CourseName IS NOT NULL AND course.CourseName <> '') AND
                   (termsubjectlecturer.cohort IS NOT NULL AND termsubjectlecturer.cohort <> '') AND
                   (subject.code IS NOT NULL AND subject.code <> '') AND
                   (subject.description IS NOT NULL AND subject.description <> '') AND
                   (student.StudentID IS NOT NULL AND student.StudentID <> '') AND
                   ((person.FirstName IS NOT NULL AND person.FirstName <> '') OR (person.Surname IS NOT NULL AND person.Surname <> '')) AND
                   (student.domain IS NOT NULL AND student.domain <> '') AND
                   (studentcohortsubject.id IS NOT NULL AND studentcohortsubject.id <> '') AND
                   (termsubjectlecturer.group IS NOT NULL AND termsubjectlecturer.group <> '') AND
                   (student.status='Enrolled') and
                   CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-',trim(institute.city_code),'-',TRIM(CONCAT_ws('',university.`UnivAcronym`,'-', course.CourseName)), '-', TRIM(subject.code))=SUBSTRING_INDEX('" . trim($courseid) . "','-',5)
                   and student.id=$sid ";
        return $query;
    }

    public function get_enrolled_students_scs_id($mdl_course_id, $student_id, $resitno)
    {
        return "select get_scs_id_mdl_course_id('$mdl_course_id','$student_id','$resitno') as scsid from dual";
    }

    public function getCourseShortName($termId, $studentId, $course, $resit)
    {
        $query = "SELECT
        DISTINCT CONCAT_ws('', DATE_FORMAT(term.Term, '%Y%m'), '-', trim(institute.city_code),'-' ,TRIM(CONCAT_ws('', university.`UnivAcronym`,'-', course.CourseName)), '-', TRIM(subject.code),if(studentcohortsubject.resitno='0','',concat('-RESIT',studentcohortsubject.resitno))) AS Shortname
        FROM termsubjectlecturer
        INNER JOIN term ON term.id = termsubjectlecturer.termid
        INNER JOIN offeredcourse ON offeredcourse.id = termsubjectlecturer.ocid
        INNER JOIN course ON course.id = offeredcourse.cid
        INNER JOIN subject ON subject.id = termsubjectlecturer.sbjid
        INNER JOIN studentcourse ON studentcourse.ocid = offeredcourse.id
        INNER JOIN studentcohortsubject  ON studentcohortsubject.tslid=termsubjectlecturer.id
        INNER JOIN partnership ON partnership.id=offeredcourse.pid
        INNER JOIN university ON university.id=partnership.`uid`
        INNER JOIN institute ON institute.id = partnership.`iid`
        INNER join student ON student.id=studentcourse.sid
        WHERE (
            termsubjectlecturer.group IS NOT NULL
AND termsubjectlecturer.group <> '' AND studentcohortsubject.`resitno`='$resit' ";

        if (!is_null($termId)) {
            $query .= " AND term.id = '$termId' ";
        }
        if (!is_null($course)) {
            $query .= " AND offeredcourse.id='$course' ";
        }

        if (!is_null($studentId)) {
            $query .= " AND student.studentid='$studentId'";
        }

        /*if (!is_null($intake)) {
            $query .= " AND date_format(student.intakecourse,'%Y%m')>='$intake'";
        }
        */
        $query .= ") ORDER BY 1  ";

        return $query;
    }

    public function get_mdl_students_course_marks($courseShortname)
    {
        $query = "select SUBSTRING_INDEX(mu.username,'@',1) as studentid,mc.`shortname`,mgi.`itemname`,mgg.`finalgrade`,mc.id as mdl_course_id
                    from 	`mdl_user` mu,
		            `mdl_enrol` me,
                    `mdl_user_enrolments` mue,
                    `mdl_course` mc,
                    `mdl_grade_items` mgi,
                    `mdl_grade_grades` mgg
                where mu.`id`=mue.`userid` and mue.`enrolid`=me.id and mc.`id`=me.`courseid`  and mgi.`courseid`=mc.id and (mgg.`itemid`=mgi.id and mgg.`userid`=mu.id)
	            and mc.`shortname`='$courseShortname' and mgi.`itemtype`='mod' and mgg.finalgrade is not NULL ";
        return $query;
    }

    public function getAttendanceFromStudentID($sid)
    {
        return "SELECT
    studentcohortsubject.id AS scsid,
    subject.description AS description,
    studentcohort.cohort,
    studentcohort.semester,
    studentcohort.retakeno,
    DATE_FORMAT(term.term, '%b %Y') AS term,
    course.CourseName,
    subject.Code,
    term.id as termid,
    (SELECT
            studentcohortsubject.Result
        FROM
            studentcohortsubject scs
                LEFT JOIN
            termsubjectlecturer tsl ON tsl.id = scs.tslid
                LEFT JOIN
            studentcohort sch ON sch.id = scs.`schid`
        WHERE
            scs.id = studentcohortsubject.id
                AND (tsl.release_results = 'YES'
                OR sch.termid <= 73)) AS Result,
    studentcohortsubject.resitno,
    (CASE
        WHEN
            (studentcohortsubject.resitno != '0'
                AND studentcohortsubject.Result IS NOT NULL)
        THEN
            '100'
        ELSE (SELECT
                ROUND(100 * SUM(attendance.`attendance`) / COUNT(attendance.`attendance`))
            FROM
                attendance
            WHERE
                attendance.scsid = studentcohortsubject.id
                    AND attendance.attendance < 2
                    AND studentcohortsubject.attrequired != 'NO')
    END) AS attendance,
    (CASE
        WHEN
            (studentcohortsubject.resitno != '0'
                AND studentcohortsubject.Result IS NOT NULL)
        THEN
            '1'
        ELSE (SELECT
                SUM(attendance.`attendance`)
            FROM
                attendance
            WHERE
                attendance.scsid = studentcohortsubject.id
                    AND attendance.attendance < 2)
    END) AS present,
    (CASE
        WHEN
            (studentcohortsubject.resitno != '0'
                AND studentcohortsubject.Result IS NOT NULL)
        THEN
            '1'
        ELSE (SELECT
                COUNT(attendance.`attendance`)
            FROM
                attendance
            WHERE
                attendance.scsid = studentcohortsubject.id
                    AND attendance.attendance < 2)
    END) AS total,
    (CASE
        WHEN
            course.CourseName LIKE 'EN%'
        THEN
            (SELECT
                    GROUP_CONCAT(CONCAT_WS(':',
                                    CONCAT_WS('-',
                                            assessmenttype.atype,
                                            assessmenttype.ano),
                                    ROUND(assessment.marksobtained, 1)))
                FROM
                    `assessment`,
                    assessmenttype
                WHERE
                    assessment.`atid` = assessmenttype.id
                        AND assessment.`scsid` = studentcohortsubject.`id`)
        WHEN
            course.CourseName = 'PSE'
        THEN
            (SELECT
                    GROUP_CONCAT(CONCAT_WS(':',
                                    CONCAT_WS('-',
                                            assessmenttype.atype,
                                            assessmenttype.ano),
                                    ROUND(assessment.marksobtained, 1)))
                FROM
                    `assessment`,
                    assessmenttype
                WHERE
                    assessment.`atid` = assessmenttype.id
                        AND assessment.`scsid` = studentcohortsubject.`id`)
        ELSE (SELECT
                GROUP_CONCAT(CONCAT_WS(':',
                                CONCAT_WS('-',
                                        assessmenttype.atype,
                                        assessmenttype.ano,
                                        CONCAT(assessmenttype.weightage, '%')),
                                ROUND(assessment.marksobtained * (100 - assessment.penalty) / 100)))
            FROM
                `assessment`,
                assessmenttype
            WHERE
                assessment.`atid` = assessmenttype.id
                    AND assessment.`scsid` = studentcohortsubject.`id`)
    END) AS assesmentdesc,
    (CASE
        WHEN
            course.CourseName LIKE 'EN%'
        THEN
            (SELECT
                    sci.`english_final_mark`
                FROM
                    studentcohort sci
                WHERE
                    sci.`id` = studentcohort.id)
        WHEN
            course.CourseName = 'PSE'
        THEN
            (SELECT
                    sci.`english_final_mark`
                FROM
                    studentcohort sci
                WHERE
                    sci.`id` = studentcohort.id)
        ELSE round((select sum((assessmenttype.Weightage/100)*assessment.marksobtained * (100-assessment.penalty)/100)
                                	  from `assessment`, assessmenttype where assessment.`atid`=assessmenttype.id and assessment.`scsid`=studentcohortsubject.`id`))
                                   END) as avgmarks,
    SUM(IF((`tsl_timetable`.`type` IN ('GS' , 'MT', 'RS', 'CS', 'EX')),
        IF(`attendance`.`attendance` = 0, 1, 0),
        0)) AS `absents`,
    studentcohort.termid,
    studentcohortsubject.final_mark,
    GROUP_CONCAT(DISTINCT `studentcohort`.`current_group`) AS timetable,
    studentcohort.allow_results_publish
FROM
    studentcourse
        LEFT JOIN
    studentcohort ON studentcohort.scid = studentcourse.id
        LEFT JOIN
    studentcohortsubject ON studentcohortsubject.schid = studentcohort.id
        LEFT JOIN
    subject ON studentcohortsubject.sbjid = subject.id
        LEFT JOIN
    offeredcourse ON offeredcourse.id = studentcourse.ocid
        LEFT JOIN
    course ON offeredcourse.cid = course.id
        LEFT JOIN
    term ON studentcohort.termid = term.id
        LEFT JOIN
    attendance ON attendance.`scsid` = studentcohortsubject.id
        LEFT JOIN
    `tsl_timetable` ON `tsl_timetable`.`id` = attendance.`tslttid`
WHERE
    studentcourse.sid = '" . $sid . "' and studentcohortsubject.id IS NOT NULL
GROUP BY studentcourse.id , studentcohort.id , studentcohortsubject.id
ORDER BY term.term DESC , studentcohort.cohort DESC , studentcohortsubject.resitno
";
    }

    public function getAttendanceFromScsid($scsid)
    {
        return "select  date_format(attendance.ldate,'%W, %d/%m/%Y') as mydate,
      tsl_timetable.type as `type`,
        if(tsl_timetable.starttime is null,'&nbsp;',tsl_timetable.starttime) as starttime,
      if(tsl_timetable.endtime is null,'&nbsp;',tsl_timetable.endtime) as endtime,
        tsl_timetable.location,
        if(attendance.arrivedtime is null,'&nbsp;',date_format(attendance.arrivedtime,'%H:%i')) as attendancetime,
        (CASE
           WHEN (attendance.attendance=0 or attendance.attendance is null) THEN 'Absent'
         WHEN attendance.attendance=1 THEN 'Present'
           WHEN attendance.attendance=2 THEN 'Exempt'
           WHEN attendance.attendance=3 THEN 'NotProcessed'
           ELSE 'NotRegistered*'
        END) AS atten,
        attendance.comments,attendance.session,sch.retakeno
from attendance
   left join tsl_timetable on tsl_timetable.id=attendance.tslttid
    left join `studentcohortsubject` scs on `attendance`.`scsid`=scs.id
    left join studentcohort sch on sch.`id`=scs.`schid`
where scs.id=" . $scsid . " and (scs.`resitno`='0' or scs.attrequired='YES')  and
     date_format(attendance.ldate,'%Y%m%d') <= date_format(now(),'%Y%m%d')  and attendance.attendance!=4
order by  attendance.ldate DESC";
    }

    public function getProgressionPath($termid, $sid)
    {
        return "SELECT
				distinct studentcohort.progress_path  AS ProgressPath
                FROM studentcohort
				INNER JOIN studentcourse ON studentcourse.id = studentcohort.scid
				INNER JOIN student ON student.id = studentcourse.sid
				LEFT JOIN offeredcourse ON offeredcourse.id = studentcourse.ocid
				LEFT JOIN course ON offeredcourse.cid = course.id
                LEFT JOIN term on term.id=studentcohort.termid
				WHERE
					studentcohort.termid = $termid AND
					student.id = $sid ";
    }

    public function getLecturers($email)
    {
        return "select * from lecturer where email='" . $email . "'";
    }

    public function getTimetable($scsid)
    {
        return "SELECT 
    DATE_FORMAT(attendance.ldate, '%W, %d/%m/%Y') AS mydate,
       attendance.ldate AS lecturedate,
    IF(tsl_timetable.starttime IS NULL,
        '&nbsp;',
        tsl_timetable.starttime) AS starttime,
    IF(tsl_timetable.endtime IS NULL,
        '&nbsp;',
        tsl_timetable.endtime) AS endtime,
    tsl_timetable.location,
    (SELECT 
            subject.code
        FROM
            subject
        WHERE
            subject.id = scs.`sbjid`) AS sub,
    `tsl_timetable`.`zoom_link`,
    l.`code` as lecturerCode,
    l.`firstname`
FROM
    attendance
        LEFT JOIN
    tsl_timetable ON tsl_timetable.id = attendance.tslttid
        LEFT JOIN
    `studentcohortsubject` scs ON `attendance`.`scsid` = scs.id
        LEFT JOIN
    studentcohort sch ON sch.`id` = scs.`schid`
        LEFT JOIN
    `tsl_lecturer` tsl_l ON tsl_l.id = `tsl_timetable`.`tsl_lid`
        LEFT JOIN
    lecturer l ON l.id = tsl_l.`lid`
WHERE
    scs.id = '" . $scsid . "'
        AND (scs.`resitno` = '0'
        OR scs.attrequired = 'YES')
        AND DATE_FORMAT(attendance.ldate, '%Y%m%d') >= DATE_FORMAT(NOW(), '%Y%m%d')
        AND attendance.attendance != 4
ORDER BY attendance.ldate DESC";
    }

    public function markAttendance($studentid, $lecturer)
    {
        return "insert into attendance_log(`studentid`,`logdate`,`location`,`lecturer`)VALUES('" . $studentid . "',CURRENT_TIMESTAMP(),'ONLINE','" . $lecturer . "')";
    }

    public function getTimetableLink($sid){
        return "SELECT
        date_format(term.term, '%b%y') as 'Term',concat('http://portal.lsclondon.co.uk/',lower(date_format(term.term, '%b%y')),'timetable.php?group=',studentcohort.current_group) AS timetable_link
        FROM studentcohort
        INNER JOIN studentcourse ON studentcourse.id = studentcohort.scid
        INNER JOIN student ON student.id = studentcourse.sid
        INNER JOIN term ON term.id = studentcohort.TermID
        WHERE
        date_format(term.term, '%Y%m') = get_latest_term(student.id) AND
        student.studentid = '" . $sid . "'";
    }
}
