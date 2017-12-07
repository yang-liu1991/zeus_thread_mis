$(document).ready(function() {
	
	$(function () { $("[data-toggle='tooltip']").tooltip(); });

	
	/**
	 *	查看邮件详情
	 */
	$('#email-list').on('click', 'button[id=email-view]', function() {
		var id = $(this).parent().prevAll(':last').text();
		console.log(id);
		if(id) window.location.href = '/email-manager/email-view?id=' + id;
	})


	/**
	 *	查看邮件详情
	 */
	$('#email-list').on('click', 'button[id=email-record]', function() {
		var id = $(this).parent().prevAll(':last').text();
		console.log(id);
		if(id) window.location.href = '/email-manager/email-record?id=' + id;
	})

	/**
	 *	更新邮件模板
	 */
	$('#email-list').on('click', 'button[id=email-update]', function() {
		var id = $(this).parent().prevAll(':last').text();
		console.log(id);
		if(id) window.location.href = '/email-manager/update-email?id=' + id;
	})


	/**
	 *	返回邮件列表
	 */
	$('#email-list-button').click(function() {
		window.location.href = '/email-manager/email-list';		
	})

	/**
	 *	发送邮件
	 */
	$('#email-send-button').click(function() {
		var id = $('#mail-view-id').val();
		sendEmail(id);
	})

	/**
	 *	邮件发送方法
	 */
	function sendEmail(id)
	{
		var alertMsg = '您确认现在要发送吗？';
		if(confirm(alertMsg) == true)
		{
			$.ajax({ 
				url:'/email-manager/send-email',// 跳转到 action
				data : {tid:id},
				type:'post',
				cache:false,  
				async:false,
				timeout:2000,
				success:function(data) {  
					console.log(data);
					if(data.status == true)
					{
						alert('已加入队列，等待邮件发送!');
						window.location.reload();
					}
				},
				complete:function(XMLHttpRequest, status) {
					if(status=='timeout') {
						alert('已加入队列，等待邮件发送!');
						window.location.reload();
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
	}


	/**
	 *	email list搜索，自动提交form
	 */
	$('#themailtemplatesearch-search').change(function() {
		var subject = $('#themailtemplatesearch-search option:last').text();
		console.log(subject);
		var select_value = $('#themailtemplatesearch-search').val();
		if(select_value == "")
		{
			window.location.href = '/email-manager/email-list'; 
			return;
		}
		$('#themailtemplatesearch-subject').val(subject)
		pjaxReloadEmailList();
	})
	
	$('#themailtemplatesearch-begin_time').change(function() {
		pjaxReloadEmailList();		
	})

	$('#themailtemplatesearch-end_time').change(function() {
		pjaxReloadEmailList();		
	})

	/**
	 *	通过pjax刷新email list
	 */
	function pjaxReloadEmailList()
	{
		$.ajax({ 
			url:'/email-manager/email-list',// 跳转到 action
			data : $('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				$.pjax.reload({container: $('#email-list'), data: $('form#email-list-search-form').serialize()});
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
	 *	email record搜索
	 */
	$('#themailrecordsearch-status').change(function() {
		pjaxReloadEmailRecord();
	})

	$('#themailrecordsearch-begin_time').change(function() {
		pjaxReloadEmailRecord();
	})

	$('#themailrecordsearch-end_time').change(function() {
		pjaxReloadEmailRecord();
	})


	/**
	 *	通过pjax刷新email record
	 */
	function pjaxReloadEmailRecord(id)
	{
		$.ajax({ 
			url:'/email-manager/email-record?id=' + id,// 跳转到 action
			data : $('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				$.pjax.reload({container: $('#email-record'), data: $('form#email-record-search-form').serialize()});
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {  
				console.log(XMLHttpRequest.status);
				console.log(XMLHttpRequest.readyState);
				console.log(textStatus);
				alert("系统繁忙,请稍后重试!");
			}
		})
	}
});
