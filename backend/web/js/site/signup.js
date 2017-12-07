$(document).ready(function(){
	$('#signup_form').submit(function(){
		$pwd = $('input[name="AddUserForm[password]"]');
		$repwd = $('input[name="AddUserForm[repassword]"]');
		$pwd.val($.md5($pwd.val()));
		$repwd.val($.md5($repwd.val()));
	});

	$('#submit-create-user').click(function(){
		$pwd = $('input[name="AddUserForm[password]"]');
		$repwd = $('input[name="AddUserForm[repassword]"]');
		$pwd.val($.md5($pwd.val()));
		$repwd.val($.md5($repwd.val()));
		$('#user_form').submit();
	});

	$('#submit-update-user').click(function(){
		$pwd = $('input[name="ModifyForm[password]"]');
		$repwd = $('input[name="ModifyForm[repassword]"]');
		$pwd.val($.md5($pwd.val()));
		$repwd.val($.md5($repwd.val()));
		$('#user_form').submit();
	});
});
