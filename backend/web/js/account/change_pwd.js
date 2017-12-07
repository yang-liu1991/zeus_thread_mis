$(document).ready(function(){
	function currentAndNewIsTheSame() {
		if ($('.current-pwd').val() != '' && $('.current-pwd').val() == $('.new-pwd').val()) {
			return true;
		} else {
			return false;
		}
	}

	function newPwdIsMatch() {
		if ($('.new-pwd').val() == $('.retype-new-pwd').val()) {
			return true;
		} else {
			return false;	
		}
	}

	function newPwdIsEnoughLong() {
		if ($('.new-pwd').val().length >= 6) {
			return true;
		} else {
			return false;
		}
	}
	
	
	//检测两次新密码输入是否相同
	$('.new-pwd, .retype-new-pwd').bind('change keyup', function(){
		if (!newPwdIsMatch()) {
			$('#not_match_warning').show();
		} else {
			$('#not_match_warning').hide();
		}
	});
	
	//表单提交前检查和MD5处理
	$('#change_pwd_form').submit(function(){
        if (!newPwdIsMatch()) {                       
            $('#not_match_warning').show();                                            
			alert($.fn.d3_t('new password not match, please check it.'));
			return false;
		} else if(!newPwdIsEnoughLong()) {
			$('.unsupport-new-pwd').show();
			alert($.fn.d3_t('the new password is too short.'));
			return false;
        } else if(currentAndNewIsTheSame()) {
			$('.unsupport-new-pwd').show();
			alert($.fn.d3_t('the new password must be different from the current.'));
			return false;
		} else {
            $('#not_match_warning').hide();
			$('.current-pwd, .new-pwd, .retype-new-pwd').each(function(){
				$(this).val($.md5($(this).val()));
			});
			return true;
        } 
	});	

	//检测新密码长度,是否与原密码相同
	$('.new-pwd').bind('keyup change', function(){
		if (!newPwdIsEnoughLong() && $('.new-pwd').val() != '') {
			$('.unsupport-new-pwd').empty().append($.fn.d3_t('the new password is too short.') + '<br>').show();
			return;
		}
		if (currentAndNewIsTheSame()) {
			$('.unsupport-new-pwd').empty().append($.fn.d3_t('the new password must be different from the current.') + '<br>').show();
			return;
		}
		$('.unsupport-new-pwd').hide();
	});

	//检测新密码强度
	$('.new-pwd, .retype-new-pwd').pStrength({
		bind: 'keyup change',
		changeBackground: false,
		backgrounds: [['#cc0000', 'black'], ['#cc3333', 'black'], ['#cc6666', 'black'], ['#ff9999', 'black'],['#e0941c', 'black'], ['#e8a53a', 'black'], ['#eab259', 'black'], ['#efd09e', 'black'],['#ccffcc', 'black'], ['#66cc66', 'black'], ['#339933', 'black'], ['#006600', 'black'], ['#105610', 'black']],
		onPasswordStrengthChanged: function(passwordStrength, strengthPercentage){
			if (passwordStrength > 0) {
				$.fn.pStrength('changeBackground', this, passwordStrength);
			} else {
				$.fn.pStrength('resetStyle', this);
				return;
			}
			if (this.hasClass('retype-new-pwd')) {
				return;
			}
			if (passwordStrength == 0) {
				$('.pwd-strength').hide();	
			} else if(passwordStrength >= 8) {
				$('.pwd-strength').empty().append($.fn.d3_t('password strength:')+' <span style="color:green">'+$.fn.d3_t('high')+'</span>').show();
			} else if(passwordStrength >= 4){
				$('.pwd-strength').empty().append($.fn.d3_t('password strength:') +'<span style="color:rgb(234, 178, 89);">'+$.fn.d3_t('medium')+'</span>').show();
			} else {
				$('.pwd-strength').empty().append($.fn.d3_t('password strength:')+ '<span style="color:red">'+$.fn.d3_t('week')+'</span>').show();
			}
		},
	});

	$('.current-pwd').bind('change keyup', function(){
		$('#incorrect_current_pwd_warning').hide();
	});
});
