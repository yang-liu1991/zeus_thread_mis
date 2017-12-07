//var current_video_folder_id = 0;
pathManager = function() {
	var $this = pathManager;

	$this.currentFolderId = 0;
	$this.currentFolderName = $.fn.d3_t('Video');
	$this.parentFolderId = $this.currentFolderId;
	$this.parentFolderName = $this.currentFolderName;
	$this.folderPath = [{name: $this.currentFolderName, id: 0}];

	$this.buildPath = function (currentFolderId, currentFolderName, parentId) {
		/* 根据currentFolder截取path生成新path */
		console.log($this.folderPath);
		var popNum = 0;
		for(var index = $this.folderPath.length - 1; index >= 0; index--) {
			if ($this.folderPath[index].id == currentFolderId) {
				if (index == 0) {
					parentFolderId = currentFolderId;
					parentFolderName = currentFolderName;
				} else {
					parentFolderId = $this.folderPath[index - 1].id;
					parentFolderName = $this.folderPath[index - 1].name;
				}   
				break;
			}   
			if ($this.folderPath[index].id == parentId) {
				parentFolderId = $this.folderPath[index].id;
				parentFolderName = $this.folderPath[index].name;
				$this.folderPath[index+1] = { 
					id: currentFolderId,
					name: currentFolderName,
				};  
				popNum--;
				break;
			}   
			popNum++;
		}   
		for(var i = 0; i < popNum; i++) {
			$this.folderPath.pop();
		}   
		console.log($this.folderPath);

		var html = [];
		html.push(
			'<li>',
				'<a href="/site/" title="">',
					$.fn.d3_t('Home')+' /',
				'</a>',
			'</li>',
			'<li>',
				'<span >',
					$.fn.d3_t('Library')+' /',
				'</span>',
			'</li>'
		);

		$.each($this.folderPath, function(){
			html.push(
				'<li>',
					'<a href="javascript:void(0)" title="" class="path_redirect" redirect="'+this.id+'" redirect_name="'+this.name+'">',
						this.name+' /',
					'</a>',
				'</li>'
			);
		});
		$('.folder-nav').find('ul').empty().append(html.join('\n'));

	}   
}
pathManager();



$(window).resize(function  () {
	resMargin ();
});
//len : ms, int
function format_video_length(len) {
	if (!len) {
		return 'N/A';
	}
	len = parseInt(len) / 1000;
	var h = (parseInt(len / 3600)).toString();
	var m = (parseInt(len / 60) % 60).toString();
	//var s = (len % 60).toFixed(2).toString();
	var s = parseInt(len % 60).toString();
	h = h.length < 2 ? "0" + h : h;
	//h = h.length < 2 ? ("00" + h).substr(-h.length) : h;
	m = m.length < 2 ? "0" + m : m;
	s = s.length < 2 ? "0" + s : s;
	return h + ':' + m + ':' + s;
}

function loadVideoFolder (folderId, folderName, parentId) {
	$('.manage-img-cont .imginfos').empty();
	//current_video_folder_id = target ? target : 0;
	$.get(
		'/userlib/open-lib-folder',
		{
			type:'video',
			target : folderId,
			fbUserId : $('#selectAccount').val().split('-')[0],
			fbAccountId : $('#selectAccount').val().split('-')[1],
		},
		function(data){
			if (typeof data != 'object') {
				data=JSON.parse(data);
			};
			$('#access_token').val(data.access_token);
			pathManager.currentFolderId = folderId;
			pathManager.currentFolderName = folderName;
			pathManager.buildPath(folderId, folderName, parentId);

			var libs=data.libs,liDom="",folderDom="",folders=data.folders;
			if (libs.length==0&&folders.length==0) {
				$('.manage-img-cont .imginfos').append('无数据！').css('padding-top', '0');
			}else{
				for (var i = 0; i < libs.length; i++) {
					liDom += '<li class="clearfix" video_url="'+libs[i].source+'" video_id="'+libs[i].id+'" picture="'+libs[i].picture+'" video_name="'+libs[i].name+'">'+
							'<a href="###" class="btn"></a>'+
							'<div class="img-info clearfix"><div class="imgshow" style="background-image:url('+libs[i].picture+');"></div>'+
							'<p class="imgtips">ID:'+libs[i].id+'</p></div>'+
							'<div class="details"><p class="tip-contents">'+
							'<label style="float:left">'+$.fn.d3_t('Length: {len} ',{len: format_video_length(libs[i].length)})+'</label>'+
							'<a href="###" class="viewMenu"><span>View</span>'+
							'</a></p></div></li>';
				};
				for (var i = 0; i < folders.length; i++) {
							folderDom +='<li class="clearfix img-folder" folder_id="'+folders[i].id+'" video_id="'+folders[i].id+'" parent_id="'+folders[i].parent_id+'" create_time="'+folders[i].create_time+'" update_time="'+folders[i].create_time+'">'+
							'<a href="###" class="btn"></a>'+
							'<div class="img-info img-folder clearfix"><div class="imgshow">'+
							'<span class="folder-click-area"></span>'+
							'<span class="folder-name" contenteditable>'+folders[i].name+'</span></div></li>';
				}
				$('.manage-img-cont .imginfos').prepend(folderDom);
				$('.manage-img-cont .imginfos').append(liDom);
				resMargin();
		//		getExcellImgStatus();
			}
		}
	);
}

