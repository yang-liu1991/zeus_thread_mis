$(function(){
	init();		
});

/**
 *	定义审核状态
 *	1: 审核成功
 *	2: 审核失败
 */
auditStatus = {
	ACCEPT : 2,
	REFUSE : 3,
}

function init(){
	var id = $('#entity_id').val();
	/**
	 *	实体详情页面审核操作
	 */
	$('#audit_accept_detail').click(id, function(event) {
		auditPannel(auditStatus.ACCEPT);
		console.log(event.data);
		auditCommit(auditStatus.ACCEPT, event.data);
	});

	$('#audit_refuse_detail').click(id, function(event) {
		auditPannel(auditStatus.REFUSE);
		console.log(event.data);
		auditCommit(auditStatus.REFUSE, event.data);
	});

	/**
	 *	查看page link
	 */
	$('#promotable_page_ids').on('click', function(event) {
		openPageLink(id);
	});

	/**
	 *	点击时，放大图片
	 */
	$('#business_registration').zoom();
}

/**
 *	列表页面的审核操作
 */
$('#entity_list_form').on('click', 'button[id=audit_accept]', function(){
	var id = $(this).parent().prevAll(':last').text();
	auditAccept(id);
	console.log(id);
})

$('#entity_list_form').on('click', 'button[id=audit_refuse]', function(){
	var id = $(this).parent().prevAll(':last').text();
	auditRefuse(id);
	console.log(id);
})

/* 备注信息 */
$('#entity_list_form').on('click', 'button[id=entity_note]', function(){
	var id = $(this).parent().prevAll(':last').text();
	entityNote(id);
	console.log(id);
});

function auditAccept(id){
	auditPannel(auditStatus.ACCEPT);
	auditCommit(auditStatus.ACCEPT, id);
}

function auditRefuse(id){
	auditPannel(auditStatus.REFUSE);
	auditCommit(auditStatus.REFUSE, id);
}

function entityNote(id) {
	notePannel();
	noteCommit(id);
}

/**
 * 显示审核参数填写面板
 */
function auditPannel(status){
	console.log('status : ' + status);
	var config = {};
	config.area = [ '600px', '460px' ];
	$('#audit_status').val(status);
	config.type = 1;
	config.title = '审核确认信息';
	config.content = $('#audit_message').show();
	config.btn = [ '确定', '取消' ];
	config.yes = function(index, layero) {$("#audit_accept_form").submit();};
	config.cancel = function(index) {layer.closeAll();}
	layer.open(config);
}

/**
 * 显示审核参数填写面板
 */
function notePannel(){
	var config = {};
	config.area = [ '600px', '460px' ];
	config.type = 1;
	config.title = '备注信息';
	config.content = $('#entity_message').show();
	config.btn = [ '确定', '取消' ];
	config.yes = function(index, layero) {$("#entity_note_form").submit();};
	config.cancel = function(index) {layer.closeAll();}
	layer.open(config);
}

/**
 *	Ajax提交审核
 */
function auditCommit(status, id)
{
	var auditdata = {
		submitHandler : function(form) {
			$.ajax({ 
				url:'/admin-manager/entity-audit',// 跳转到 action
				data:{  
					'id'	: id,
					'audit_status'	: status,  
					'audit_message'	: $('#audit_text').val(),
				},
				type:'post',
				cache:false,  
				async:false,
				//dataType:'json',
				success:function(data) {  
					if(data.message == 'success' && data.status == true)
					{
						alert('审核提交成功！');
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
	}

	$('#audit_accept_form')[0].reset();
	$('#audit_accept_form').validate(auditdata).resetForm();
}


/**
 *	Ajax提交备注信息
 */
function openPageLink(id)
{
	$.ajax({ 
		url:'/admin-manager/get-page-link',// 跳转到 action
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


/**
 *	Ajax提交备注信息
 */
function noteCommit(id, entityNote)
{
	var notedata = {
		submitHandler : function(form) {
			$.ajax({ 
				url:'/account-executive/account-comment',// 跳转到 action
				data:{  
					'id'	: id,
					'entity_note'	: $('#entity_text').val(),
				},
				type:'post',
				cache:false,  
				async:false,
				//dataType:'json',
				success:function(data) {  
					if(data.message == 'success' && data.status == true)
					{
						alert('备注信息提交成功！');
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
	}

	$('#entity_note_form')[0].reset();
	$('#entity_note_form').validate(notedata).resetForm();
}
