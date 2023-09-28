<?php

require_once '../../config.php';
require_once("../classes/database.class.php");
$student = $USER->username;
echo "<table  class='table table-striped table-sm'>";
if (strpos($student, "student") !== false) {
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
    echo "<tr><th>University of Suffolk</th></tr>";
    uos();
    echo "<tr><th>New College Durham</th></tr>";
    ncd();
    echo "<tr><th>Canterbury Christ Church University</th></tr>";
    cccu();
    echo "<tr><th>London School of Commerce</th></tr>";
    lsc();
}
echo "</table>";
function create_link($label, $link)
{
    echo "<tr><td><a target='_blank' href='$link'>$label</a></td></tr>";
}

function uos()
{
    create_link('University of Suffolk Library Resources', 'https://intranet.uos.ac.uk/saml_login');
    create_link('Instructions to Connect to Library Resources', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/uos/uos-library.pdf');
    create_link('UoS Temporary Academic Regulations', 'https://www.new-portal.lsclondon.co.uk/mod/url/view.php?id=1483');
    create_link('Introduction to National Student Survey', 'https://youtu.be/B_QGABBXVIs');
    create_link('Students Union tell us why NSS is important', 'https://www.youtube.com/watch?v=Qn7FWSm4WTg');
    create_link('UOS BABS Handbook', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/uos/uos-babs-with-foundation-program-handbook.pdf');
    create_link('UOS MBA Handbook', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/uos/uos-mba-handbook.pdf');
    create_link('UOS Attendance Policy', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/uos/uos_ttendance policy_23_04_21.pdf');
    create_link('LSC Student Info Booklet', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/uos/lsc-student-info-booklet.pdf');
}

function ncd()
{
    // NEW LINKS
    create_link('Regulations on Assessments Prior to November 2022','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/academic-regulations-presentation-for-student-prior-to-november-2022.pdf');
    create_link('Regulations on Assessments November 2022 onwards','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/academic-regulations-presentation-nov22-onwards.pdf');
    create_link('Assessment Boards Presentation','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/assessment-boards-presentation.pdf');
    create_link('Extensions, Exceptional Circumstances and Suspending Your Studies','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/extensions-exceptional-circumstances-and-suspending-your-studies-new-college-durham.pdf');
    create_link('HE Withdrawals Policy','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/he-withdrawals-policy-v12.pdf');
    create_link('HE Suspension of Studies Policy V1','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/he-suspension-of-studies-policy-v1.pdf');
    create_link('Mitigation Presentation V2','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/mitigation-presentation-v2.pdf');
    create_link('Online Resources for NCD Students at the LSC','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/online-resources-for-ncd-students-at-the-lsc.mp4');
    create_link('Student Union Presentation','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/student-union-presentation.pdf');
    create_link('Using your OpenAthens account','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/using-your-openathens-account-ncd-at-lsc.mp4');

    // OLD LINKSß
    create_link('LSC & NCD Online Resources Handout', 'https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/LSC_AND_NCD_Online_Resources_and_OpenAthens_accounts.pdf');
    create_link("Free online learning resources to support your learning and development", "https://www.new-portal.lsclondon.co.uk/course/view.php?id=811");
    create_link('Programme Handbook Rules & Regs Level 4', 'https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/Programme Handbook Rules  Regulations L4.pdf');
    create_link('Programme Handbook Rules & Regs Level 5', 'https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/Programme Handbook Rules  Regualtions L5.pdf');
    create_link('Programme Handbook Your Course Level 4', 'https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/Programme Handbook Your Course L4.pdf');
    create_link('Programme Handbook Your Course Level 5', 'https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/Programme Handbook Your Course L5.pdf');
    create_link('LSC & NCD Student Guide 2022-23', 'https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/LSC_NCD student guide 2022_23.pdf');
    create_link('Induction Presentation - NCD Bus-Apr23', 'https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/Induction Presentation - NCD Bus Apr23.pdf');
    create_link('Induction Presentation - NCD HSC-Apr23', 'https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/Induction Presentation - NCD HSC  Apr23.pdf');

    //create_link('Using your OpenAthens account NCD at LSC', 'https://www.youtube.com/watch?v=xjnYgbq32PE');
    //create_link('Online Resources for NCD Students at the LSC', 'https://www.youtube.com/watch?v=kwt9i8N6ok4');
    //create_link('Academic Regulations Presentation','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/academic-regulations-presentation.pptx');
    //create_link('Assessment Boards Presentation','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/assessment-boards-presentation.pptx');
    //create_link('Extensions, Exceptional Circumstances and Suspending Your Studies New College Durham ','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/extensions-exceptional-circumstances-and-suspending-your-studies-new-college-durham.pptx');
    //create_link('Mitigation Presentation V2','https://new-portal.lsclondon.co.uk/administrator/assets/downloads/ncd/mitigation-presentation-v2.pptx');
}

function cccu()
{
    create_link('CCCU Student Programme Handbook', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/cccu/lsc-cccu-student-programme-handbook-v3.pdf');
    create_link('Handout of Online Resources', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/cccu/handout-of-online-resources.pdf');
    create_link('Module Handbook Template', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/cccu/lsc-cccu-module-handbook-template-2020-21.docx');
    create_link('CCCU Assessment grading criteria Level 0', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/cccu/cccu-assessment-grading-criteria-level-0.pdf');
    create_link('CCCU Assessment grading criteria Level 4', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/cccu/cccu-assessment-grading-criteria-level-4.pdf');
    create_link('CCCU Assessment grading criteria Level 5', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/cccu/cccu-assessment-grading-criteria-level-5.pdf');
    create_link('CCCU Assessment grading criteria Level 6', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/cccu/cccu-assessment-grading-criteria-level-6.pdf');
    create_link('Plagiarism and Academic Misconduct Procedures', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/cccu/plagiarism-and-academic-misconduct-procedures.pdf');
    create_link('Regulations for Taught Awards September 2020', 'http://new-portal.lsclondon.co.uk/administrator/assets/downloads/cccu/regulations-for-taught-awards-sep20.pdf');
    create_link('Information for students studying at a partner institution', 'https://www.canterbury.ac.uk/our-students/ug-current/support-services/personal-support/studying-at-a-partner-institution');
}

function lsc()
{

}