function resMargin () {
	var imginfos=$('.imginfos:visible'),imginfosli=$('.imginfos:visible li');
	if (imginfos.width()<=imginfosli.width()*4) {
		imginfos.css('min-width', imginfosli.width()*4.1+'px');
		$('body').css('min-width', imginfosli.width()*4.1+'px');
	}else{
		$('body,.excell-img-cont .imginfos').width('auto');
		var ml=imginfos.width()-imginfosli.width()*4.1;
		for (var i = 1; i < imginfosli.length; i++) {
			imginfosli.eq(i*4-4).css({
				'margin-right': Math.floor(ml/3)+'px'
			});
			imginfosli.eq(i*4-3).css({
				'margin-right': Math.floor(ml/3)+'px'
			});
			imginfosli.eq(i*4-2).css({
				'margin-right': Math.floor(ml/3)+'px'
			});
			imginfosli.eq(i*4-1).css({
				'margin-right': '0px'
			});
		}
	}
}
function stopScroll (obj) {
	$('body').css({
		position: 'fixed',
		width: '100%'
	});
	if (obj.find('.modal-content').height()>700) {
		obj.css('overflow', 'auto');
	};
	$('.checkTag-modal').scroll(function  () {
		if ($('.editing-cont').length && $('.checkTag-modal').scrollTop()>=400) {
			$('.saved-fixed').show().find('a').text('save');
		}else{
			$('.saved-fixed').hide();
		}
	});
	$('.modal .btn-close').on('click', function() {
		$('body').removeAttr('style');
		$('.modal-content .tagName-typein').removeAttr('disabled');
		$('.modal-content').removeClass('editing-cont');
	});
}
$(document.body).on('click','.btn-addTag', function() {
}).on('click','.details .editTag', function() {
}).on('click','.details .checkTag',function() {
}).on('click','.details .viewMenu',function() {
	stopScroll($('.checkTag-modal'));
	var video_url = $(this).parents('li').attr('video_url');
	var poster = $(this).parents('li').attr('picture');
	var name = $(this).parents('li').attr('video_name') ? $(this).parents('li').attr('video_name') : 'Video';
	$('.viewMenu-modal').show().find('.viewMenu-img').attr('src', video_url).attr('poster', poster);
	$('.viewMenu-modal').find('h3').text(name);
	$('.viewMenu-modal').find('#btn_dnload').attr('href', video_url);
}).on('click', '.imginfos:visible li .img-info', function(event) {
	$(this).parents('li').toggleClass('choosed');
}).on('click','.modal .btn-close',function  () {
	$(this).closest('.modal').modalHide();
}).on('click','.img-folder-edit .btn-success', function() {
	var foldername = $(this).siblings('.folder-name-edit').val();
	var parentli = $(this).parents('li');
	var retdata;
	var currentFolder = $('#currentFolder').val();
	if (foldername == '') {
		alert($.fn.d3_t('name is necessary for image folder.'));
		return false;
	}
	var tmp = $('#selectAccount').val().split('-');
	var fbUserId = tmp[0];
	var fbAccountId = tmp[1];
	var data = {name: foldername, parentId: pathManager.currentFolderId, _csrf: $('#_csrf').val()};
	$.ajax({
		type: 'POST',
		url: '/userlib/create-lib-folder?type=video&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
		data: data,
		success: function(ret) {
			ret = JSON.parse(ret);
			retdata=ret;
			if (ret.error != '') {
				alert($.fn.d3_t('create image folder failed.'));
				return;
			}else{
				parentli.attr({
					'folder_id': retdata.folder.id,
					'parent_id': retdata.folder.parent_id,
					'create_time': retdata.folder.create_time,
					'update_time': retdata.folder.update_time
				});
				$('.img-folder-edit').removeClass('img-folder-edit');
				$('.folder-name-edit').siblings().remove();
				$('input.folder-name-edit').replaceWith('<span class="folder-click-area"></span><span class="folder-name">'+foldername+'</span>');
			}
		},
		error: function(){
			alert($.fn.d3_t('internet error.'));
		}
	});
}).on('click','.img-folder-edit .btn-cancel',function() {
	$(this).parents('li').remove();
	resMargin();
}).on('click','.adTxt-btn .btn-del',function  () {
	if ($('.imginfos li.choosed').length == 0) {
		$('.delete-modal').hide();
		alert($.fn.d3_t('you must select at least one video.'));
		return;
	}else{
		$('.delete-modal').modal();
	}
}).on('click', '.addfolder-btn', function(event) {
	if ($('.img-folder-edit').length == 0) {
		if ($('.manage-img-cont .imginfos li').length==0) {
			$('.manage-img-cont .imginfos').text('');
		}
		$('.manage-img-cont .imginfos').prepend('<li class="clearfix img-folder">'+
			'<a href="###" class="btn"></a><div class="img-info img-folder-edit clearfix">'+
			'<div class="imgshow"><input class="col-md-10 col-md-offset-1 folder-name-edit input-default">'+
			'<a href="###" class="btn btn-green btn-success btn-sm">save</a>'+
			'<a href="###" class="btn btn-cancel btn-blue btn-sm">cancel</a></div></div></li>');
		resMargin();
	}
}).on('click', '.delete-modal .btn-delete', function(event) {
	var data = {folders: [], libs: [], _csrf: $('#_csrf').val()};
	$('.imginfos li.choosed.img-folder').each(function(){
		data.folders.push($(this).attr('folder_id'));
	});
	$('.imginfos li.choosed').not('.img-folder').each(function(){
		data.libs.push($(this).attr('video_id'));
	});
	delete_folder_lib(data);
}).on('click','.btn-move',function() {
	if ($('.imginfos li.choosed').length == 0) {
		alert($.fn.d3_t('please select at least one image or folder.'));
		return;
	} else {
		$('.move-modal').modal();
		get_folder_tree();
	}
}).on('change', '#selectAccount', function(){
	loadVideoFolder (pathManager.currentFolderId, pathManager.currentFolderName, pathManager.parentFolderId);
}).on('click', '#load_fb_videos', function() {
	var fbUserId = $('#selectAccount').val().split('-')[0];
	var fbAccountId = $('#selectAccount').val().split('-')[1];
	$.get(
		'/userlib/load-videos?fbUserId='+fbUserId+'&fbAccountId='+fbAccountId, 
		function(msg) {
			alert(msg);
			loadVideoFolder (pathManager.currentFolderId, pathManager.currentFolderName, pathManager.parentFolderId);
	});
}).on('click', '.path_redirect', function(){
	loadVideoFolder(parseInt($(this).attr('redirect')), $(this).attr('redirect_name'));
}).on('dblclick', '.folder-click-area', function() {
	loadVideoFolder($(this).parents('li').attr('folder_id'), $(this).parents('li').find('.folder-name').text(), pathManager.currentFolderId);
	//current_video_folder_id=$(this).parents('li').attr('folder_id');
}).on('click', '.move-modal .btn-create', function(){
	//var currentFolder = $('#currentFolder').val();
	var target=$('.jstree-clicked').parents('li').attr('id');
	if (!target.length) {
		return;
	}
	var folders = [],libs = [];
	$('.manage-img-cont .imginfos li.img-folder.choosed').each(function(i){
		folders.push(parseInt($(this).attr('folder_id')));
	});
	$('.manage-img-cont .imginfos li.choosed').not('.img-folder').each(function(i){
		libs.push(parseInt($(this).attr('video_id')));
	});
	moveLibsAndFolders(folders, libs, target);
}).on('mouseenter', '.folder-name', function(event) {
	$(this).attr('contenteditable', '');
	$(this).css({
		'border': '1px solid #acb2ba',
		'border-radius': '3px'
	});
	folderName=$(this).text(),
	ch_folderId=$(this).parents('li').attr('folder_id');
	folder_clicked=true;

}).on('mouseleave', '.folder-name', function(event) {
	$(this).removeAttr('contenteditable');
	$(this).css('border', 'none');
	var txt=$(this).text();
	if (folder_clicked && txt!=folderName) {
		change_folder_name (txt,ch_folderId);
	}
	folder_clicked=false;
}).on('keydown', '.folder-name', function(event) {
	if(event.keyCode ==13){
		return false;
	}

}).on('change', '#xs_video_input', function(){
	var files = document.getElementById('xs_video_input').files;
	var inMainlandChina = (REMOTE_IP_INFO && REMOTE_IP_INFO.country == '中国' && REMOTE_IP_INFO.province != '香港' && REMOTE_IP_INFO.province != '台湾' && REMOTE_IP_INFO.province != '澳门');
	if (files[0].size < 300 * 1000 * 1024 && inMainlandChina) {
		$('#xs_video_input').attr('name', 'file');
		$('#xs_video_token').attr('name', 'token');
		_upload_video_qiniu();
	} else {
		$('xs_video_input').attr('name', 'source');
		$('#xs_video_token').attr('name', 'access_token').val($('#access_token').val());
		_upload_video_fb();
	}
});





