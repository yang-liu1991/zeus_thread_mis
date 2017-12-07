$(document).ready(function() {
	/**
	 *	定义操作类型
	 */
	ACTION_TYPE ={
		BINDING		:	1,
		REMOVIN		:	2
	}

	
	/**
	 *	点击获取BM信息时，通过ajax请求FB
	 */
	$('#get-account-info').click(function() {
		var account_id	= $('#bindingmodel-account_id').val();
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
	var action = $('#bindingmodel-action').val();
	/* 如果是单次上传，则展现出输入account_id的表单，否则展现上传文件 */
	if(action == 11)
	{
		$('#bindingmodel-single-form').css('display', 'block');
		$('#bindingmodel-upload_file-form').css('display', 'none');		
	} else {
		$('#bindingmodel-single-form').css('display', 'none');
		$('#bindingmodel-upload_file-form').css('display', 'block');
	}


	/**
	 *	文件上传操作
	 */
	$('#bindingmodel-upload_file').fileupload({
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
				//这里的索引从1开始，因为单次上传的表单中也有相同的字段
				var index = parseInt(index)+1
				if(account_infos.action_type == 'set' || account_infos.action_type == 'SET')
				{
					var setBindingHtml = buildSetBindingHtml(index, account_infos);
					$('#bindingmodel-upload_file-form').append(setBindingHtml);
					$('select[id=bindingmodel-action_type]:eq('+ index +')').val(1);
					$('select[id=bindingmodel-permitted_roles]:eq('+ index +')').val(account_infos.roles);
				} else {
					var resetBindingHtml = buildResetBindingHtml(index, account_infos);
					$('#bindingmodel-upload_file-form').append(resetBindingHtml);
					$('select[id=bindingmodel-action_type]:eq('+ index +')').val(2);
				}
			})
			$('#bindingmodel-upload_file-button').css('display', 'block');
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
		$('#bindingmodel-account_id').attr('readonly', true);
		$('#get-account-info').css('display', 'none');
		
		if(accountInfo.hasOwnProperty('name'))
		{
			$('#bindingmodel-account_name').val(accountInfo.name);
		}

		if(accountInfo.hasOwnProperty('account_status')) 
		{
			$('#bindingmodel-account_status').val(accountInfo.account_status);
			accountStatusHtml = getAccountStatus(accountInfo.account_status);
			$('#button-adaccount-status').html(accountStatusHtml);
		}

		if(accountInfo.hasOwnProperty('error')) 
		{
			alterBindingReasons(accountInfo.error);
		} else {
			if(accountInfo.account_status != 1)
			{
				$('#binding-change-info').css('display', 'block');
				$('#binding-detail').css('display', 'none');
			} else {
				$('#binding-change-info').css('display', 'block');
			}
		}
	}

	/**
	 *	当操作类型发生变化时，控制权限列表
	 *	当操作类型为绑定时，显示选择权限列表；操作类型为解绑时，隐藏选择权限列表
	 */
	$('#bindingmodel-action_type').change(function() {
		var action_type = $('#bindingmodel-action_type').val();
		if(action_type == ACTION_TYPE.BINDING)
		{
			$('#binding-permitted_roles').css('display', 'block');
		} else {
			$("#bindingmodel-permitted_roles").val("");
			$('#binding-permitted_roles').css('display', 'none');
		}
	})

	/**
	 *	Ajax提交表单
	 */
	$('.binding-submit-button').click(function() {
		/* 如果页面上有错误 */
		if($('.has-error').text() != '') return;
		var action = $('#bindingmodel-action').val();
		$.ajax({ 
			url:'/account-manager/binding-change?action=' + action,
			dataType: "json",
			data:$('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				console.log(data);
				if(data.status == true)
				{
					window.location.href="/account-manager/binding-list";
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
	$('.binding-reset-button').click(function() {
		var action = $('#bindingmodel-action').val();
		window.location.href="/account-manager/binding-change?action=" + action;
	})

	/**
	 *	删除帐户申请操作
	 */
	$('#bindingmodel-upload_file-form').on('click', 'button[id=account-del-button]', function() {
		if(confirm('确定要删除吗？'))
		{
			$(this).parent("div").remove();
		}
	})

	
	/**
	 *	Ajax提交表单
	 */
	$('#binding-submit-button').click(function() {
		/* 如果页面上有错误 */
		var has_error = $('.has-error');
		if(has_error.length > 0) return;

		$.ajax({ 
			url:'/account-manager/binding-change',
			dataType: "json",
			data:$('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				console.log(data);
				if(data.status == true)
				{
					window.location.href="/account-manager/binding-list";
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
	 *	提交Facebook绑定申请
	 */
	$('#binding_list_form').on('click', 'button[id=binding-submit]', function() {
		if(confirm('确定要提交帐户关联吗?'))
		{
			var binding_record_id = $(this).parent().prevAll(':last').children('input').val();
			var binding_record_list =  new Array();
			binding_record_list.push(binding_record_id);
			bindingChangeSubmit(binding_record_list);

		}
	});
	
	/**
	 *	驳回更新名称
	 */
	$('#binding_list_form').on('click', 'button[id=binding-reject]', function() {
		var binding_record_id	= $(this).parent().prevAll(':last').children('input').val();
		if(!binding_record_id) alert('获取焦点失败，请重试！');
		content = {'title':'驳回原因', 'message':'驳回成功！', 'action_url':'/account-manager/reject-binding'};
		bindingRejectPannel(binding_record_id, content);
	});


	/**
	 *	异常原因查看
	 */
	$('#binding_list_form').on('click', 'button[id=binding-reason]', function() {
		var binding_record_id = $(this).parent().prevAll(':last').children('input').val();
		console.log(binding_record_id);
		$.ajax({ 
			url:'/account-manager/binding-reason',// 跳转到 action
			data:{'binding_record_id'	: binding_record_id},
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
	function bindingRejectPannel(binding_record_id, content)
	{
		var config = {};
		config.area = [ '500px', '220px' ];
		config.type = 1;
		config.title = content.title;
		config.content = $('#binding_reject_reason').show();
		config.btn = [ '确定', '取消' ];
		config.yes = function(index, layero) {bindingRejectSubmit(binding_record_id, content)};
		config.cancel = function(index) {layer.closeAll();}
		layer.open(config);
	}

	/* 提交驳回 */
	function bindingRejectSubmit(binding_record_id, content)
	{
		var reject_reason = $('#bindingmodel-reason').val();
		$.ajax({ 
			url:'/account-manager/reject-binding',// 跳转到 action
			data:{'binding_record_id'	: binding_record_id, 'reject_reason'	: reject_reason},
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
			var binding_record_list =  new Array();
			$("input[type=checkbox]").each(function(){
				if($(this).prop('checked') == true) {
					binding_record_list.push($(this).val());
				}
			});

			if(binding_record_list.length == 0)
			{
				alert('请选择需要提交的记录!');
				return;
			}
			$('#button-of-submit').attr("disabled", true);
			bindingChangeSubmit(binding_record_list);
		}
	});


	/**
	 * Ajax 提交
	 */
	function bindingChangeSubmit(binding_record_list)
	{
		console.log(binding_record_list);
		$.ajax({
			url:'/account-manager/submit-binding',
			data:{'binding_record_list'	: binding_record_list},
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
	 *	生成绑定的html
	 */
	function buildSetBindingHtml(index, account_infos)
	{
		var statusHtml	= getAccountStatus(account_infos.account_info.account_status);
		var setBindingHtml = '<div><div class="form-group field-bindingmodel-account_id required has-success"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="bindingmodel-account_id">Account ID</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="bindingmodel-account_id" class="form-control" name="BindingModel[accounts]['+ index +'][account_id]" style="width:300px;" readonly="readonly" value="'+ account_infos.account_id +'"><div class="help-block"></div></span></div><div class="form-group field-bindingmodel-account_name required"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="bindingmodel-account_name">Account Name</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="bindingmodel-account_name" class="form-control" name="BindingModel[accounts]['+ index +'][account_name]" readonly="true" style="width:300px;" value="'+ account_infos.account_info.name +'"><div class="help-block"></div></span></div><div class="form-group field-bindingmodel-account_status"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="bindingmodel-account_status">Account Status</label></div><span class="col-xs-5 col-sm-4"><div id="button-adaccount-status">'+ statusHtml +'</div><input type="hidden" name="BindingModel[accounts]['+ index  +'][account_status]" id="bindingmodel-account_status" value="'+ account_infos.account_info.account_status +'"></span></div><div class="form-group field-bindingmodel-business_id required"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="bindingmodel-business_id">BM ID</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="bindingmodel-business_id" class="form-control" name="BindingModel[accounts]['+ index +'][business_id]" style="width:300px;" placeholder="" value="'+ account_infos.business_id +'"><div class="help-block"></div></span></div><div class="form-group field-bindingmodel-action_type required"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="bindingmodel-action_type">操作类型</label></div><span class="col-xs-5 col-sm-4"><select id="bindingmodel-action_type" class="form-control" name="BindingModel[accounts]['+ index +'][action_type]" style="width:150px" value=""><option value="">请选择操作类型</option><option value="1">绑定</option><option value="2">解绑</option></select><div class="help-block"></div></span></div><div class="form-group field-bindingmodel-permitted_roles"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="bindingmodel-permitted_roles">分配角色</label></div><span class="col-xs-5 col-sm-4"><select id="bindingmodel-permitted_roles" class="form-control" name="BindingModel[accounts]['+ index +'][permitted_roles]" style="width:150px" value=""><option value="">请选择角色分配</option><option value="GENERAL_USER">GENERAL_USER</option><option value="REPORTS_ONLY">REPORTS_ONLY</option></select><div class="help-block"></div></span></div><button type="button" id="account-del-button" class="btn btn-danger btn-xs" style="margin-left:60px;">删除</button><hr style="width:90%"></div>';
		return setBindingHtml;
	}


	/**
	 *	生成解绑的html
	 */
	function buildResetBindingHtml(index, account_infos)
	{
		var statusHtml	= getAccountStatus(account_infos.account_info.account_status);
		var resetBindingHtml = '<div><div class="form-group field-bindingmodel-account_id required has-success"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="bindingmodel-account_id">Account ID</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="bindingmodel-account_id" class="form-control" name="BindingModel[accounts]['+ index +'][account_id]" style="width:300px;" readonly="readonly" value="'+ account_infos.account_id +'"><div class="help-block"></div></span></div><div class="form-group field-bindingmodel-account_name required"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="bindingmodel-account_name">Account Name</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="bindingmodel-account_name" class="form-control" name="BindingModel[accounts]['+ index +'][account_name]" readonly="true" style="width:300px;" value="'+ account_infos.account_info.name +'"><div class="help-block"></div></span></div><div class="form-group field-bindingmodel-account_status"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="bindingmodel-account_status">Account Status</label></div><span class="col-xs-5 col-sm-4"><div id="button-adaccount-status">'+ statusHtml +'</div><input type="hidden" name="BindingModel[accounts]['+ index +'][account_status]" id="bindingmodel-account_status" value="'+ account_infos.account_info.account_status +'"></span></div><div class="form-group field-bindingmodel-business_id required"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="bindingmodel-business_id">BM ID</label></div><span class="col-xs-5 col-sm-4"><input type="text" id="bindingmodel-business_id" class="form-control" name="BindingModel[accounts]['+ index +'][business_id]" style="width:300px;" placeholder="" value="'+ account_infos.business_id +'"><div class="help-block"></div></span></div><div class="form-group field-bindingmodel-action_type required"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="bindingmodel-action_type">操作类型</label></div><span class="col-xs-5 col-sm-4"><select id="bindingmodel-action_type" class="form-control" name="BindingModel[accounts]['+ index +'][action_type]" style="width:150px" value=""><option value="">请选择操作类型</option><option value="1">绑定</option><option value="2">解绑</option></select><div class="help-block"></div></span></div><div class="form-group field-bindingmodel-permitted_roles" style="display:none;"><div class="col-xs-3 col-sm-2 text-right"><label class="col-lg-l control-label" for="bindingmodel-permitted_roles">分配角色</label></div><span class="col-xs-5 col-sm-4"><select id="bindingmodel-permitted_roles" class="form-control" name="BindingModel[accounts]['+ index +'][permitted_roles]" style="width:150px"><option value="">请选择角色分配</option><option value="GENERAL_USER">GENERAL_USER</option><option value="REPORTS_ONLY">REPORTS_ONLY</option></select><div class="help-block"></div></span></div><button type="button" id="account-del-button" class="btn btn-danger btn-xs" style="margin-left:60px;">删除</button><hr style="width:90%"></div>';
		return resetBindingHtml;
	}
})	
