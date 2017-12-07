$(function() {
		var status = false;
		var btn = $('.btn-success');
		$('.th-amount-list').on('click', 'button[id=amount-detail-button]', function(e) {
			console.log(e.target);
			var _this = $(this);
			var account_id = $(this).parent().prevAll().eq(5).text();
			console.log(account_id);
			if(!account_id) alert('获取焦点失败，请重试！');
			console.log($(this).parent().parent().siblings(".aa"));
			if($(this).parent().parent().siblings(".aa").length>0)
			{
				console.log('ok');
				$('.aa').remove();	
				$('.aa').remove();
			} else if($('table#amount_detail_table').length>0 || $('table#amount_total_table').length>0 &&  status == 'true'){
				var loading = layer.load(1, {time: 2*1000});
				getAccountReportData(account_id);
				$('table#amount_detail_table').parent().remove();   
				$('table#amount_total_table').parent().remove();
				$(this).parent().parent().after(htmlContent);
			} else{
				var loading = layer.load(1, {time: 2*1000});
				getAccountReportData(account_id);
				$(this).parent().parent().after(htmlContent);
				status = true;
			}
		})
	

	/**
	 *	通过Pjax刷新页面数据
	 */
	function pjaxReloadData()
	{
		$.ajax({ 
			url:'/payment-manager/days-amount-list',// 跳转到 action
			data : $('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				$.pjax.reload({container: $('#days-amount-list'), data: $('form#days-amount-search-form').serialize()});
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
				requestUrl = 'http://' + window.location.hostname + '/payment-manager/days-amount-list'
			} else {
				requestUrl = 'http://' + window.location.hostname + ':' + window.location.port + '/payment-manager/days-amount-list'
			}
			window.location.href = requestUrl; 
			return;
		}

		$('#thaccountinfosearch-name_zh').val(company_name)
		pjaxReloadData();	
	})


	/**
	 *	当先择起始时间时，刷新数据
	 */
	$('#thspendreportsearch-date_start').change(function() {
		pjaxReloadData();	
	})

	/**
	 *	当先择结束时间时，刷新数据
	 */
	$('#thspendreportsearch-date_stop').change(function() {
		pjaxReloadData();
	})

	/**
	 *	获取指定时间段内的account 消耗数据
	 */
	function getAccountReportData(account_id)
	{
		$.ajax({ 
			url:'/payment-manager/get-days-account-amount',// 跳转到 action
			data : {
				'account_id'	: account_id, 
				'date_start'	: $('#thspendreportsearch-date_start').val(), 
				'date_stop'		: $('#thspendreportsearch-date_stop').val()
			},
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				if(data.status == true)
				{
					htmlContent = getAmountReportTableHtml(data.amount_info);
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
	 *	获取到的amount report数据整理成表格，供layer显示
	 */
	function getAmountReportTableHtml(amount_info)
	{
		if(!amount_info) return '<tr class = "aa"><td colspan="7"><table id="amount_total_table" class="table table-condensed table-striped">' + 
			'<thead><tr><th style="text-align:center;">总消耗 : <th><th style="text-align:center;">余额 : </th><tr></thead>' +
			'<thbody><tr class="aa"><td>' + 0 +  '<td><td>' + 0 + '</td></tr></thbody></table></td></tr><tr class="aa"><td colspan="7"><table id="amount_detail_table" class="table table-condensed table-striped"><thead><tr class="aa"><th style="text-align:center;">Account Id</th><th style="text-align:center;">日期</th><th style="text-align:center;">金额</th></tr></thead></table></td><tr> ';

		htmlContent = '<tr class="aa"><td colspan="7"><table id="amount_total_table" class="table table-condensed table-striped">' + 
			'<thead><tr><th style="text-align:center;">总消耗 : <th><th style="text-align:center;">余额 : </th><tr></thead>' +
			'<thbody><tr><td>' + amount_info.cost_info.spend_total +  '<td><td>' + amount_info.cost_info.balance + '</td></tr></thbody></table></td></tr>';

		htmlContent += '<tr class="aa"><td colspan="7"><table id="amount_detail_table" class="table">' + 
			'<thead><tr><th style="text-align:center;">Account Id</th><th style="text-align:center;">日期</th><th style="text-align:center;">花费</th></tr></thead><tbody>';
		status = true;
		for(var i=0; i<amount_info.spendlist_info.length; i++)
		{
			htmlContent += '<tr class="aa"><td>' + amount_info.spendlist_info[i].account_id + 
				'</td><td>'+ amount_info.spendlist_info[i].date_start + 
				'</td><td>'+ amount_info.spendlist_info[i].spend + '</td></tr>'
		}
		htmlContent += '</tbody></table></td></tr>';
		return htmlContent;
	}

	/**
	 *	导出操作
	 */
	$('#amount-export-button').click(function() {
		$('form#days-amount-search-form').attr('action', '/payment-manager/days-amount-export');
		$('form#days-amount-search-form').submit();
	})

})

