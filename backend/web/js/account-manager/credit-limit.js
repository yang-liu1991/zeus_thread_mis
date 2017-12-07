$(document).ready(function() {
		
	/**
	 *	定义操作类型
	 */
	ACTION_TYPE = {
		ADD		: 1,
		DEL		: 2,
		RESET	: 3
	}

	
	/**
	 *	点击获取BM信息时，通过ajax请求FB
	 */
	$('#get-spend-info').click(function() {
		var account_id	= $('#creditlimitmodel-account_id').val();
		console.log(account_id);

		if(!account_id) 
		{
			alert('请填写BM信息！');
			return;
		}

		getCreditLimit(account_id);
	})

	/**
	 *	判断是批量上传还是单次操作
	 */
	var action = $('#creditlimitmodel-action').val();
	/* 如果是单次上传，则展现出输入account_id的表单，否则展现上传文件 */
	if(action == 11)
	{
		$('#creditlimitmodel-single-form').css('display', 'block');
		$('#creditlimitmodel-upload_file-form').css('display', 'none');		
	} else {
		$('#creditlimitmodel-single-form').css('display', 'none');
		$('#creditlimitmodel-upload_file-form').css('display', 'block');
	}


	/**
	 *	文件上传操作
	 */
	$('#creditlimitmodel-upload_file').fileupload({
		url:'/account-manager/upload-file',	
		dataType:'json',
		progressall: function(e, data) {
			layer_loading = layer.load(0);
		},
		add: function(e, data) {
                var uploadErrors = [];
                var acceptFileTypes = /(\.sheet|\/csv)$/i;
				console.log(data.originalFiles[0]);
                if(data.originalFiles[0]['type'].length && !acceptFileTypes.test(data.originalFiles[0]['type'])) {
                    uploadErrors.push('Not an accepted file type');
                }
                if(data.originalFiles[0]['size'].length && data.originalFiles[0]['size'] > 5000000) {
                    uploadErrors.push('Filesize is too big');
                }
                if(uploadErrors.length > 0) {
                    alert(uploadErrors.join("\n"));
                } else {
                    data.submit();
                }
        },
		done:function(e, data) {
			console.log(data);
			$.each(data.result.accountInfoList, function(index, account_infos) {
				console.log(index);	
				console.log(account_infos);
				var index = parseInt(index)+1;
				formHtml = buildFormHtml(index, account_infos);
				$('#creditlimitmodel-upload_file-form').append(formHtml);
				if(account_infos.action_type == 'addition')
				{
					$('select[id=creditlimitmodel-action_type]:eq('+ index +')').val(1);
				} else if(account_infos.action_type == 'subtraction') {
					$('select[id=creditlimitmodel-action_type]:eq('+ index +')').val(2);
				} else if(account_infos.action_type == 'reset') {
					$('select[id=creditlimitmodel-action_type]:eq('+ index +')').val(3);
					$('.field-creditlimitmodel-number:eq('+ index +')').val(0);
					$('.field-creditlimitmodel-number:eq('+ index +')').css('display', 'none');
				}
			})
			$('#creditlimitmodel-upload_file-button').css('display', 'block');
			layer.close(layer_loading);
		},
	})

	/**
	 *	定义account status
	 */
	function getAccountStatus(account_status)
	{
		switch(account_status)
		{
			case 1: return '<span class="btn btn-xs btn-success">ACTIVE</span>';break;
			case 2: return '<span class="btn btn-xs btn-danger">DISABLED</span>';break;
			case 3: return '<span class="btn btn-xs btn-warning">UNSETTLED</span>';break;
			case 7: return '<span class="btn btn-xs btn-danger">PENDING_RISK_REVIEW</span>';break;
			case 9: return '<span class="btn btn-xs btn-warning">IN_GRACE_PERIOD</span>';break;
			case 100: return '<span class="btn btn-xs btn-warning">PENDING_CLOSURE</span>';break;
			case 101: return '<span class="btn btn-xs btn-warning">CLOSED</span>';break;
			case 102: return '<span class="btn btn-xs btn-warning">PENDING_SETTLEMENT</span>';break;
			case 201: return '<span class="btn btn-xs btn-warning">ANY_ACTIVE</span>';break;
			case 202: return '<span class="btn btn-xs btn-warning">ANY_CLOSED</span>';break;
			default: return '<span class="btn btn-xs btn-warning">UNKNOW_STATUS</span>';break;
		}

	}

	/**
	 *	Ajax 获取信息
	 */
	function getCreditLimit(account_id)
	{
		$.ajax({ 
			url:'/account-manager/get-account-info',
			data:$('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				console.log(data);
				if(data.status == true)
				{
					setAttributes(data.accountInfo);
				} else {
					alterSpendcpaReasons(data.error_message);
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
	 *	获取spend_cpa失败时，显示JSON数据
	 */
	function alterSpendcpaReasons(reasons)
	{
		console.log(reasons);
		var config = {};
		config.area = [ '600px', '300px' ];
		config.type = 1;
		config.title = '错误信息';
		config.skin	= 'layui-layer-rim';
		layer.open(config);
		$('.layui-layer-content').JSONView(reasons);
	}


	/**
	 *	将Ajax返回的数据进行赋值
	 */
	function setAttributes(spendInfo)
	{
		$('#creditlimitmodel-account_id').attr('readonly', true);
		$('#get-spend-info').css('display', 'none');
		
		if(spendInfo.hasOwnProperty('name'))
		{
			$('#creditlimitmodel-account_name').val(spendInfo.name);
		}

		if(spendInfo.hasOwnProperty('min_spend_cap')) 
		{ 
			if(spendInfo.min_spend_cap < spendInfo.spend_cap)
			{
				$('#creditlimitmodel-min_spend_cap').val(spendInfo.min_spend_cap); 
			} else {
				$('#creditlimitmodel-min_spend_cap').val(spendInfo.spend_cap); 
			}
		}

		if(spendInfo.hasOwnProperty('spend_cap')) 
		{
			$('#creditlimitmodel-spend_cap').val(spendInfo.spend_cap);
		}

		if(spendInfo.hasOwnProperty('amount_spent')) 
		{
			$('#creditlimitmodel-amount_spent').val(spendInfo.amount_spent);
		}

		if(spendInfo.hasOwnProperty('account_status')) 
		{
			$('#creditlimitmodel-account_status').val(spendInfo.account_status);
			accountStatusHtml = getAccountStatus(spendInfo.account_status);
			$('#button-adaccount-status').html(accountStatusHtml);
		}

		if(spendInfo.hasOwnProperty('error')) 
		{
			alterSpendcpaReasons(spendInfo.error);
		} else {
			if(spendInfo.account_status != 1)
			{
				$('#account-creditlimit-info').css('display', 'block');
				$('#account-creditlimit-detail').css('display', 'none');
			} else {
				$('#account-creditlimit-info').css('display', 'block');
			}
		}
	}

	/**
	 *	当操作类型发生变化，即操作类型为清零时，隐藏调整额度的输入框
	 */
	$('#creditlimitmodel-action_type').change(function() {
		var action_type = $('#creditlimitmodel-action_type').val();
		if(action_type == ACTION_TYPE.RESET)
		{
			$('.field-creditlimitmodel-number').css('display', 'none');
		} else {
			$('.field-creditlimitmodel-number').css('display', 'block');
		}
	})


	/**
	 *	Ajax提交表单
	 */
	$('.creditlimit-submit-button').click(function() {
		/* 如果页面上有错误 */
		var has_error = $('.has-error');
		if(has_error.length > 0) return;
		var action = $('#creditlimitmodel-action').val();

		$.ajax({ 
			url:'/account-manager/spendcap-change?action=' + action,
			dataType: "json",
			data:$('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				console.log(data);
				if(data.status == true)
				{
					window.location.href="/account-manager/spendcap-list";
				} else {
					alterSpendcpaReasons(data.error_message);
				}
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
	 *	重置页面
	 */
	$('.creditlimit-reset-button').click(function() {
		var action = $('#creditlimitmodel-action').val();
		window.location.href="/account-manager/spendcap-change?action=" + action;
	})


	/**
	 *	删除帐户申请操作
	 */
	$('#creditlimitmodel-upload_file-form').on('click', 'button[id=account-del-button]', function() {
		if(confirm('确定要删除吗？'))
		{
			$(this).parent("div").remove();
		}
	})


	/**
	 *	提交Facebook额度变更
	 */
	$('#credit-limit_list_form').on('click', 'button[id=credit-limit-submit]', function() {
		if(confirm('确定要提交帐户额度变更吗?'))
		{
			var credit_limit_record_id = $(this).parent().prevAll(':last').children('input').val();
			var credit_limit_record_list =  new Array();
			credit_limit_record_list.push(credit_limit_record_id);
			creditlimitChangeSubmit(credit_limit_record_list);
		}
	});
	
	/**
	 *	驳回额度调整
	 */
	$('#credit-limit_list_form').on('click', 'button[id=credit-limit-reject]', function() {
		var credit_limit_record_id	= $(this).parent().prevAll(':last').children('input').val();

		if(!credit_limit_record_id) alert('获取焦点失败，请重试！');
		content = {'title':'驳回原因', 'message':'驳回成功！', 'action_url':'/account-manager/reject-credit-limit'};
		CreditlimitRejectPannel(credit_limit_record_id, content);
	});


	/**
	 *	异常原因查看
	 */
	$('#credit-limit_list_form').on('click', 'button[id=credit-limit-reason]', function() {
		var credit_limit_record_id = $(this).parent().prevAll(':last').children('input').val();
		$.ajax({ 
			url:'/account-manager/credit-limit-reason',// 跳转到 action
			data:{'credit_limit_record_id'	: credit_limit_record_id},
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				console.log(data);
				if(data.message == 'success') 
				{
					reason = eval('(' + data.reason + ')'); 
					alterAccountReasons(reason);
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
	 *	操作失败Json提示信息
	 */
	function alterAccountReasons(reasons)
	{
		var config = {};
		config.area = [ '700px', '250px' ];
		config.type = 1;
		config.title = '错误信息';
		config.skin	= 'layui-layer-rim';
		config.cancel= function(){ window.location.reload();};
		layer.open(config);
		$('.layui-layer-content').JSONView(reasons);
	}
	
	/* 显示编辑面板 */
	function CreditlimitRejectPannel(credit_limit_record_id, content)
	{
		var config = {};
		config.area = [ '500px', '220px' ];
		config.type = 1;
		config.title = content.title;
		config.content = $('#creditlimit_reject_reason').show();
		config.btn = [ '确定', '取消' ];
		config.yes = function(index, layero) {CreditlimitRejectSubmit(credit_limit_record_id, content)};
		config.cancel = function(index) {layer.closeAll();}
		layer.open(config);
	}

	/* 提交驳回 */
	function CreditlimitRejectSubmit(credit_limit_record_id, content)
	{
		var reject_reason = $('#creditlimitmodel-reason').val();
		$.ajax({ 
			url:'/account-manager/reject-credit-limit',// 跳转到 action
			data:{'credit_limit_record_id'	: credit_limit_record_id, 'reject_reason'	: reject_reason},
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				console.log(data);
				if(data.message == 'success') alert('驳回成功！');
				window.location.reload();
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
	 * 当点击全选时,所有的筛选框标记为选中状态
	 */
	$('#button-of-all').click(function() {
		$('input[name="checkbox"]').prop('checked', true);
	});


	/**
	 * 当点击全不选时,所有筛选框标记为未选中状态
	 */
	$('#button-of-none').click(function() {
		$('input[name="checkbox"]').prop('checked', false);
	});


	/**
	 * 当点击提交时,所有选中的都提交
	 */
	$('#button-of-submit').click(function() {
		if(confirm('确认要全部提交吗?'))
		{
			var credit_limit_record_list =  new Array();
			$("input[type=checkbox]").each(function(){
				if($(this).prop('checked') == true)
				{
					credit_limit_record_list.push($(this).val());
				}
			});

			if(credit_limit_record_list.length == 0)
			{
				alert('请选择需要提交的记录!');
				return;
			}
			$('#button-of-submit').attr("disabled", true);
			creditlimitChangeSubmit(credit_limit_record_list);
		}
	});


	/**
	 * Ajax 提交
	 */
	function creditlimitChangeSubmit(credit_limit_record_list)
	{
		console.log(credit_limit_record_list);
		$.ajax({
			url:'/account-manager/submit-credit-limit',
			data:{'credit_limit_record_list'	: credit_limit_record_list},
			type:'post',
			cache:false,
			async:false,
			dataType: "json",
			beforeSend:function(XMLHttpRequest) {
				$('#submit-loading').html('<img src="http://'+ window.location.host +'/js/account-manager/loading.gif">');
			},
			success:function(data) {
				if(data.message == 'success')
				{
					alert('提交成功！');
					window.location.reload();
				} else {
					console.log(data.error_message_list);
					alterAccountReasons(data.error_message_list);
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
				console.log(XMLHttpRequest.status);
				console.log(XMLHttpRequest.readyState);
				console.log(textStatus);
				alert("系统繁忙,请稍后重试!");
			},
			complete:function(XMLHttpRequest, textStatus) {
				$('#submit-loading').html('');
				$('#button-of-submit').removeAttr('disabled');
			}
		})
	}

	/**
	 *	生成批量表单的html
	 */
	function buildFormHtml(index, account_infos)
	{
		var statusHtml = getAccountStatus(account_infos.account_info.account_status);
		var formHtml = '<div><div class="form-group field-creditlimitmodel-account_id required has-success"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="creditlimitmodel-account_id">Account ID</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="creditlimitmodel-account_id" class="form-control" name="CreditLimitModel[accounts]['+ index +'][account_id]" style="width:300px;" placeholder="" readonly="readonly" value="'+ account_infos.account_id +'"><div class="help-block"></div></span></div>	<div class="form-group field-creditlimitmodel-account_name"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="creditlimitmodel-account_name">Account Name</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="creditlimitmodel-account_name" class="form-control" name="CreditLimitModel[accounts]['+ index +'][account_name]" readonly="true" style="width:300px;" value="'+ account_infos.account_info.name +'"><div class="help-block"></div></span></div><div class="form-group field-creditlimitmodel-account_status"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="creditlimitmodel-account_status">Account Status</label></div><span class="col-xs-5 col-sm-4"><div id="button-adaccount-status">'+ statusHtml +'</div><input type="hidden" name="CreditLimitModel[accounts]['+ index +'][account_status]" id="creditlimitmodel-account_status" value="'+ account_infos.account_info.account_status +'"></span></div><div class="form-group field-creditlimitmodel-spend_cap"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="creditlimitmodel-spend_cap">Current Limit</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="creditlimitmodel-spend_cap" class="form-control" name="CreditLimitModel[accounts]['+ index +'][spend_cap]" readonly="true" style="width:300px;" value="' + account_infos.account_info.spend_cap + '"><div class="help-block"></div></span></div><div class="form-group field-creditlimitmodel-min_spend_cap"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="creditlimitmodel-min_spend_cap">Can\'t be lower than</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="creditlimitmodel-min_spend_cap" class="form-control" name="CreditLimitModel[accounts]['+ index +'][min_spend_cap]" readonly="true" style="width:300px;" value="'+ account_infos.account_info.min_spend_cap +'"><div class="help-block"></div></span></div><div class="form-group field-creditlimitmodel-amount_spent"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="creditlimitmodel-amount_spent">Amount Spent</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="creditlimitmodel-amount_spent" class="form-control" name="CreditLimitModel[accounts]['+ index +'][amount_spent]" readonly="true" style="width:300px;" value="'+ account_infos.account_info.amount_spent +'"><div class="help-block"></div></span></div><div class="form-group field-creditlimitmodel-action_type required"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="creditlimitmodel-action_type">操作类型</label></div><span class="col-xs-5 col-sm-4"><select id="creditlimitmodel-action_type" class="form-control" name="CreditLimitModel[accounts]['+ index +'][action_type]" style="width:100px"><option value="1">增加</option><option value="2">减少</option><option value="3">清零</option></select><div class="help-block"></div></span></div><div class="form-group field-creditlimitmodel-number required"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="creditlimitmodel-number">调整额度</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="creditlimitmodel-number" class="form-control" name="CreditLimitModel[accounts]['+ index +'][number]" style="width:300px;" placeholder="" value="'+ account_infos.number +'"><div class="help-block"></div></span></div><button type="button" id="account-del-button" class="btn btn-danger btn-xs" style="margin-left:60px;">删除</button><hr style="width:90%"></div>';
		return formHtml;
	}
})
