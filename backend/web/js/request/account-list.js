$(document).ready(function() {
		
	/**
	 *	广告主侧的开户列表操作
	 */
	$(function () { $("[data-toggle='tooltip']").tooltip(); });


	/**
	 *	异常原因查看
	 */
	$('#account-list-form').on('click', 'button[id=refer-reason]', function() {
		var id = $(this).parent().prevAll(':last').text();
		$.ajax({ 
			url:'/advertiser/account-reason',// 跳转到 action
			data:{'id'	: id},
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				console.log(data);
				if(data.message == 'success') alterAccountReasons(data.reasons);
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {  
				console.log(XMLHttpRequest.status);
				console.log(XMLHttpRequest.readyState);
				console.log(textStatus);
				alert("系统繁忙,请稍后重试!");
			}
		})
	});
	
	
	/**
	 *	发送提醒邮件
	 */
	$('#account-list-form').on('click', 'button[id=refer-remind]', function() {
		var id = $(this).parent().prevAll(':last').text();
		$.ajax({ 
			url:'/advertiser/account-remind',// 跳转到 action
			data:{'id'	: id},
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				console.log(data);
				if(data.status == true) 
				{
					alert('Remind Success!');
					window.location.reload();
				} else {
					alterAccountReasons(data.message);
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {  
				console.log(XMLHttpRequest.status);
				console.log(XMLHttpRequest.readyState);
				console.log(textStatus);
				alert("系统繁忙,请稍后重试!");
			}
		})
	});


	/**
	 *	开户失败的提示弹窗
	 */
	function alterAccountReasons(reasons)
	{
		reasonsJson	= eval('(' + reasons + ')');;
		console.log(reasonsJson);
		var config = {};
		config.area = [ '700px', '300px' ];
		config.type = 1;
		config.title = '错误信息';
		config.skin	= 'layui-layer-rim';
		layer.open(config);
		$('.layui-layer-content').JSONView(reasonsJson);
	}


	/**
	 *	当选择筛选后，自动提交
	 */
	$('#thaccountinfosearch-company_id').change(function() {
		$('#account-list-search').submit();
	});

	$('#thaccountinfosearch-begin_time').change(function() {
		$('#account-list-search').submit();
	});

	$('#thaccountinfosearch-end_time').change(function() {
		$('#account-list-search').submit();
	});

	$('#thaccountinfosearch-status').change(function() {
		$('#account-list-search').submit();
	});
})
