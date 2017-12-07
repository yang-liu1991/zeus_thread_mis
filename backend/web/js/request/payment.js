$(function() {
		
	/**
	 *	当点击添加按钮时的操作
	 */
	$('#payment_list_form').on('click', 'button[id="create-payment-button"]', function() {
		var account_id = $(this).parent().prevAll(':last').text();
		if(!account_id) alert('获取焦点失败，请重试！');
		$('#paymentmodel-account_id').val(account_id);
		content = {'title':'添加信息', 'message':'帐户付款信息添加成功!', 'action_url':'/payment-manager/pay-create'};
		console.log(account_id);
		paymentPannel(content);
	})

	/**
	 *	当点击更新按钮时的操作
	 */
	$('#payment_list_form').on('click', 'button[id="update-payment-button"]', function() {
		var account_id = $(this).parent().prevAll(':last').text();
		if(!account_id) alert('获取焦点失败，请重试！');
		$('#paymentmodel-account_id').val(account_id);
		content = {'title':'更新信息', 'message':'帐户付款信息更新成功!', 'action_url':'/payment-manager/pay-create'};
		console.log(account_id);
		getPaymentInfo(account_id);
		paymentPannel(content);
	})

	/**
	 *	当点击查看历史记录
	 */
	$('#payment_list_form').on('click', 'button[id="history-payment-button"]', function() {
		var account_id = $(this).parent().prevAll(':last').text();
		if(!account_id) alert('获取焦点失败，请重试！');
		content = {'title':'历史记录', 'account_id':account_id, 'action_url':'/payment-manager/get-pay-history'};
		getPaymentHistory(content)
	})


	/**
	 *	当清空搜索框时，自动提交form
	 */
	$('#thaccountinfosearch-search').change(function() {
		var company_name = $('#thaccountinfosearch-search option:last').text();
		console.log(company_name);
		var select_value = $('#thaccountinfosearch-search').val();
		if(select_value == "")
		{
			if(window.location.port == "")
			{
				requestUrl = 'http://' + window.location.hostname + '/payment-manager/pay-list'
			} else {
				requestUrl = 'http://' + window.location.hostname + ':' + window.location.port + '/payment-manager/pay-list'
			}
			window.location.href = requestUrl; 
			return;
		}

		$('#thaccountinfosearch-name_zh').val(company_name)
		
		$.ajax({ 
			url:'/payment-manager/pay-list',// 跳转到 action
			data : $('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				$.pjax.reload({container: $('#paymentlist'), data: $('form#parment-search-form').serialize()});
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {  
				console.log(XMLHttpRequest.status);
				console.log(XMLHttpRequest.readyState);
				console.log(textStatus);
				alert("系统繁忙,请稍后重试!");
			}
		})
	})


	/**
	 *	导出按钮
	 */
	$('#parment-export-button').click(function() {
		$('form#parment-search-form').attr('action', '/payment-manager/pay-export');
		$('form#parment-search-form').submit();
	})
	
	/* 显示编辑面板 */
	function historyPannel(title, content)
	{
		var config = {};
		config.area = [ '800px', '300px' ];
		config.type = 1;
		config.title = title;
		config.content = content;
		config.cancel = function(index) {layer.closeAll();}
		layer.open(config);
	}


	/* 显示编辑面板 */
	function paymentPannel(content)
	{
		var config = {};
		config.area = [ '500px', '320px' ];
		config.type = 1;
		config.title = content.title;
		config.content = $('#payment_message').show();
		config.btn = [ '确定', '取消' ];
		config.yes = function(index, layero) {paymentCommit(content)};
		config.cancel = function(index) {layer.closeAll();}
		layer.open(config);
	}


	/**
	 *	Ajax获取付款信息
	 */
	function getPaymentInfo(account_id)
	{
		$.ajax({ 
			url:'/payment-manager/get-pay-info',// 跳转到 action
			data : {'account_id' : account_id},
			type:'post',
			cache:false,  
			async:false,
			//dataType:'json',
			success:function(data) {  
				console.log(data);
				if(data.status == true)
				{
					var payment_info = data.pay_info;
					$('#paymentmodel-pay_name_real').val(payment_info.pay_name_real);
					$('#paymentmodel-pay_type').val(payment_info.pay_type);
					$('#paymentmodel-pay_comment').val(payment_info.pay_comment);
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
	 *	Ajax提交付款信息，进行保存
	 */
	function paymentCommit(content)
	{
		var pay_name_real	= $('#paymentmodel-pay_name_real').val();
		var pay_type		= $('#paymentmodel-pay_type').val();
		
		if(!pay_name_real) 
		{
			alert('请输入实际付款实体！');
			return;
		}

		if(pay_type == "0") 
		{
			alert('请选择结算方式！');
			return;
		}

		$.ajax({ 
			url:content.action_url,// 跳转到 action
			data : $('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				if(data.status == true)
				{
					alert(content.message);
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
	 *	获取到的payment history数据整理成表格，供layer显示
	 */
	function getPaymentHistoryTableHtml(history_info)
	{
		htmlContent = '<table class="table table-condensed table-striped"><thead><tr><th>操作人</th><th>执行动作</th><th>实际付款主体</th><th>结算方式</th><th>操作时间</th></tr></thead><tbody>';
		if(history_info)
		{
			for(var i=0; i<history_info.length; i++)
			{
				htmlContent += '<tr><td>' + history_info[i].username + 
					'</td><td>'+ history_info[i].action_type + 
					'</td><td>'+ history_info[i].pay_name_real +
					'</td><td>'+ history_info[i].pay_type +
					'</td><td>'+ history_info[i].created_at + '</td></tr>'
			}
		}
		htmlContent += '</tbody></table>';
		return htmlContent;
	}

	/**
	 *	Ajax获取历史修改记录
	 */
	function getPaymentHistory(content)
	{
		$.ajax({ 
			url:content.action_url,// 跳转到 action
			data : {'account_id' : content.account_id},
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				if(data.status == true)
				{
					console.log(data.history_info);
					htmlContent = getPaymentHistoryTableHtml(data.history_info);
					historyPannel(content.title, htmlContent);	
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
	 *	删除url中参数的方法
	 */
	function delQuery(url, ref) //删除参数值
	{
		var str = "";
		if (url.indexOf('?') != -1)
			str = url.substr(url.indexOf('?') + 1);
		else
			return url;
		var arr = "";
		var returnurl = "";
		var setparam = "";
		if (str.indexOf('&') != -1) {
			arr = str.split('&');
			for (i in arr) {
				if (arr[i].split('=')[0] != ref) {
					returnurl = returnurl + arr[i].split('=')[0] + "=" + arr[i].split('=')[1] + "&";
				}
			}
			return url.substr(0, url.indexOf('?')) + "?" + returnurl.substr(0, returnurl.length - 1);
		}
		else {
			arr = str.split('=');
			if (arr[0] == ref)
				return url.substr(0, url.indexOf('?'));
			else
				return url;
		}
	}
})
