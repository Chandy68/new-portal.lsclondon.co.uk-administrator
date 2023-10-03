function FormSubmit() {
  document.getElementById("container").innerHTML = "<center><img src='../administrator/assets/img/loader.gif'></center>";
  //const intake = document.getElementById("intake").value;
  const term = document.getElementById("term").value;
  const course = document.getElementById("course").value;
  const operation = document.getElementById("operation").value;
  const student = document.getElementById("student").value;
  const resit = document.getElementById("resit").value;
  const group_name = document.getElementById("group_name").value;

  if (term == "-1") {
    alert("Please select a term");
  } else if (operation == "-1") {
    alert("Please select an operation");
  } else {
    let url = "../administrator/classes/ajax.class.php?operation=" + operation + "&term=" + term + "&resit=" + resit;
    /*if (intake != "-1") {
      url += "&intake=" + intake;
    }*/
    if (course != "-1") {
      url += "&course=" + course;
    }
    if (student != "") {
      url += "&student=" + student;
    }
    if (group_name != "-1") {
      url += "&group_name=" + group_name;
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById("container").innerHTML = this.responseText;
      }
    };
    xmlhttp.open("GET", url, true);
    xmlhttp.send();
  }
  return false;
}

function changeAtid(dropdown) {
  const atid = document.getElementsByClassName(dropdown.name);
  for (let i = 0; i < atid.length; i++) {
    atid[i].value = dropdown.value;
  }
}

function ChangeTerms() {
  const term = document.getElementById("term").value;
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("group_name").innerHTML = this.responseText;
    }
  };
  xmlhttp.open("GET", "../administrator/ajax/group.ajax.php?term_id=" + term, true);
  xmlhttp.send();
}

function resultOnSubmit() {
  var flag = document.getElementById("keyLength").value;
  var queryExecute = "";
  var query, scsid, atid, marks, comments;
  for (var i = 0; i < flag; i++) {
    scsid = document.getElementById("scsid_" + i).value;
    atid = document.getElementById("atid_" + i).value;
    marks = document.getElementById("marks_" + i).value;
    comments = document.getElementById("comments_" + i).value;
    console.log(atid);
    if (scsid != "" && atid != "") {
      query =
        "REPLACE INTO assessment SET scsid='" +
        scsid +
        "', atid='" +
        atid +
        "', MarksObtained='" +
        marks +
        "', Comments='" +
        comments +
        "', user='######'; ";
      queryExecute = queryExecute + query;
    }
  }
  document.getElementById("query").value = queryExecute;
  return true;
}

function resultOnPreparedSubmit() {
  var flag = document.getElementById("keyLength").value;
  var queryExecute = [];
  var query,scsid, atid, marks, comments;
  for (var i = 0; i < flag; i++) {
    scsid = document.getElementById("scsid_" + i).value;
    atid = document.getElementById("atid_" + i).value;
    marks = document.getElementById("marks_" + i).value;
    comments = document.getElementById("comments_" + i).value;
    console.log(atid);
    if (scsid != "" && atid != "") {
      queryExecute.push([scsid,atid,marks,comments,user])
    }
  }
  document.getElementById("query").value = queryExecute;
  return true;
}