var folder_clicked=false,folderName,ch_folderId,folder_clicked;
function change_folder_name (name,id) {
	$.post('/userlib/update-group-name?id='+id,{
		name:name
	},function(ret) {
		if (ret.data) {
			// alert('修改成功！');
		}else{
			alert(ret.error);
		}
	});
}
function delete_folder_lib (data) {
	var tmp = $('#selectAccount').val().split('-');
	var fbAccountId = tmp[1];
	var fbUserId = tmp[0];
	$.ajax({
		type: 'POST',
		url: '/userlib/delete-lib?type=video&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
		data: data,
		success: function(ret) {
			ret = JSON.parse(ret);
			if (ret.error != '') {
				alert($.fn.d3_t('delete videos failed.'));
				return;
			}
			loadVideoFolder (pathManager.currentFolderId, pathManager.currentFolderNmae, pathManager.parentFolderId);
		},
		error: function() {
			alert($.fn.d3_t('internet error.'))
		}
	});
}
function get_folder_tree() {
	var tmp = $('#selectAccount').val().split('-');
	var fbUserId = tmp[0];
	var fbAccountId = tmp[1];
	$.ajax({
		type: 'GET',
		url: '/userlib/get-folder-tree?type=video&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
		data: {},
		success: function(ret){
			oret = JSON.parse(ret);
			$('#folder_js_tree_container').jstree('destroy');
			$('#folder_js_tree_container').jstree({
				core: {
					data: oret.folders,
					multiple: false,
				},
			});
		},
		error: function(){
			alert('failed');
		}
	});
}

