$(document).ready(function(){
	/**
	 *	重置密码
	 */
	$('#reset-password').click(function() {
		$pwd = $('input[name="ResetPassword[password]"]');
		$pwd.val($.md5($pwd.val()));
		$('#reset-password-form').submit();
	})

	/**
	 *	更新密码
	 */
	$('#change-password').click(function() {
		$oldPwd = $('input[name="ChangePassword[oldPassword]"]');
		$newPwd = $('input[name="ChangePassword[newPassword]"]');
		$retypePwd = $('input[name="ChangePassword[retypePassword]"]');
		$oldPwd.val($.md5($oldPwd.val()));
		$newPwd.val($.md5($newPwd.val()));
		$retypePwd.val($.md5($retypePwd.val()));
		$('#form-change').submit();
	})
});
