var fb_promotion_id,imge_id,current_folder_id = 0;
renderSelect();
$('.saved-fixed').hide();
var dataStore,imgStore,tagNameVal="",flag;
$('.excell-form-tit form').find('#account-name,#country-name,#datepicker').change(function() {
	getImage();
	imageModal();
});
$('.img-classify-titName').on('click', function() {
	$(this).each(function(index, el) {
		$(this).addClass('active').siblings('li').removeClass('active');
		$('.img-classify-content').eq($(this).index()).removeClass('hidden').siblings('.img-classify-content').addClass('hidden');
		resMargin ();
	});
}).eq(0).trigger('click');
var clicked=false;
$('.manage-img-tit').on('click', function() {
	if (!clicked) {
		loadImageFolder (0);
	}
	clicked=true;
});
$(window).resize(function  () {
	resMargin ();
});
function imageModal() {
	$('.body-item-data thead').empty();
	$('.body-item-data tbody').empty();
	$.get('/mark/get-rank-list', {
		fb_promotion_id : $('#account-name').val(),
		days : $('#datepicker').val(),
	},function(data) {
		var modalData=data.data,thDom="",trDom="";
		if (modalData.length>0) {
			for (var k = 0; k < modalData[0].length; k++) {
				thDom+= '<th>'+modalData[0][k]+'</th>';
			};
			for (var i = 1; i < modalData.length; i++) {
				var tdDom="";
				for (var j = 0; j < modalData[i].length; j++) {
					tdDom+= '<td>'+modalData[i][j]+'</td>';
				};
				trDom+='<tr>'+tdDom+'</tr>';
			};
			$('.body-item-data thead').append('<tr>'+thDom+'</tr>');
		}else{
			trDom='无数据！';
		}
		$('.body-item-data tbody').append(trDom);
	});
}
function getImage () {
	$('.excell-img-cont .imginfos').empty();
	$.get('/mark/get-rank-image',{
		fb_promotion_id : $('#account-name').val(),
		days : $('#datepicker').val(),
		location_targeting : $('#country-name option:checked').val()
	}, function(data) {
		var liDom="",imgStore = data.data;
		if (imgStore.length==0) {
			liDom="无数据！";
		}else{
			for (var i = 0; i < imgStore.length; i++) {
			var flag= imgStore[i].marked ===0 ? 'grayflag':'redflag';
			liDom += '<li class="clearfix"  data-wh="'+imgStore[i].origin_width+"*"+imgStore[i].origin_height+'" data-id="'+imgStore[i].id+'" image_url="'+imgStore[i].image_url+'">'+
					'<div class="img-info clearfix"><div class="imgshow" style="background-image:url('+imgStore[i].mini_image+')"></div>'+
					'<p class="imgtips">ID:'+imgStore[i].id+'</p></div>'+
					'<div class="details '+flag+'"><p class="tip-contents">'+
					'<a href="###" class="viewMenu"><span>View</span>'+
					'</a></p></div></li>';
			};
		}
		$('.excell-img-cont .imginfos').append(liDom);
		resMargin();
	});
}
function postTag () {
	$.post(
		'/mark/tag',
		{
			text : $('#addTag-items span:last').text(),
			fb_promotion_id : fb_promotion_id,
		},
		function(data){
			var data=data.data;
			$('#addTag-items span:last').attr('data-val', data);
	});
}
// 下拉框数据填充
function renderSelect () {
	$.ajax({
		url: '/mark/get-promotion-list',
		type: 'GET',
		dataType: 'json',
		data: {},
		success:function  (data) {
			if (data.status.error) {
				alert('error');
			};
			var dataSelect=data.data;
			for (prop in dataSelect) {
				$('#account-name').append('<option value="'+prop+'">'+dataSelect[prop]+'</option>');
			}
			getImage();
			imageModal();
		}
	});
}
// 保存用户标签
function savedUserTags (obj) {
	var text="", usermakeTag="", dataObj={};
	$('tbody label input[type="radio"]:checked').each(function(index, el) {
		var key=$(this).attr('name');
		var val=$(this).val();
		dataObj[key]=val;
	});
	$('#addTag-items span.btn').each(function(index, el) {
		var key1=$(this).attr('data-val');
		var val1=1;
		if (val1&&key1) {
			dataObj[key1]=val1;
		}
	});
	$.ajax({
		url: '/mark/image-tag',
		type: 'POST',
		dataType: 'json',
		data: {
			fb_promotion_id : fb_promotion_id,
			id : imge_id,//图片id
			data : dataObj
		},
		success: function(getdata) {
			if (getdata.data==1) {
				alert('保存成功！');
			}else{
				alert(getdata.status.error);
			}
		},
		error: function() {
			alert($.fn.d3_t('request failed please retry.'));
			return;
		}
	});
}
// 获取优秀图片标签信息
function getExcellImgTagInfo(obj,objId,objfbp) {
	$('.checkTag-table-info tbody').empty();
	$('.system-tags').find('span').remove();
	$('.addTag-items').empty();
	$('.tag-item-name').remove();
	$.get('/mark/get-image',{
			id : objId ? objId : "",
			fb_promotion_id : fb_promotion_id
		},function(data) {
			var feature = data.data["feature"],name = data.data.name,
				value = data.data.value,layoutDom = "",tagsDom ="",tagsDef="",
				tags = data.data.tags,tagsStore=[],namecont="",inputItem="";
			if (data.status.error) {
				alert(data.status.error);
			}else{
				for (prop in feature) {
					var inputItem="";
					if (feature.hasOwnProperty(prop)) {
						var feaItem=feature[prop];
						for (var i = 0; i < feaItem.length; i++) {
							name[feaItem[i]] = name[feaItem[i]]==="" ? "--" : name[feaItem[i]];
							if (value[prop]==feaItem[i]) {
								inputItem += '<label><input checked class="hidden" type="radio" name="'+prop+'" value="'+feaItem[i]+'"/>'+name[feaItem[i]]+'</label>';
							}else if (i==0) {
								inputItem += '<label><input checked class="hidden" type="radio" name="'+prop+'" value="'+feaItem[i]+'"/>'+name[feaItem[i]]+'</label>';
							}else{
								inputItem += '<label><input class="hidden" type="radio" name="'+prop+'" value="'+feaItem[i]+'"/>'+name[feaItem[i]]+'</label>';
							}
						}
						namecont ='<tr><td><div class="row"><div class="col-md-offset-1 col-md-3 col-xs-3 layout-name">'+name[prop]+
								':</div><div class="col-md-8 layout-name">'+
								'<span class="modal-layout-cont">'+inputItem+
								'</span></div></div></td></tr>';
						$('.checkTag-table-info tbody').append(namecont);
					}
				}
				for (prop in tags) {
					if (tags.hasOwnProperty(prop) && value.hasOwnProperty(prop) && value[prop]==1) {
						tagsDef += '<span class="btn tag-item-name" data-val="'+prop+'">'+tags[prop]+
									'<a href="###" class="tag-item-close"></a></span>';
						tagsStore.push(tags[prop]);
					}else{
						tagsDom += '<span class="btn tag-item-name" data-val="'+prop+'">'+tags[prop]+'</span>';
					}
				}
				$('.addTag-items').append(tagsDef);
				$('.checkTag-table-info tbody input').not(':checked').parents('label').addClass('hidden');
				var tagsTemp='<tr><td><div class="row">'+
							'<div class="col-md-offset-1 col-md-4 col-xs-4 layout-name">custom tags:'+
							'</div><div class="col-md-7 col-xs-7 layout-name">'+
							'<span class="modal-layout-cont">'+tagsStore.toString()+'</span>'+
							'</div></div></td><td></td></tr>';
				$('.system-tags').append(tagsDom);
				$('.tag-item-name').each(function(index, el) {
					if ($(this).data('val')==$(this).next().data('val')) {
						$(this).next().remove();
					}
				});
				$('.checkTag-table-info tbody tr:even').each(function(index, el) {
						$(this).find('td').appendTo($('.checkTag-table-info tbody tr').eq(index-1));
						$(this).remove();
				});
				if ($('.checkTag-table-info tbody tr:last td').length==1) {
					$('.checkTag-table-info tbody tr:last').append('<td>');
				}
				if (obj.hasClass('checkTag')) {
					$('.checkTag-table-info tbody').append(tagsTemp);
					$('.addTag-area').addClass('hidden');
				}
			}
	});
}


