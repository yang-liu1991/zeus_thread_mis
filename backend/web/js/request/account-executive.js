$(document).ready(function() {
	/**
	 *	FB帐户申请提交
	 */
	$('#refer_list_form').on('click', 'button[id=refer-submit]', function() {
		var id = $(this).parent().prevAll(':last').text();
		var status = $(this).parent().prevAll(':first').text();
		$(this).attr("disabled", true);
		if(status == 'WAITING' || status == 'RE_CHANGE')
		{
			setAgencyBusiness(id);
		}
	});

	/**
	 *	FB帐户取消
	 */
	$('#refer_list_form').on('click', 'button[id=refer-delete]', function() {
		var id = $(this).parent().prevAll(':last').text();
		var status = $(this).parent().prevAll(':first').text();
		console.log(status);
		if(status != 'PENDING' && status != 'RE_CHANGE') { alert("只有PENDING或者REQUESTED_CHANGE状态下才可以取消！");return;}
		if(confirm("确定要取消开户吗?"))
		{
			referDelete(id);
		}
	});

	/**
	 *	异常原因查看
	 */
	$('#refer_list_form').on('click', 'button[id=refer-reason]', function() {
		var id = $(this).parent().prevAll(':last').text();
		referReason(id);
	});

	/**
	 *	查看page link
	 */
	$('#promotable_page_ids').on('click', function(event) {
		var id = $('#entity_id').val();
		openPageLink(id);
	});

	/**
	 *	点击时，放大图片
	 */
	$('#business_registration').zoom();

	/**
	 *	选择agency businesses id
	 */
	function setAgencyBusiness(id)
	{
		var config = {};
		config.area = [ '500px', '300px' ];
		config.type = 1;
		config.title = '开户信息';
		config.content = $('#planning_agency_business_id').show();
		config.btn = [ '确定', '取消' ];
		config.yes = function(index, layero) {referCommit(id);};
		config.cancel = function(index) {layer.closeAll();}
		layer.open(config);
	}

	/**
	 *	开户失败的提示弹窗
	 */
	function alterAccountReasons(reasons)
	{
		reasonsStr	= reasons.replace(']', '').replace('[', '');
		reasonsJson	= eval('(' + reasonsStr + ')');;
		console.log(reasonsJson);
		var config = {};
		config.area = [ '500px', '300px' ];
		config.type = 1;
		config.title = '错误信息';
		config.skin	= 'layui-layer-rim';
		layer.open(config);
		$('.layui-layer-content').JSONView(reasonsJson);
	}

	/**
	 *	Ajax提交开户信息
	 */
	function referCommit(id)
	{
		var business_id = $('#planning_agency_business_id option:selected').val();
		var additional_comment = $('#requestmodel-additional_comment').val();
		console.log(business_id);
		if(!business_id) 
		{
			alert('请选择BuessinessId!');
			return;
		}
		$.ajax({ 
			url:'/account-executive/account-commit',// 跳转到 action
			data:{  
				'id'	: id,
				'business_id' : business_id,
				'additional_comment' : additional_comment
			},
			type:'post',
			cache:false,  
			async:false,
			//dataType:'json',
			success:function(data) {  
				console.log(data);
				if(data.message == 'success' && data.status == true)
				{
					alert('提交成功！');
					window.location.reload()
				} else {
					alert(data.response);
					window.location.reload()
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {  
				console.log(XMLHttpRequest.status);
				console.log(XMLHttpRequest.readyState);
				console.log(textStatus);
				alert("系统繁忙,请稍后重试!");
			}
		})
	}

	/**
	 *	Ajax取消开户申请
	 */
	function referDelete(id)
	{
		$.ajax({ 
			url:'/account-executive/account-delete',// 跳转到 action
			data:{  
				'id'	: id,
			},
			type:'post',
			cache:false,  
			async:false,
			//dataType:'json',
			success:function(data) {  
				console.log(data);
				if(data.message == 'success' && data.status == true)
				{
					alert('取消成功！');
					window.location.reload()
				} else {
					alert(data.response);
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {  
				console.log(XMLHttpRequest.status);
				console.log(XMLHttpRequest.readyState);
				console.log(textStatus);
				alert("系统繁忙,请稍后重试!");
			}
		})
	}

	/**
	 *	查看开户失败原因
	 */
	function referReason(id)
	{
		$.ajax({ 
			url:'/account-executive/account-reason',// 跳转到 action
			data:{  
				'id'	: id,
			},
			type:'post',
			cache:false,  
			async:false,
			//dataType:'json',
			success:function(data) {  
				console.log(data);
				if(data.message == 'success')
				{
					alterAccountReasons(data.reasons);
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {  
				console.log(XMLHttpRequest.status);
				console.log(XMLHttpRequest.readyState);
				console.log(textStatus);
				alert("系统繁忙,请稍后重试!");
			}
		})
	}
	
	/**
	*	Ajax提交备注信息
	 */
	function openPageLink(id)
	{
		$.ajax({ 
			url:'/account-executive/get-page-link',// 跳转到 action
			data:{  
				'id'	: id,
			},
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				if(data.message == 'success')
				{
					console.log(data);
					window.open(data.link, 'newwindow');
				} else {
					alert('打开粉丝页出错啦，可能page id 无效噢！');
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {  
				console.log(XMLHttpRequest.status);
				console.log(XMLHttpRequest.readyState);
				console.log(textStatus);
				alert("系统繁忙,请稍后重试!");
			}
		})
	}
})

