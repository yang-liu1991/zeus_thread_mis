$(document).ready(function() {
	/**
	 *	点击获取BM信息时，通过ajax请求FB
	 */
	$('#get-account-info').click(function() {
		var account_id	= $('#changenamemodel-account_id').val();
		console.log(account_id);

		if(!account_id) 
		{
			alert('请填写BM信息！');
			return;
		}
		getAccountInfo(account_id);
	})

	/**
	 *	判断是批量上传还是单次操作
	 */
	var action = $('#changenamemodel-action').val();
	/* 如果是单次上传，则展现出输入account_id的表单，否则展现上传文件 */
	if(action == 11)
	{
		$('#changenamemodel-single-form').css('display', 'block');
		$('#changenamemodel-upload_file-form').css('display', 'none');		
	} else {
		$('#changenamemodel-single-form').css('display', 'none');
		$('#changenamemodel-upload_file-form').css('display', 'block');
	}


	/**
	 *	文件上传操作
	 */
	$('#changenamemodel-upload_file').fileupload({
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
				formHtml = buildFormHtml(index, account_infos);
				$('#changenamemodel-upload_file-form').append(formHtml);
			})
			$('#changenamemodel-upload_file-button').css('display', 'block');
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
	function getAccountInfo(account_id)
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
					alterBindingReasons(data.error_message);	
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
	 *	显示JSON数据
	 */
	function alterBindingReasons(reasons)
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
	function setAttributes(accountInfo)
	{
		$('#changenamemodel-account_id').attr('readonly', true);
		$('#get-account-info').css('display', 'none');
		
		if(accountInfo.hasOwnProperty('name'))
		{
			$('#changenamemodel-account_name').val(accountInfo.name);
		}

		if(accountInfo.hasOwnProperty('account_status')) 
		{
			$('#changenamemodel-account_status').val(accountInfo.account_status);
			accountStatusHtml = getAccountStatus(accountInfo.account_status);
			$('#button-adaccount-status').html(accountStatusHtml);
		}

		if(accountInfo.hasOwnProperty('error')) 
		{
			alterBindingReasons(accountInfo.error);
		} else {
			if(accountInfo.account_status != 1)
			{
				$('#name-change-info').css('display', 'block');
			} else {
				$('#name-change-info').css('display', 'block');
			}
		}
	}

	
	/**
	 *	Ajax提交表单
	 */
	$('.name-submit-button').click(function() {
		/* 如果页面上有错误 */
		if($('.has-error').text() != '') return;
		var action = $('#changenamemodel-action').val();
		$.ajax({ 
			url:'/account-manager/name-change?action=' + action,
			dataType: "json",
			data:$('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				console.log(data);
				if(data.status == true)
				{
					window.location.href="/account-manager/name-list";
				} else {
					alterBindingReasons(data.error_message);
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
	$('.name-reset-button').click(function() {
		var action = $('#changenamemodel-action').val();
		window.location.href="/account-manager/name-change?action=" + action;
	})


	/**
	 *	删除帐户申请操作
	 */
	$('#changenamemodel-upload_file-form').on('click', 'button[id=account-del-button]', function() {
		if(confirm('确定要删除吗？'))
		{
			$(this).parent("div").remove();
		}
	})

	
	/**
	 *	提交Facebook更新名称
	 */
	$('#name_list_form').on('click', 'button[id=change-submit]', function() {
		if(confirm('确定要更新名称吗?'))
		{
			var change_record_id = $(this).parent().prevAll(':last').children('input').val();
			var change_record_list =  new Array();
			change_record_list.push(change_record_id);
			nameChangeSubmit(change_record_list);
		}
	});
	
	/**
	 *	驳回更新名称
	 */
	$('#name_list_form').on('click', 'button[id=change-reject]', function() {
		var change_record_id	= $(this).parent().prevAll(':last').children('input').val();
		if(!change_record_id) alert('获取焦点失败，请重试！');
		content = {'title':'驳回原因', 'message':'驳回成功！', 'action_url':'/account-manager/reject-change'};
		changeRejectPannel(change_record_id, content);
	});


	/**
	 *	异常原因查看
	 */
	$('#name_list_form').on('click', 'button[id=change-reason]', function() {
		var change_record_id = $(this).parent().prevAll(':last').children('input').val();
		$.ajax({ 
			url:'/account-manager/change-reason',// 跳转到 action
			data:{'change_record_id'	: change_record_id},
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
	function changeRejectPannel(change_record_id, content)
	{
		var config = {};
		config.area = [ '500px', '220px' ];
		config.type = 1;
		config.title = content.title;
		config.content = $('#change_reject_reason').show();
		config.btn = [ '确定', '取消' ];
		config.yes = function(index, layero) {changeRejectSubmit(change_record_id, content)};
		config.cancel = function(index) {layer.closeAll();}
		layer.open(config);
	}

	/* 提交驳回 */
	function changeRejectSubmit(change_record_id, content)
	{
		var reject_reason = $('#changenamemodel-reason').val();
		$.ajax({ 
			url:'/account-manager/reject-change',// 跳转到 action
			data:{'change_record_id'	: change_record_id, 'reject_reason'	: reject_reason},
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

	/* 获取URL中的参数 */
	function getUrlParam(name)
	{
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
		var r = window.location.search.substr(1).match(reg);  //匹配目标参数
		if (r != null) return unescape(r[2]); return null; //返回参数值
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
	$('#button-of-submit').click(function()
	{
		if(confirm('确认要全部提交吗?'))
		{
			var change_record_list =  new Array();
			$("input[type=checkbox]").each(function(){
				if($(this).prop('checked') == true)
				{
					change_record_list.push($(this).val());
				}
			});

			if(change_record_list.length == 0)
			{
				alert('请选择需要提交的记录!');
				return;
			}
			$('#button-of-submit').attr("disabled", true);
			nameChangeSubmit(change_record_list);
		}
	});


	/**
	 * Ajax 提交
	 */
	function nameChangeSubmit(change_record_list)
	{

		$.ajax({
			url:'/account-manager/submit-change',
			data:{'change_record_list'	: change_record_list},
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
	 *	生成批量的表单html
	 */
	function buildFormHtml(index, account_infos)
	{
		var statusHtml = getAccountStatus(account_infos.account_info.account_status);
		var fromHtml = '<div><div class="form-group field-changenamemodel-account_id required"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="changenamemodel-accounts">Account ID</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="changenamemodel-account_id" class="form-control" name="ChangeNameModel[accounts]['+ index +'][account_id]" style="width:300px;" placeholder="" value="'+ account_infos.account_id +'" readonly="readonly"><div class="help-block"></div></span></div><div class="form-group field-changenamemodel-account_name"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="changenamemodel-accounts">Account Name</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="changenamemodel-account_name" class="form-control" name="ChangeNameModel[accounts]['+index+'][account_name]" readonly="true" value="'+ account_infos.account_info.name +'" style="width:300px;"><div class="help-block"></div></span></div><div class="form-group field-changenamemodel-account_status"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="changenamemodel-accounts">Account Status</label></div><span class="col-xs-5 col-sm-4"><div id="button-adaccount-status">'+ statusHtml +'<input type="hidden" id="changenamemodel-account_status" name="ChangeNameModel[accounts]['+ index +'][account_status]" value="'+ account_infos.account_info.account_status  +'"></div></span></div><div class="form-group field-changenamemodel-new_account_name required"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="changenamemodel-accounts">New Account Name</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="changenamemodel-new_account_name" class="form-control" name="ChangeNameModel[accounts]['+index+'][new_account_name]" style="width:300px;" value="'+ account_infos.account_name +'"><div class="help-block"></div></span></div><button type="button" id="account-del-button" class="btn btn-danger btn-xs" style="margin-left:60px;">删除</button><hr style="width:90%"></div>';
		return fromHtml;
	}
})	