//var current_video_folder_id = 0;
pathManager = function() {
	var $this = pathManager;

	$this.currentFolderId = 0;
	$this.currentFolderName = $.fn.d3_t('Image');
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


function loadImageFolder (folderId, folderName, parentId) {
	$('.manage-img-cont .imginfos').empty();
	//current_folder_id = target ? target : 0;
	$.get(
		'/userlib/open-lib-folder',
		{
			type:'image',
			target : folderId,
			fbUserId : $('#selectAccount').val().split('-')[0],
			fbAccountId : $('#selectAccount').val().split('-')[1],
		},
		function(data){
			if (typeof data != 'object') {
				data=JSON.parse(data);
			};
			
			pathManager.currentFolderId = folderId;
			pathManager.currentFolderName = folderName;
			pathManager.buildPath(folderId, folderName, parentId);

			var libs=data.libs,liDom="",folderDom="",folders=data.folders;
			if (libs.length==0&&folders.length==0) {
				$('.manage-img-cont .imginfos').append('无数据！').css('padding-top', '0');
			}else{
				for (var i = 0; i < libs.length; i++) {
					var flag= libs[i].marked ==0 ? 'grayflag':'redflag';
					liDom += '<li class="clearfix" data-wh="'+libs[i].origin_width+"*"+libs[i].origin_height+'" image_url="'+libs[i].image_url+'" imge_id="'+libs[i].id+'" data-img="'+libs[i].mini_image+'">'+
							'<a href="###" class="btn"></a>'+
							'<div class="img-info clearfix"><div class="imgshow" style="background-image:url('+libs[i].mini_image+');"></div>'+
							'<p class="imgtips">ID:'+libs[i].id+'</p></div>'+
							'<div class="details '+flag+'"><p class="tip-contents" marked="'+libs[i].marked+'">'+
							'<a href="###" class="viewMenu"><span>View</span>'+
							'</a></p></div></li>';
				};
				for (var i = 0; i < folders.length; i++) {
							folderDom +='<li class="clearfix img-folder" folder_id="'+folders[i].id+'" imge_id="'+folders[i].id+'" parent_id="'+folders[i].parent_id+'" create_time="'+folders[i].create_time+'" update_time="'+folders[i].create_time+'">'+
							'<a href="###" class="btn"></a>'+
							'<div class="img-info img-folder clearfix"><div class="imgshow">'+
							'<span class="folder-click-area"></span>'+
							'<span class="folder-name" contenteditable>'+folders[i].name+'</span></div></li>';
				}
				$('.manage-img-cont .imginfos').prepend(folderDom);
				$('.manage-img-cont .imginfos').append(liDom);
				resMargin();
				getExcellImgStatus();
			}
		}
	);
}
// 获取优秀图片标签状态
function getExcellImgStatus (){
	var ids=[],tipContents="";
	$('.manage-img-cont .imginfos li').not('.img-folder').each(function(index, el) {
		ids.push(Number($(this).attr('imge_id')));
	});
	$.get('/mark/get-image-status',{
			ids : ids,
		}, function(data) {
			var data=data.data;
			for (prop in data) {
				if (data.hasOwnProperty(prop)) {
					tipContents=$('.manage-img-cont .imginfos li[imge_id="'+prop+'"]').find('.tip-contents');
					if (tipContents.attr('marked')==0) {
						tipContents.attr('fb_promotion_id', data[prop]);
						tipContents.prepend('<a href="###" class="tagName editTag"><span>Add Tag</span><em></em></a>');
					}else if(tipContents.attr('marked')==1){
						tipContents.attr('fb_promotion_id', data[prop])
						tipContents.prepend('<a href="###" class="tagName checkTag"><span>Tag</span><em></em></a>');
					}
				}
			}
	});
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
$('.details .tagName').html('Add Tag').addClass('editTag').removeClass('checkTag');
$('.details.redflag .tagName').html('Tag').addClass('checkTag').removeClass('editTag');
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
var hasclicked=false;
$(document.body).on('click','.btn-addTag', function() {
	tagNameVal=$(this).siblings('.tagName-typein').eq(0).val();
	if ( /\s/.test(tagNameVal)) {
		alert('必须不能为空！');
	}else if($(this).siblings('.tagName-typein').eq(0).attr('disabled')){
		return;
	}else{
		for (var i = 0; i < $('.tag-item-name').length; i++) {
			if (tagNameVal==$('.tag-item-name').eq(i).val()) {
				return;
			};
		}
		var tagItemName='<span class="btn tag-item-name">'+tagNameVal+'<a href="###" class="tag-item-close"></a></span>';
		$('.addTag-items').append(tagItemName);
		postTag();
		$(this).siblings('.tagName-typein').eq(0).attr('disabled', 'disabled').val('');
	}
}).on('click','.details .editTag', function() {
	var bgurl=$(this).closest('li').attr('data-img');
	hasclicked=false;
	$('.checkTag-modal').show().find('img').attr('src', bgurl);
	$('.checkTag-modal').find('.modal-content').addClass('editing-cont');
	stopScroll($('.editTag-modal'));
	var objWh=$(this).parents('li').attr('data-wh'),objId=$(this).parents('li').attr('imge_id'),
		objfbp=$(this).parents('.tip-contents').attr('fb_promotion_id');
	fb_promotion_id=objfbp;
	imge_id=objId;
	getExcellImgTagInfo($(this),objId,fb_promotion_id);
	$('.checkTag-modal').find('.img-id').attr('data-val', objId).text('ID:'+objId).attr('fb_promotion_id', objfbp);
	$('.checkTag-modal').find('.width-height').text(objWh);
	$('.checkTag-modal').find('.width-height').siblings('a.btn').addClass('saved-btn').text('save').removeClass('edit-tag-btn');
}).on('click','.details .checkTag',function() {
	stopScroll($('.checkTag-modal'));
	hasclicked=false;
	var index=$(this).closest('li').index(),
		bgurl=$(this).closest('li').attr('data-img'),
		objWh=$(this).parents('li').attr('data-wh'),
		objId=$(this).parents('li').attr('imge_id'),
		objfbp=$(this).parents('.tip-contents').attr('fb_promotion_id');
	fb_promotion_id=objfbp;
	imge_id=objId;
	getExcellImgTagInfo($(this),objId,fb_promotion_id);
	$('.checkTag-modal').show().find('img').attr('src', bgurl);
	$('.checkTag-modal').find('.img-id').attr('data-val', objId).text('ID:'+objId).attr('fb_promotion_id', objfbp);
	$('.checkTag-modal').find('.width-height').text(objWh);
	$('.checkTag-modal').find('.width-height').siblings('a.btn').addClass('edit-tag-btn').text('Edit').removeClass('saved-btn');
	$('.checkTag-modal').find('.modal-layout-cont label').removeClass('hidden');
}).on('click','.details .viewMenu',function() {
	stopScroll($('.checkTag-modal'));
	var index=$(this).closest('li').index();
	var origin_url=$(this).parents('li').attr('image_url');
	var origin_wh=$(this).parents('li').attr('data-wh').split('*');
	if (origin_wh[0]<1000) {
		$('.viewMenu-modal').show().find('.viewMenu-img').css('background-size', 'auto');
		if (origin_wh[1]>1000) {
			$('.viewMenu-modal').show().find('.viewMenu-img').css('background-size', 'contain');
		}
	}
	$('.viewMenu-modal').show().find('.viewMenu-img').css('background-image', 'url('+origin_url+')').attr('data-img', origin_url);
	$('.viewMenu-modal').find('#btn_dnload').attr('href', origin_url);
}).on('click', '.edit-tag-btn', function(event) {
	$(this).closest('.modal-content').addClass('editing-cont');
	$(this).addClass('saved-btn').removeClass('edit-tag-btn').text('Save');
	$('.modal-content label').show();
	$('.checkTag-table-info tr:last').remove();
}).on('click', '.saved-btn', function(target,event) {
	savedUserTags($(target));
	hasclicked=true;
}).on('click', '.system-tags .tag-item-name', function(event) {
	$(this).append('<a href="###" class="tag-item-close"></a>').prependTo($('.addTag-items'));
}).on('click', '.addTag-items span', function(event) {
	$(this).find('.tag-item-close').remove();
	$(this).appendTo($('.system-tags'));
	$('.tagName-typein').removeAttr('disabled');
}).on('click', '.checkTag-modal .btn-close', function(event) {
	if (hasclicked) {
		loadImageFolder (0);
	}
}).on('click', '.imginfos:visible li .img-info', function(event) {
	$(this).parents('li').toggleClass('choosed');
}).on('click','.modal .btn-close',function  () {
	$(this).closest('.modal').modalHide();
}).on('click','.img-folder-edit .btn-success', function() {
	var foldername = $(this).siblings('.folder-name-edit').val();
	var parentli = $(this).parents('li');
	var retdata;
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
		url: '/userlib/create-lib-folder?type=image&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
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
		alert($.fn.d3_t('you must select at least one image.'));
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
		data.libs.push($(this).attr('imge_id'));
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
	loadImageFolder (0);
}).on('click', '#load_fb_images', function() {
	var fbUserId = $('#selectAccount').val().split('-')[0];
	var fbAccountId = $('#selectAccount').val().split('-')[1];
	$.get(
		'/userlib/load-images?fbUserId='+fbUserId+'&fbAccountId='+fbAccountId, 
		function(msg) {
			alert(msg);
			loadImageFolder(0);
	});
}).on('click', '.path_redirect', function() {
	console.log(123);
	loadImageFolder(parseInt($(this).attr('redirect')), $(this).attr('redirect_name'));
}).on('dblclick', '.folder-click-area', function() {
	//loadImageFolder($(this).parents('li').attr('folder_id'));
	//current_folder_id=$(this).parents('li').attr('folder_id');
	loadImageFolder($(this).parents('li').attr('folder_id'), $(this).parents('li').find('.folder-name').text(), pathManager.currentFolderId);
}).on('click', '.move-modal .btn-create', function(){
	var target=$('.jstree-clicked').parents('li').attr('id');
	if (!target.length) {
		return;
	}
	var folders = [],libs = [];
	$('.manage-img-cont .imginfos li.img-folder.choosed').each(function(i){
		folders.push(parseInt($(this).attr('folder_id')));
	});
	$('.manage-img-cont .imginfos li.choosed').not('.img-folder').each(function(i){
		libs.push(parseInt($(this).attr('imge_id')));
	});
	moveLibsAndFolders(folders, libs, pathManager.currentFolderId, target);
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
}).on('change','#dcImageInput',dcUploadImages);
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
	$.ajax({
		type: 'POST',
		url: '/userlib/delete-image-lib?fbAccountId='+fbAccountId + '&current=' + pathManager.currentFolderId,
		data: data,
		success: function(ret) {
			ret = JSON.parse(ret);
			if (ret.error != '') {
				alert($.fn.d3_t('delete images failed.'));
				return;
			}
			loadImageFolder (pathManager.currentFolderId, pathManager.currentFolderName, pathManager.parentFolderId);
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
		url: '/userlib/get-folder-tree?type=image&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
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
function upload_image(){
	$('#dcImageInput').click();
}
function render_image(image){
	var html = '<div class="dcImgBlock" data-id="';
	html += image.id;
	html += '" style="background-image:url(';
	html += image.mini_image;
	html += '); float:left;margin:10px;"><div class="sizeText">';
	html += image.origin_width + ' x ' + image.origin_height;
	html += '</div><div class="flag"></div></div>';
	return html;
}
function dcUploadEnd(reply){
	if(reply.images.constructor != Array){
		alert('success!');
		//$('.manage-img-tit').trigger('click');
		loadImageFolder(pathManager.currentFolderId, pathManager.currentFolderName, pathManager.parentFolderId);
		return;
	}else{
		var str = '';
		for(var i in reply.failed){
			str += i + ' => ' + reply.failed[i] + '\n';
		}
		if(str){
			alert(str);
		} else {
			alert('Upload image failed.');
		}
	}
	$('#dcImageInput').val('');
}
function dcUploadImages(){
	var accountId = $('#selectAccount').val();
	var arr = accountId.split('-');
	var files = document.getElementById('dcImageInput').files;
	var fileNum = files.length;
	var imageMode = /^[\s\S]+(.JPEG|.jpeg|.JPG|.jpg|.GIF|.gif|.BMP|.bmp|.PNG|.png)$/;
	for(var i = 0; i < fileNum; i++){
		if(!imageMode.test(files[i].name)){
			alert('The image must be png,jpg,peg,gif or bmp.');
			return;
		}
	}
	var form = document.getElementById('dcImageUploadForm');
	var action = '/api/upload-image?fbUserId='+ arr[0] +'&fbAccountId='+ arr[1]+'&folder=' + pathManager.currentFolderId;
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(){
		if(xhr.readyState == 4){
			if(xhr.status == 200){
				try{
					var reply = JSON.parse(xhr.responseText);
					dcUploadEnd(reply);
				}
				catch(e){
					alert('Data error:'+xhr.responseText);
				}
			}else{
				alert('Upload image failed, please retry.');
			}
		}
	}
	xhr.open('POST',action);
	xhr.send(new FormData(form));
}
function moveLibsAndFolders(folders, libs, current, target) {
    var tmp = $('#selectAccount').val().split('-');
    var fbUserId = tmp[0];
    var fbAccountId = tmp[1];

    var data = {folders: folders, libs: libs, current:current, target:target, _csrf: $('#_csrf').val()};

	$.ajax({
		type: 'POST',
		url: '/userlib/move-image-libs?fbAccountId=' + fbAccountId,
		data: data,
		success: function(ret) {
			ret = JSON.parse(ret);
			if (ret.error != '') {
				alert(ret.error);
			}else{
				loadImageFolder (pathManager.currentFolderId, pathManager.currentFolderName, pathManager.parentFolderId);
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
		$("img.lazy").lazyload({
			threshold : 100
		});
	});

