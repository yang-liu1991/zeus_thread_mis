$(function() {
	/**
	 *	帐户申请提交操作
	 */
	$('#account-submit-button').click(function() {
		/* 这些全是针对实体信息修改的，与entity-form.js相同 */	
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

		/* 以下是针对开户信息的 */
		var referral	= $('#requestmodel-referral').val();
		if(!referral) 
		{
			alert('推荐人不能为空，且应为有效邮箱！');
			return;
		}

		var timezone_id	= $('#requestmodel-timezone_id').val();
		if(timezone_id == '')
		{
			alert('时区不能为空！');
			return;
		}

		if($('.has-error').text() != '') return;

		var entity_id	= $('#requestmodel-fbaccount_entity_id').val();
		console.log(entity_id);
		$.ajax({ 
			url:'/advertiser/account-apply-add?entity-id=' + entity_id,
			data : $('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				if(data.status)
				{
					window.location.href = '/advertiser/account-list';
				} else {
					showReasons(data.message);	
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
	 *	当输入推荐人字段时，进行ajax验证
	 */
	$('#requestmodel-referral').change(function() {
		var referral = $('#requestmodel-referral').val();
		$.ajax({ 
			url:'/advertiser/validate-referral',
			data : {'referral' : referral},
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				console.log(data);
				if(!data)
				{
					if($('#requestmodel-referral').nextAll('.help-block').length) return;
					$('.field-requestmodel-referral').addClass('has-error');
					$('#requestmodel-referral').parent().append('<div class="help-block">无效的推荐人！</div>');
				} else {
					$('.field-requestmodel-referral').removeClass('has-error');
					$('#requestmodel-referral').next('.help-block').remove();
					$('.field-requestmodel-referral').addClass('has-success');
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
	 *	增加帐户申请操作
	 */
	initCount = 0;
	$('#account-add-button').click(function() {
		initCount += 1;
		var jsObj		= $('#ad-account-info').clone();
		$(jsObj).find('select[name="RequestModel[0][timezone_id]"]').attr("name", "RequestModel["+ initCount +"][timezone_id]")
		$(jsObj).find('input[name="RequestModel[0][number]"]').attr("name", "RequestModel["+ initCount +"][number]");
		$(jsObj).find('input[name="RequestModel[0][referral]"]').attr("name", "RequestModel["+ initCount +"][referral]");
		$(jsObj).find('.field-requestmodel-referral').remove();
		$(jsObj).find('select[name="RequestModel['+ initCount +'][timezone_id]"]').val("");
		$(jsObj).find('input[name="RequestModel['+ initCount +'][number]"]').val(1);
		$(jsObj).find("hr").remove();
		$(jsObj).attr("id", "");
		console.log(jsObj);
		$('#ad-account-info-form').append(jsObj);
		$('#ad-account-info-form').append('<button type="button" id="account-del-button" class="btn btn-danger btn-xs" style="margin-left:9%;" name="submit-button">删除</button>');
		$('#ad-account-info-form').append('<hr/ style="width:85%;">');
	})

	/**
	 *	删除帐户申请操作
	 */
	$('#ad-account-info-form').on('click', 'button[id=account-del-button]', function() {
		if(confirm('确定要删除吗？'))
		{
			$(this).prev("div").remove();
			$(this).next("hr").remove();
			$(this).remove();
		}
	})
	
	/**
	 *	开户失败的提示弹窗
	 */
	function showReasons(reasons)
	{
		console.log(reasons);
		reasonsStr	= reasons.replace(']', '').replace('[', '');
		reasonsJson	= eval('(' + reasonsStr + ')');;
		console.log(reasonsJson);
		var config = {};
		config.area = [ '500px', '300px' ];
		config.type = 1;
		config.title = '错误信息';
		config.skin	= 'layui-layer-rim';
		layer.open(config);
		$('.layui-layer-content').JSONView(reasonsJson);
	}


	
	/**
	 *	中文逗号转英文
	 */
	function  changeComma(str)
	{
		str=str.replace(/，/ig,',');
		return str;
	}
})


