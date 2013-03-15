Namaste = {};

function namasteConfirmDelete(frm, msg) {
	if(!confirm(msg)) return false;
	frm.del.value=1;
	frm.submit(); 
}