var _upload_xhr = null;
var REMOTE_IP_INFO = {};
function upload_video(tar) {
	if ($('.upload_btn').hasClass('ready')) {
		$('#xs_video_input').val('');
		$('#xs_video_input').click();
	} else {
		alert("please wait for the current video uploading.");
	}
}

function cancel_upload() {
	if (_upload_xhr) {
		_upload_xhr.abort();	
		$('.uploading').remove();
		$('.upload_btn').addClass('ready');
		resMargin();
	}
}

function show_uploading(xhr, type) {
	$('.uploading').remove();
	var html =[ 
		'<li class="clearfix uploading" video_url="" video_id="" picture="" style="margin-right: 173px;">',
			'<a href="###" class="btn"></a>',
			'<div class="clearfix">',
				'<div class="imgshow" style="background-color:grey;width:250px">',
					'<a class="btn cancel-upload" href="javascript:cancel_upload()"><i class="icon-close"></i></a>',
					'<div class="video-progress progress-info" style="padding-top: 34%;padding-left: 18%;"></div>',
				'</div>',
				'<p class="imgtips">UPLOADING</p>',
			'</div>',
			'<div class="video-progress progress-details">',
				'<p class="tip-contents" style="padding-right: 0px;">',
				'</p>',
			'</div>',
		'</li>'
	];
	$('ul.imginfos').prepend(html.join('\n'));
	resMargin();
	if(xhr && xhr.upload && xhr.upload.addEventListener){
		//$('.video-progress').html('<span>Step 1/2 Uploading</span><progress value="1" max="100"></progress><span class="pro-val">1%</span>');
		if (type == 'facebook') {
			$('.video-progress.progress-info').empty().append('<span>Step 1/2 Uploading</span><span class="pro-val">1%</span>');
		} else {
			$('.video-progress.progress-info').empty().append('<span>Step 1/3 Uploading</span><span class="pro-val">1%</span>');
		}
		$('.video-progress.progress-details p').empty().prepend('<progress value="1" max="100" style="width: 100%;float: left;height: 34px;"></progress>');
		$('.video-progress').show();
		xhr.upload.addEventListener("progress", function(e) {
			var percent = e.loaded / e.total;
			var show = Math.ceil((Math.sqrt(2 * percent - percent * percent) / 3 + percent *2 / 3) * 100);
			//console.log(percent); 
			$('.video-progress progress').attr('value', show);
			$('.video-progress .pro-val').text(show + '%');
		}, false);
	}
}

