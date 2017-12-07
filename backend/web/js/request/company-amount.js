$(function() {
		var status = false;
		var btn = $('.btn-success');
		$('.th-amount-list').on('click', 'button[id=amount-detail-button]', function(e) {
			console.log(e.target);
			var _this = $(this);
			var entity_id = $(this).parent().prevAll().eq(5).text();
			console.log(entity_id);
			if(!entity_id) alert('获取焦点失败，请重试！');
			console.log($(this).parent().parent().siblings(".aa"));
			if($(this).parent().parent().siblings(".aa").length>0)
			{
				console.log('ok');
				$('.aa').remove();	
				$('.aa').remove();
			} else if($('table#amount_detail_table').length>0 || $('table#amount_total_table').length>0 &&  status == 'true'){
				var loading = layer.load(1, {time: 2*1000});
				getAccountReportData(entity_id);
				$('table#amount_detail_table').parent().remove();   
				$('table#amount_total_table').parent().remove();
				$(this).parent().parent().after(htmlContent);
			} else{
				var loading = layer.load(1, {time: 2*1000});
				getAccountReportData(entity_id);
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
			url:'/payment-manager/company-amount-list',// 跳转到 action
			data : $('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				$.pjax.reload({container: $('#company-amount-list'), data: $('form#company-amount-search-form').serialize()});
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
	$('#thentityinfosearch-search').change(function() {
		var company_name = $('#thentityinfosearch-search option:last').text();
		console.log(company_name);
		var select_value = $('#thentityinfosearch-search').val();
		if(select_value == "")
		{
			if(window.location.port == "")
			{
				requestUrl = 'http://' + window.location.hostname + '/payment-manager/company-amount-list'
			} else {
				requestUrl = 'http://' + window.location.hostname + ':' + window.location.port + '/payment-manager/company-amount-list'
			}
			window.location.href = requestUrl; 
			return;
		}

		$('#thentityinfosearch-name_zh').val(company_name)
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
	 *	获取指定时间段内的company消耗数据
	 */
	function getAccountReportData(entity_id)
	{
		$.ajax({ 
			url:'/payment-manager/get-company-account-amount',// 跳转到 action
			data : {
				'entity_id'		: entity_id, 
				'date_start'	: $('#thspendreportsearch-date_start').val(), 
				'date_stop'		: $('#thspendreportsearch-date_stop').val()
			},
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				if(data.status == true)
				{
					htmlContent = getAmountReportTableHtml(data.company_amount_info);
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
	function getAmountReportTableHtml(company_amount_info)
	{
		if(!company_amount_info) return '<tr class="aa"><td colspan="7"><table id="amount_detail_table" class="table table-condensed table-striped"><thead><tr class="aa"><th style="text-align:center;">Account Id</th><th style="text-align:center;">金额</th><th style="text-align:center;">余额</th></tr></thead></table></td><tr> ';

		htmlContent = '<tr class="aa"><td colspan="7"><table id="amount_detail_table" class="table">' + 
			'<thead><tr><th style="text-align:center;">Account Id</th><th style="text-align:center;">金额</th><th style="text-align:center;">余额</th></tr></thead><tbody>';
		status = true;
		for(var i=0; i<company_amount_info.length; i++)
		{
			htmlContent += '<tr class="aa"><td>' + company_amount_info[i].account_id + 
				'</td><td>'+ company_amount_info[i].spend_total + 
				'</td><td>'+ company_amount_info[i].balance + '</td></tr>'
		}
		htmlContent += '</tbody></table></td></tr>';
		return htmlContent;
	}

	/**
	 *	导出操作
	 */
	$('#amount-export-button').click(function() {
		$('form#company-amount-search-form').attr('action', '/payment-manager/company-amount-export');
		$('form#company-amount-search-form').submit();
	})

})

