$(document).ready(function() {
	/**
	 *	以下JS为增加website所需
	 */

	//初始化fileinput控件（第一次初始化）
	function initFileInput(ctrlName, uploadUrl) 
	{
		console.log(uploadUrl);
		var control = $('#' + ctrlName); 
		control.fileinput({
        	language: 'zh', //设置语言
        	uploadUrl: uploadUrl, //上传的地址
        	allowedFileExtensions : ['jpg', 'png','gif'],//接收的文件后缀
        	showUpload: true, //是否显示上传按钮
        	showCaption: false,//是否显示标题
			browseClass: "btn btn-primary", //按钮样式
			previewFileIcon: "<i class='glyphicon glyphicon-king'></i>", 
    	});
	}

	initFileInput('entitymodel-business_registration', '/advertiser/upload-file');	

	/**
	 *	如果是更新操作，将英文地址按格式显示
	 */
	var entity_scenario = $('#entity_scenario').val();
	if(entity_scenario == "update")
	{
		var address_en	= $("#entitymodel-address_en").val();
		var is_smb		= $('#entitymodel-is_smb-value').val();
		var address_en_object = eval('(' + address_en + ')');
		var verticalId = $('#entitymodel-vertical').val();
		$("#entitymodel-full_name").val(address_en_object.full_name);
		$("#entitymodel-address_line_1").val(address_en_object.address_line_1);
		$("#entitymodel-address_line_2").val(address_en_object.address_line_2);
		$("#entitymodel-city").val(address_en_object.city);
		$("#entitymodel-state").val(address_en_object.state);
		$("#entitymodel-zip").val(address_en_object.zip);
		
		/* 若业务类型为gaming，则不需要设置is_smb */
		if(verticalId == 7)
		{
			$('.field-entitymodel-is_smb').css('display', 'none');
		} else {
			$('.field-entitymodel-is_smb').css('display', 'block');
			if(is_smb == 0)
			{
				$('input[name="EntityModel[is_smb]"][value=0]').attr('checked', 'checked');
			} else {
				$('input[name="EntityModel[is_smb]"][value=1]').attr('checked', 'checked');
			}
		}
		console.log(eval('(' + address_en + ')'));
		var business_registration_path = $('#entitymodel-business_registration_path').val();
		if(business_registration_path)
		{
			console.log(business_registration_path);
			$('#entitymodel-business_registration_path').parent().append('<img id="entitymodel-business_registration_path" class="img-thumbnail" src="'+ business_registration_path +'" style="width:200px;height:240px">');
		}
	}


	/**
	 *	当点击添加时，增加输入框
	 */
	$('#addwebsite').click(function() {
		var website = $('#entitymodel-promotable_url').val().trim();
		var promotable_urls = $("input[name='promotable_urls']").last().val();
		/* 这里获取id参数主要是为了判断新建和更新两种场景 */	
		console.log(promotable_urls);
		var scenario = $('#entity_scenario').val();
		if(promotable_urls == undefined ) 
			var promotable_urls = website;

		if(promotable_urls == "" || promotable_urls == null) 
		{
			alert('填写的URL有误，请检查后重试！');
			return;
		}

		/* 验证是否为合法的URL*/
		/*
		if(!IsURL(promotable_urls))
		{
			alert('非合法有效的URL，请检查后重试！');
			return;
		}
		*/

		if(scenario == 'create')
		{
			htmlStr = '<div><input id="entitymodel-promotable_url" type="text" name="promotable_urls" class="form-control" style="width:500px;margin-bottom:5px;display:inline">';
			htmlStr += '<button type="button" style="width:60px;margin-left:5px;" id="deletewebsite"> - </button>';
			htmlStr += '<input id="entitymodel-promotable_url" type="hidden" name="EntityModel[promotable_urls][]" value="' + promotable_urls + '"></div>';
			$('#entitymodel-promotable_url').parent().append(htmlStr);
		} else {
			htmlStr = '<div><button type="button" style="width:60px;float:right;margin-right:360px;margin-top:5px;" id="deletewebsite"> - </button>';
			htmlStr += '<input id="entitymodel-promotable_url" type="text" name="promotable_urls" class="form-control" style="width:500px;margin-bottom:5px;margin-left:195px;">';
			htmlStr += '<input id="entitymodel-promotable_url" type="hidden" name="EntityModel[promotable_urls][]" value="' + promotable_urls + '"></div>';
			$(htmlStr).insertBefore('#addwebsite');
		}
	})

	/**
	 *	删除操作
	 */
	$('#ad-entity-info-form').on('click', 'button[id=deletewebsite]', function(){
		if(!confirm('确定删除？')) return false;
		$(this).parent().remove();
	})

	/**
	 *	当选择广告主营业执照时，Ajax自动上传图片
	 */
	$('#entitymodel-business_registration').change(function() {
		$('img#entitymodel-business_registration_path').css('display', 'none');
	});

	$('#entitymodel-business_registration').fileupload({
		url: '/advertiser/upload-file',
        dataType: 'json',
        done: function (e, data) {
			if(data.result.status == true)
			{
				$('#entitymodel-business_registration_path').val(data.result.filePath);
				console.log($('#entitymodel-business_registration_path').val());
			} else {
				console.log(data);
				alert('营业执照上传失败，请重试!');	
			}
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css(
                'width',
                progress + '%'
            );
        }			
	})


	/**
	 *	提交操作
	 */
	$('#submit-button').click(function() {
		var website = $('#entitymodel-promotable_url').val().trim();
		var promotable_urls = $("input[name='promotable_urls']").last().val();
		var full_name	= $("#entitymodel-full_name").val().trim();
		var address_line_1 = $("#entitymodel-address_line_1").val().trim();
		var address_line_2 = $("#entitymodel-address_line_2").val().trim();
		var city	= $("#entitymodel-city").val().trim();
		var state	= $("#entitymodel-state").val().trim();
		var zip		= $("#entitymodel-zip").val().trim();
		var country	= $("#entitymodel-country").val().trim();
		if(full_name == undefined || full_name == ''|| address_line_1 == '' || address_line_1 == undefined || city == '' || city == undefined || state == '' || state == undefined || country == '' || country == undefined || zip == undefined || zip == '')
		{
			alert("请填写完整的英文地址！");
			return
		}
	
		var business_registration_path = $('#entitymodel-business_registration_path').val();
		if(business_registration_path == undefined || business_registration_path == '')
		{
			$('.field-entitymodel-business_registration').addClass('has-error');
			$('#entitymodel-business_registration').parent().append('<div class="help-block">公司营业执照不能为空！</div>');
			alert("请上传营业执照！");
			return
		}
		var business_registration_path = business_registration_path.split('/').pop(); 
		$('#entitymodel-business_registration_path').val(business_registration_path);

		/* 将英文地址拼接成JSONS字符串 */
		var address_en_object	= {"full_name":full_name, "address_line_1":address_line_1, "address_line_2":address_line_2, "city":city, "state":state, "zip":zip, "country":country}
		var jStr = JSON.stringify(address_en_object);
		jStr = jStr.replace("'", "\\'");
		console.log(jStr);
		$("#entitymodel-address_en").val(jStr);

		/* 如果有中文逗号，转化成英文*/
		var promotable_page_ids = $("#entitymodel-promotable_page_ids").val().trim();
		var promotable_page_urls = $("#entitymodel-promotable_page_urls").val().trim();
		var promotable_app_ids	= $("#entitymodel-promotable_app_ids").val().trim();
		$("#entitymodel-promotable_page_ids").val(changeComma(promotable_page_ids));
		$("#entitymodel-promotable_page_urls").val(changeComma(promotable_page_urls));
		$("#entitymodel-promotable_app_ids").val(changeComma(promotable_app_ids));
		$("#entitymodel-full_name").val(changeComma(full_name));
		if(promotable_urls == undefined) var promotable_urls = website;
		htmlStr = '<input type="hidden" name="EntityModel[promotable_urls][]" value="' + promotable_urls + '"></div>';
		$('#entitymodel-promotable_url').parent().append(htmlStr);
		$('#ad-entity-info-form').submit();
	})

	/**
	* 获取url中的参数
	*/
    function getUrlParam(name) 
	{
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
		var r = window.location.search.substr(1).match(reg);
		if (r != null) return unescape(r[2]); return null;
    }

	/**
	 *	中文逗号转英文
	 */
	function  changeComma(str)
	{
		str=str.replace(/，/ig,',');
		return str;
	}

	
	/**
	 *	验证URL有效性
	 */
	function IsURL(str_url)
	{
		var strRegex = "^((https|http|ftp|rtsp|mms)?://)"
			+ "?(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?" // ftp的user@
			+ "(([0-9]{1,3}\.){3}[0-9]{1,3}" // IP形式的URL- 199.194.52.184
			+ "|" // 允许IP和DOMAIN（域名）
			+ "([0-9a-z_!~*'()-]+\.)*" // 域名- www.
			+ "([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\." // 二级域名
			+ "[a-z]{2,6})" // first level domain- .com or .museum
			+ "(:[0-9]{1,4})?" // 端口- :80
			+ "((/?)|" // a slash isn't required if there is no file name
			+ "(/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+/?)$";
		var re=new RegExp(strRegex);
		if (re.test(str_url)){
			return (true);
		}else{
			return (false);
		}
	}


	/**
	 *	选择主业务线改变时
	 */
	$('#entitymodel-vertical').change(function() {
		var verticalId = $(this).val();
		/* 若业务类型为gaming，则不需要设置is_smb */
		var is_smb	= $('#entitymodel-is_smb-value').val();
		if(verticalId == 7)
		{
			$('.field-entitymodel-is_smb').css('display', 'none');
		} else {
			$('.field-entitymodel-is_smb').css('display', 'block');
			if(is_smb == 0)
			{
				$('input[name="EntityModel[is_smb]"][value=0]').attr('checked', 'checked');
			} else if(is_smb == 1) {
				$('input[name="EntityModel[is_smb]"][value=1]').attr('checked', 'checked');
			} else {
			}
		}
		if(verticalId >= 0) getSubvertical(verticalId);
	})
	
	/**
	 *	根据主业务线获取子业务线
	 */
	function getSubvertical(verticalId)
	{
		$.ajax({ 
			url:'/advertiser/get-subvertical',// 跳转到 action
			data:{  
				'verticalId'	: verticalId,
			},
			type:'post',
			success:function(data) {  
				$('#entitymodel-subvertical').find("option").remove();
				$('#entitymodel-subvertical').append(data);
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
		*/
	function getPreviewHtml(imageUrl)
	{
		previewHtml = '<div class="file-preview">' 
			+ '<div class="close fileinput-remove">×</div>'
			+ '<div class="file-drop-disabled">'
			+ '<div class="file-preview-thumbnails">'
			+ '<div class="file-preview-frame kv-preview-thumb" id="preview-1485075697242-0" data-fileindex="0" data-template="image">'
			+ '<div class="kv-file-content"><img src="'+ imageUrl +'" class="kv-preview-data file-preview-image" title="8071ddebly1fbs6wtlwv0j20hs0dct9j.png" alt="8071ddebly1fbs6wtlwv0j20hs0dct9j.png" style="width:auto;height:160px;">'
			+ '</div><div class="file-thumbnail-footer">'
			+ '<div class="file-footer-caption" title="8071ddebly1fbs6wtlwv0j20hs0dct9j.png">8071ddebly1fbs6wtlwv0j20hs0dct9j.png <br><samp>(263.15 KB)</samp></div>'
			+ '<div class="file-actions">'
			+ '<div class="file-footer-buttons">'
			+ '<button type="button" class="kv-file-zoom btn btn-xs btn-default" title="View Details"><i class="glyphicon glyphicon-zoom-in"></i></button>'
			+ '</div>'
			+ '<div class="file-upload-indicator" title="Not uploaded yet"><i class="glyphicon glyphicon-hand-down text-warning"></i></div>'
			+ '<div class="clearfix"></div>'
			+ '</div>'
			+ '</div>'
			+ '<div class="clearfix"></div>'
			+ '<div class="file-preview-status text-center text-success"></div>'
			+ '<div class="kv-fileinput-error file-error-message" style="display: none;"></div>'
			+ '</div>'
			+ '</div>';

		return previewHtml;	
	}
})
