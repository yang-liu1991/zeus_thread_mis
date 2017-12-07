$(document).ready(function() {
	//初始化fileinput控件（第一次初始化）
	function initFileInput(ctrlName, uploadUrl) 
	{
		console.log(uploadUrl);
		var control = $('#' + ctrlName); 
		control.fileinput({
        	language: 'en', //设置语言
			dropZoneTitle:'Email address file',
        	uploadUrl: uploadUrl, //上传的地址
        	allowedFileExtensions : ['csv', 'xlsx'],//接收的文件后缀
        	showUpload: false, //是否显示上传按钮
        	showCaption: false,//是否显示标题
			browseClass: "btn btn-primary", //按钮样式
			previewFileIcon: "<i class='glyphicon glyphicon-eye-open'></i>", 
    	});
	}
	
	initFileInput('emailmodel-receiver_file', '/email-manager/upload-file');	

	/**
	 *	上传文件时
	 */
	$('#emailmodel-receiver_file').fileupload({
		url: '/email-manager/upload-file',
        dataType: 'json',
        done: function (e, data) {
			if(data.result.status == true)
			{
				$('#emailmodel-receiver').val(data.result.receiver_list);
			} else {
				console.log(data);
				alert('收件人邮箱上传失败，请重试!');	
			}
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css(
                'width',
                progress + '%'
            );
        }						
	});


	/**
	 *	初始化.summernote
	 */
	$('#summernote').summernote({
		height: 500,                 // set editor height
		minHeight: null,             // set minimum height of editor
		maxHeight: null,             // set maximum height of editor
		focus: true,
		/*
		 *	本来想着图片要上传到我们自己的服务器上，但是现在有些问题，回头再研究下
		 */
		callbacks: {
			onImageUpload: function(files, editor, $editable) {
				uploadImage(files);
			}
		}
	});
	
	/**
	 *	如果为更新的话，用html进行填充
	 */
	var emailmodel_scenario = $('#emailmodel-scenario').val();
	if(emailmodel_scenario == 'update')
	{
		var contentStr = $('#emailmodel-content').val();
		$('#summernote').summernote('code', contentStr);
	}


	/**
	 *	文件上传方法，这里上传到自己的服务器上
	 *	默认summernote上传的图片是以二进制方法存储的，这样太占空间了
	 */
	function uploadImage(files, editor, $editable) 
	{
		console.log(files);
        var data = new FormData();  
        data.append("ajaxTaskFile", files[0]);  
		console.log(data.get("ajaxTaskFile"));
        $.ajax({  
			data : data, 
            type : 'post',  
            url	: '/email-manager/upload-image', //图片上传出来的url，返回的是图片上传后的路径，http格式  
            cache : false,  
            async : false,
			contentType : false,  
            processData : false,  
            success: function(data) {//data是返回的hash,key之类的值，key是定义的文件名  
				console.log(data);
				if(data.status == true)
				{
					$('#summernote').summernote('insertImage', data.image_path);  
				}
			},  
            error:function(XMLHttpRequest, textStatus, errorThrown){ 
				console.log(XMLHttpRequest.status);
				console.log(XMLHttpRequest.readyState);
				console.log(textStatus);
            }  
        });  
    }  

	
	/**
	 *	验证表单数据
	 */
	function validateFormData()
	{
		var receiver	= $('#emailmodel-receiver').val();
		var subject		= $('#emailmodel-subject').val();
		var content		= $('#summernote').summernote('code');
		var receiver_file	= $('#emailmodel-receiver_file').val();

		if(!receiver && !receiver_file)
		{
			alert('收件人不能为空！');
			return;
		}

		if(!subject)
		{
			alert('邮件主题不能为空！');
			return;
		}

		if(!content)
		{
			alert('邮件内容不能为空！');
			return;
		} else {
			$('#emailmodel-content').val(content);
		}
	}


	/**
	 *	保存按钮
	 */
	$('#email-create-button').click(function() 
	{
		validateFormData();

		$.ajax({ 
			url:'/email-manager/create-email',// 跳转到 action
			data : $('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				console.log(data);
				if(data.status == true)
				{
					window.location.href='/email-manager/email-list';
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
	 *	更新按钮
	 */
	$('#email-update-button').click(function() 
	{
		validateFormData();
		var id = $('#mail-update-id').val();
		$.ajax({ 
			url:'/email-manager/update-email?id=' + id,// 跳转到 action
			data : $('form').serialize(),
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				console.log(data);
				if(data.status == true)
				{
					window.location.href='/email-manager/email-list';
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
})
