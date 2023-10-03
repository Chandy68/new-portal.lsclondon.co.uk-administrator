//  COURSE TEMPLATE

const courses = [
  "uos_babsfy_iit",
  "uos_babsfy_ipsda",
  "cccu_babsf_ipsda",
  "uos_babsfy_cb",
  "uos_babsfy_cb_apr_21",
  "uos_babsfy_nda",
  "uos_babsfy_bc",
  "uos_babsfy_mp",
  "uos_babsfy_benv",
  "uos_babsfy_sshe",
  "ncd_fdabm_ppd",
  "ncd_fdabm_ppd1",
  "ncd_fdabm_ppd2",
  "ncd_fdabm_ine",
  "ncd_fdabm_benv",
  "ncd_fdabm_mp",
  "ncd_fdabm_sam",
  "ncd_fdhsc_ppd",
  "ncd_fdhsc_wrl",
  "ncd_fdhsc_wrr",
  "ncd_fdabm_wrl",
  "ncd_fdabm_lp",
  "ncd_fdhsc_eba",
  "ncd_fdhsc_ehc",
  "uos_babsfy_mm",
  "uos_babsfy_cbe",
  "ncd_fdabm_hrm",
  "ncd_fdhsc_cwg",
  "ncd_fdhsc_ci",
  "ncd_fdhsc_cpd",
  "uos_babsfy_bdm",
  "cccu_babsf_cb",
  "cccu_babsf_sshe",
  "uos_babsfy_ppd",
  "uos_babsfy_hrm",
  "cccu_babsf_iit",
  "cccu_babsf_bc",
  "ncd_fdabm_ent",
  "ncd_fdabm_bf",
  "uos_babsfy_ble",
  "uos_babsfy_otp",
  "ncd_fdabm_lscm",
  "uos_babsfy_db",
  "ncd_fdabm_dt",
  "ncd_fdhsc_pss",
  "ncd_fdhsc_ph",
  "ncd_fdhsc_si",
  "ncd_fdhsc_at",
  "uos_babsfy_ebd",
  "cccu_babsy3_cmi",
  "cccu_babsy3_gsm",
  "cccu_babsy3_rm",
  "cccu_babsf_bc",
  "cccu_babsf_ppd1",
  "cccu_babsf_bdm",
  "uos_babsfy_rm",
  "uos_babsfy_afb",
  "uos_babsfy_ib",
  "cccu_babsy3_csa",
  "cccu_babsy3_bso",
  "cccu_bscy3_nwl",
  "cccu_bscy3_hw",
  "ncd_fdabm_lam",
  "ncd_fdabm_pom",
  "ncd_fdabm_pib",
  "ncd_fdabm_dcr",
  "cccu_babsf_cbe",
  "cccu_babsf_ble",
  "cccu_bscy3_gh",
  "cccu_bscy3_pahw",
  "cccu_babsf_nda",
  "cccu_bscy3_diss",
  "cccu_babsf_mm",
  "uos_babsfy_gsm",
  "uos_babsfy_cmi",
  "uos_babsfy_csa",
  "cccu_babsf_db_fk",
  "cccu_babsf_db_cr",
  "uos_babsfy_db_cr",
  "uos_babsfy_diss",
  "uos_babsfy_bso",
  "cccu_babsf_ebd",
  "cccu_babsf_ib",
  "cccu_babsf_otp",
  "cccu_babsf_afb",
  "cccu_babsfy3_diss"

];

for (let i = 0; i < courses.length; i++) {
  if (document.getElementsByClassName(courses[i])[0]) {
    var xmlhttp3 = new XMLHttpRequest();
    xmlhttp3.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementsByClassName(courses[i])[0].innerHTML =
          this.responseText;
      }
    };
    xmlhttp3.open(
      "GET",
      "../administrator/ajax/template.class.php?template=" + courses[i],
      true
    );
    xmlhttp3.send();
  }
}

// STUDENT DASHBOARD ATTENDANCE

let url = "../administrator/ajax/attendance.ajax.php";

var xmlhttp = new XMLHttpRequest();
xmlhttp.onreadystatechange = function () {
  if (this.readyState == 4 && this.status == 200) {
    document.getElementsByClassName(
      "my_attendance_administrator_plugin"
    )[0].innerHTML = this.responseText;
  }
};
xmlhttp.open("GET", url, true);
xmlhttp.send();

//  STUDENT DASHBOARD TIMETABLE

let url5 = "../administrator/ajax/timetablep.ajax.php";

var xmlhttp5 = new XMLHttpRequest();
xmlhttp5.onreadystatechange = function () {
  if (this.readyState == 4 && this.status == 200) {
    document.getElementsByClassName(
      "my_timetable_administrator_plugin"
    )[0].innerHTML = this.responseText;
  }
};
xmlhttp5.open("GET", url5, true);
xmlhttp5.send();

// STUDENT DASHBOARD RESULT

let url2 = "../administrator/ajax/result.ajax.php";

var xmlhttp2 = new XMLHttpRequest();
xmlhttp2.onreadystatechange = function () {
  if (this.readyState == 4 && this.status == 200) {
    document.getElementsByClassName(
      "my_result_administrator_plugin"
    )[0].innerHTML = this.responseText;
  }
};
xmlhttp2.open("GET", url2, true);
xmlhttp2.send();

// STUDENT DASHBOARD RESULT

let url3 = "../administrator/ajax/links.ajax.php";

var xmlhttp3 = new XMLHttpRequest();
xmlhttp3.onreadystatechange = function () {
  if (this.readyState == 4 && this.status == 200) {
    document.getElementsByClassName("custom_quick_links")[0].innerHTML =
      this.responseText;
  }
};
xmlhttp3.open("GET", url3, true);
xmlhttp3.send();

// STUDENT ANNOUNCEMENT RESULT

let url4 = "../administrator/ajax/announcements.ajax.php";

var xmlhttp4 = new XMLHttpRequest();
xmlhttp4.onreadystatechange = function () {
  if (this.readyState == 4 && this.status == 200) {
    document.getElementsByClassName("custom_announcements")[0].innerHTML =
      this.responseText;
  }
};
xmlhttp4.open("GET", url4, true);
xmlhttp4.send();

function StoreAttendance(
  studentid,
  lecturer,
  zoomlink,
  lectureDate,
  lectureTime
) {
  console.log("Attendance session started...");
  const today = new Date();
  const strDate = lectureDate.split("-");
  const strTime = lectureTime.split(":");
  const lecture_date = new Date(
    strDate[0],
    parseInt(strDate[1] - 1).toString(),
    strDate[2],
    strTime[0],
    strTime[1]
  );
  if (today.getDate() === lecture_date.getDate()) {
    let time = (lecture_date.getTime() - today.getTime()) / 1000;
    time /= 60;
    const difference = Math.round(time);
    if (difference < 5 && difference > -150) {
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
          console.log("Send to zoom after attendance...");
          window.location.href = zoomlink;
        }
      };
      xmlhttp.open(
        "GET",
        `../administrator/ajax/record_attendance.ajax.php?studentid=${studentid}&lecturer=${lecturer}`,
        true
      );
      xmlhttp.send();
    } else {
      console.log("Send to zoom...");
      window.location.href = zoomlink;
    }
  }
}
