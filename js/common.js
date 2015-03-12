Namaste = {};

function namasteConfirmDelete(frm, msg) {
	if(!confirm(msg)) return false;
	frm.del.value=1;
	frm.submit(); 
}

function namasteEnrollCourse(boxTitle, courseID, studentID, url) {
	tb_show(boxTitle, 
		url + '&course_id=' + courseID + 
		'&student_id=' + studentID);
}

function namasteResetPoints(uid) {
	if(confirm("Are you sure?")) {
		var s = "?";
		var loc = new String(window.location);
		if(loc.indexOf('?') >= 0) s = "&";		
		window.location.href = loc + s + "namaste_cleanup_points=" + uid;  
	}
}