$(document).ready(function(){
	$('#login_form').submit(function(){
		$pwd = $('input[name="LoginForm[password]"]');
		$pwd.val($.md5($pwd.val()));
	});
});
