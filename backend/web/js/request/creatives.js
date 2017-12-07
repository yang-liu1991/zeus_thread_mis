/**
 *	定义审核状态
 *	1: 等待审核
 *	2: 审核成功
 *	3: 审核失败
 */
auditStatus = {
	ACCEPT : 2,
	REFUSE : 3,
};


/**
 *	列表页面的审核操作
 */
$('.item-view-form').on('click', 'button[id=audit_accept_detail]', function(){
	var account_id = $(this).parent().parent().find('div input[id=item-view-accountid]').attr('value');
	var ad_id = $(this).parent().parent().find('div input[id=item-view-adid]').attr('value');
	auditAccept(account_id, ad_id);
	console.log(account_id, ad_id);
});

$('.item-view-form').on('click', 'button[id=audit_refuse_detail]', function(){
	var account_id = $(this).parent().parent().find('div input[id=item-view-accountid]').attr('value');
	var ad_id = $(this).parent().parent().find('div input[id=item-view-adid]').attr('value');
	auditRefuse(account_id, ad_id);
	console.log(account_id, ad_id);
});

$('#creative-search').click(function() {
	$('form#creatives-view-form').attr('action', '/admin-manager/creatives-view');
	$('form#creatives-view-form').submit();
});


/* 导出操作 */
$('#creative-export').click(function() {
	$('form#creatives-view-form').attr('action', '/admin-manager/creatives-export');
	$('form#creatives-view-form').submit();
});

function auditAccept(account_id, ad_id){
	auditPannel(auditStatus.ACCEPT);
	auditCommit(auditStatus.ACCEPT, account_id, ad_id);
}

function auditRefuse(account_id, ad_id){
	auditPannel(auditStatus.REFUSE);
	auditCommit(auditStatus.REFUSE, account_id, ad_id);
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
	config.cancel = function(index) {layer.closeAll();};
	layer.open(config);
}


/**
 *	Ajax提交审核
 */
function auditCommit(status, account_id, ad_id) {
	var auditdata = {
		submitHandler: function (form) {
			$.ajax({
				url: '/admin-manager/creatives-audit',// 跳转到 action
				data: {
					'account_id': account_id,
					'ad_id': ad_id,
					'audit_status': status,
					'audit_message': $('#audit_text').val(),
				},
				type: 'post',
				cache: false,
				async: false,
				//dataType:'json',
				success: function (data) {
					if (data.message == 'success' && data.status == true) {
						alert('审核提交成功！');
						window.location.reload()
					}
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					console.log(XMLHttpRequest.status);
					console.log(XMLHttpRequest.readyState);
					console.log(textStatus);
					alert("系统繁忙,请稍后重试!");
				}
			})
		}
	};

	$('#audit_accept_form')[0].reset();
	$('#audit_accept_form').validate(auditdata).resetForm();

}


$(function() {
	/**
	 *    以下为文件上传的东东
	 */
	//'use strict';

	// Initialize the jQuery File Upload widget:
	$('#fileupload').fileupload({
		url: '/admin-manager/upload-file',
		dataType:'json',
		progressall: function(e, data) {
			layer_loading = layer.load(0);
		},
		add: function(e, data) {
			var uploadErrors = [];
			var acceptFileTypes = /(\.sheet|\/csv)$/i;
			console.log(data.originalFiles[0]);
			if (data.originalFiles[0]['type'].length && !acceptFileTypes.test(data.originalFiles[0]['type'])) {
				uploadErrors.push('Not an accepted file type');
			}
			if (data.originalFiles[0]['size'].length && data.originalFiles[0]['size'] > 5000000) {
				uploadErrors.push('Filesize is too big');
			}
			if (uploadErrors.length > 0) {
				alert(uploadErrors.join("\n"));
			} else {
				data.submit();
			}
		},
		done:function(e, data) {
			console.log(data.result);
			var account_list	= data.result.accountInfoList;
			var account_str		= '';
			for(i=0; i<account_list.length; i++)
			{
				account_str += account_list[i]['account_id'];
				account_str += ',';
			}
			console.log(account_str);
			$.each(data.result.files, function (index, file) {
				var priviewHtml = buildPreviewHtml(file);
				$('.files').append(priviewHtml);
			});
			$('#thadcreativessearch-account_id_list').val(account_str);
			submitSearchForm();
			layer.close(layer_loading);
		}
	});


	/**
	 *	Ajax提交搜索表单
	 */
	function submitSearchForm()
	{
		$.ajax({
			url	:	'/admin-manager/creatives-view',
			data:	$('#creatives-view-form').serialize(),
			type:	'post',
			success: function(data) {
				$.pjax.reload({container: $('#creatives-view-list'), data: $('form#creatives-view-form').serialize()});
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
	 * 生成preview 的html
	 * @param file
	 * @returns {string}
	 */
	function buildPreviewHtml(file)
	{
		var previewHtml = '<tr class="template-download fade in"><td><span class="preview"></span></td><td><p class="name"><span>'+ file.name +'</span></p></td><td><span class="size">'+ (file.size/1000).toFixed(2) +'k</span></td><td><button class="btn btn-warning cancel"><i class="glyphicon glyphicon-ban-circle"></i><span>Cancel</span></button></td></tr>';
		return previewHtml;
	}
});


