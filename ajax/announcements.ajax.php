<?php

require_once '../../config.php';
require_once("../classes/database.class.php");
$student = $USER->username;
if (strpos($student, "student") !== false) {
    $database = new Database();
    $student_id = explode("@", $student);
    $results = $database->executeQuery("select count(*) as total from student s where  s.`StudentID`='$student_id[0]' and date_format(s.`IntakeCourse`,'%Y%m') in ('201807','201810','201901')");
    if ($results[0]['total'] > 0) {
        echo "<p>The National Student Survey (NSS) is your chance to look back on your higher education experience and speak out on everything from the teaching on your academic course, online access to resources and the in-class support of the Teaching Assistants. Your contribution is valued and each entry be entered in a £100 voucher prize draw. Your views can and will make a difference. With your feedback, your University could make changes to improve their courses for future students. Vist you MySuffolk page and follow the links. We want your views. It’s your NSS.</p>";
        echo "<a target='_blank' href='http://new-portal.lsclondon.co.uk/administrator/assets/downloads/nss-a4-poster.pdf'>Read More</a>";
    }
    if ($student[0] == "s") {
        uos();
    } elseif ($student[0] == "n") {
        ncd();
    } elseif ($student[0] == "c") {
        cccu();
    } elseif ($student[0] == "l") {
        lsc();
    }
} else {
    uos();
    echo "<br><hr><br>";
    ncd();
    echo "<br><hr><br>";
    cccu();
    echo "<br><hr><br>";
    lsc();
}

function uos()
{
    ?>
    <br>Build on your money and finance skills</h4>
    <p>
        Invest in yourself by growing your knowledge and understanding - one of
        the most powerful ways to impact your future.
        From navigating student finance to planning your future pension, the UoS has teamed up with BlackBullion to
        focus on building your financial confidence, knowledge and skills to empower better decision making.
        <br><br>
        To access BlackBullion, please follow these instructions:</p>
    <ol>
        <li>Go to www.blackbullion.com and click on 'Register'</li>
        <li>Select 'I am a University student'</li>
        <li>Enter in your name and email address (this can be a personal non-University email)</li>
        <li>Create a password</li>
        <li>Gain access to the website</li>
    </ol>
    <p>
        Helping people to achieve their dreams by becoming money smart.
    </p>
    <?php
}

function ncd()
{
    ?>
    <h4>Be inspired and learn something new today with OpenLearn - free online learning from the Open University</h4>
    <p>Study on free courses - from one to one-hundred hours of learning and discover hundreds of free courses to
        inspire and inform you. From skills for work, to resources for health and wellbeing, OpenLearn has your needs
        covered with the range of resources to also support your life skills outside of study.
        <br><br>
        Improve your study skills and to discover student life with especially created courses and resources. You can
        also boost your employability skills and find useful resources to help you with your life outside study.
        <br><br>
        Get the most out of OpenLearn<br><br>
        Anyone can learn for free on OpenLearn but by creating an account it lets you set-up a personal learning profile
        which tracks your OpenLearn course programme and gives you access to Statements of Participation and digital
        badges you can gain along the way.
        <br><br>
        Everything on the multi-award winning OpenLearn is free for everyone.</p>
    <a href="https://www.open.edu/openlearn/" target="_blank">Click here for more details</a>
    <?php
}

function cccu()
{

}

function lsc()
{

}