function get_upload_token(filename) {
	_upload_xhr = $.ajax({
		type: 'get',
		url: '/api/get-upload-token',
		success: function(ret){
			ret = JSON.parse(ret);
		},
		error: function() {
			alert('some error occurs, please retry or contact us')
		}
	});
}

function _upload_video_fb(){
	var files = document.getElementById('xs_video_input').files;				   
	var fileNum = files.length;
	var imageMode = /^[\s\S]+(.MP4|.MOV|.jpg|.mp4|.mov)$/;						 
	if(!imageMode.test(files[0].name)){											
		alert('The video must be mp4 or mov');									 
		return;
	}																			  
	$('#xs_video_name').val(files[0].name);
	
	var form = document.getElementById('xs_video_upload');						 
	var action = 'https://graph-video.facebook.com/v2.5/act_' + $('#selectAccount').val().split('-')[1] + '/advideos';
	var xhr = new XMLHttpRequest();												
	_upload_xhr = xhr;
	$('.upload_btn').removeClass('ready')
	show_uploading(xhr, 'facebook');
	xhr.onreadystatechange = function(){										   
		if(xhr.readyState == 4){
			if(xhr.status == 200){
				//$('.video-progress.progress-info').empty().append('<span>Step 2/2 Decoding</span>');	   
				try{
					$('.video-progress.progress-info').empty().append('<span>Step 2/2 Facebook decoding</span>');
					uploadVideoEnd(JSON.parse(xhr.responseText));  
					console.log(JSON.parse(xhr.responseText));					 
				} catch(e){
					$('.video-progress').hide();
					alert('Data error:' + xhr.responseText);
					$('.upload_btn').addClass('ready')
					loadVideoFolder (pathManager.currentFolderId, pathManager.currentFolderNmae, pathManager.parentFolderId);
				}
			} else if (xhr.status == 0) {
				console.log('cancel upload');
				alert($.fn.d3_t('The upload has been canceled'))
			} else {
				console.log(xhr);
				$('.video-progress').hide();
				alert('Upload video failed, please retry.');					   
				$('#xs_video_input').val('')
				$('.upload_btn').addClass('ready')
				loadVideoFolder (pathManager.currentFolderId, pathManager.currentFolderNmae, pathManager.parentFolderId);
			}																	  
		}																		  
	}
	xhr.open('POST',action);
	xhr.send(new FormData(form));
}

function _upload_video_qiniu(){
	var files = document.getElementById('xs_video_input').files;				   
	var fileNum = files.length;
	var imageMode = /^[\s\S]+(.MP4|.MOV|.jpg|.mp4|.mov)$/;						 
	if(!imageMode.test(files[0].name)){											
		alert('The video must be mp4 or mov');									 
		return;
	}																			  
	//$('#xs_video_name').val(files[0].name);
	var filename = files[0].name;
	console.log(filename);

	_upload_xhr = $.ajax({
		type: 'get',
		url: '/api/get-upload-token?filename='+filename,
		success: function(ret){
			ret = JSON.parse(ret);
			$('#xs_video_token').val(ret.token);
	
			var form = document.getElementById('xs_video_upload');						 
			//var action = 'https://graph-video.facebook.com/v2.5/act_' + $('#selectAccount').val().split('-')[1] + '/advideos';
			var action = 'http://up.qiniu.com';
			var xhr = new XMLHttpRequest();												
			_upload_xhr = xhr;
			$('.upload_btn').removeClass('ready')
			show_uploading(xhr, 'qiniu');
			xhr.onreadystatechange = function(){										   
				if(xhr.readyState == 4){
					if(xhr.status == 200){
						try{
							//uploadVideoEnd(JSON.parse(xhr.responseText));  
							FB_VIDEO_RETRY = 5;
							create_facebook_video(JSON.parse(xhr.responseText), filename);
							console.log(JSON.parse(xhr.responseText));					 
						} catch(e){
							$('.video-progress').hide();
							alert('Data error:' + xhr.responseText);
							$('.upload_btn').addClass('ready')
							loadVideoFolder (pathManager.currentFolderId, pathManager.currentFolderNmae, pathManager.parentFolderId);
						}
					} else if (xhr.status == 0) {
						console.log('cancel upload');
						alert($.fn.d3_t('The upload has been canceled.'))
					} else {
						console.log(xhr);
						$('.video-progress').hide();
						alert('Upload video failed, please retry.');					   
						$('#xs_video_input').val('')
						$('.upload_btn').addClass('ready')
						loadVideoFolder (pathManager.currentFolderId, pathManager.currentFolderNmae, pathManager.parentFolderId);
					}																	  
				}																		  
			}
			xhr.open('POST',action);
			xhr.send(new FormData(form));

		},
		error : function() {
			alert('error')
		}
	});
}

var FB_VIDEO_RETRY = 5;
function create_facebook_video(data, filename) {
	if (!data) {
		console.log('data is null');
		return;	
	}
	$('.video-progress.progress-info').empty().append('<span>Step 2/3</span><br><span> Create advideo on facebook</span>');
	tmp = $('#selectAccount').val().split('-');
	fbUserId = tmp[0];
	fbAccountId = tmp[1];
	_upload_xhr = $.ajax({
		type: 'get',
		url: '/api/upload-video-with-key',
		//长一点，20分钟
		timeout: 1200000,
		data: {
			key: data.key,
			filename: filename,
			fbUserId: fbUserId,
			fbAccountId: fbAccountId,
		},
		success: function (ret) {
			console.log(ret);
			ret = JSON.parse(ret);
			if (!ret || ret.error != '') {
				alert('failed, errr occurs');
				cancel_upload();
				return;
			}
			$('.video-progress.progress-info').empty().append('<span>Step 3/3 Facebook decoding</span>');
			uploadVideoEnd(ret);
		},
		error : function() {
			//alert('error occurs');
			console.log('failed and retry.');
			if (FB_VIDEO_RETRY > 0 &&  _upload_xhr.status != 0) {
				FB_VIDEO_RETRY -= 1;
				create_facebook_video(data, filename);
			} else {
				alert('failed, errr occurs');
				cancel_upload();
			}
		}
	});
}

var FB_DECOD_RETRY = 3;
function uploadVideoEnd(obj){
	_upload_xhr = $.get(
		'/userlib/get-video-info',
		{
			FbVideoId : obj.id,
			fbUserId : $('#selectAccount').val().split('-')[0],
			fbAccountId : $('#selectAccount').val().split('-')[1],
			folder: pathManager.currentFolderId,
		},
		function(ret){
			if  (ret.errors) {
				alert(ret.errors);
			}
			loadVideoFolder (pathManager.currentFolderId, pathManager.currentFolderNmae, pathManager.parentFolderId);
			$('.upload_btn').addClass('ready')
			//getCurentoObj().append_video(ret, 'block');
			//$('.xs_img_area:visible .xs_upload_image').prevAll().remove();
			//getCurentoObj().showThumb(ret.thumbnails);
			//$('.video-progress').hide();
			}
		);
}
function moveLibsAndFolders(folders, libs,target) {
	var tmp = $('#selectAccount').val().split('-');
	var fbUserId = tmp[0];
	var fbAccountId = tmp[1];

	var data = {folders: folders, libs: libs, target:target, _csrf: $('#_csrf').val()};

	$.ajax({
		type: 'POST',
		url: '/userlib/move-libs?type=video&fbAccountId=' + fbAccountId,
		data: data,
		success: function(ret) {
			ret = JSON.parse(ret);
			if (ret.error != '') {
				alert(ret.error);
			}else{
				loadVideoFolder (pathManager.currentFolderId, pathManager.currentFolderNmae, pathManager.parentFolderId);
			}
		},
		error: function() {
			alert($.fn.d3_t('request failed please retry.'));
			return;
		}
	});
}
// function trigger_click () {
// 	$('.manage-img-tit').addClass('active').siblings('li').removeClass('active');
// }
/*************************************/
	jQuery(document).ready(function($) {
		//from script whitch src = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=jejs"
		REMOTE_IP_INFO = remote_ip_info || {};
		$("img.lazy").lazyload({
			threshold : 100
		});
		loadVideoFolder(0);
	});

