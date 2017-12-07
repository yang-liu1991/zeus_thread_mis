
jQuery(document).ready(function($) {
	var tmp = $('#account').val().split('-');
	var fbUserId = tmp[0];
	var fbAccountId = tmp[1];	
    /* for folder and audience lsit */
    var $CONTAINER = $('.adTxt-table');
    var currentFolderId = 0;
    var currentFolderName = $.fn.d3_t('Creative text');
    var parentFolderId = currentFolderId;
    var parentFolderName = currentFolderName;
    var folderPath = [{name: currentFolderName, id: 0}];

/************************functions begin****************************************/

    function buildPath(currentFolderId, currentFolderName, parentId) {
        /* 根据currentFolder截取path生成新path */
        var popNum = 0;
        for(var index = folderPath.length - 1; index >= 0; index--) {
            if (folderPath[index].id == currentFolderId) {
                if (index == 0) {
                    parentFolderId = currentFolderId;
                    parentFolderName = currentFolderName;
                } else {
                    parentFolderId = folderPath[index - 1].id;
                    parentFolderName = folderPath[index - 1].name;
                }
                break;
            }
            if (folderPath[index].id == parentId) {
                parentFolderId = folderPath[index].id;
                parentFolderName = folderPath[index].name;
                folderPath[index+1] = {
                    id: currentFolderId,
                    name: currentFolderName,
                };
                popNum--;
                break;
            }
            popNum++;
        }
        for(var i = 0; i < popNum; i++) {
            folderPath.pop();
        }
    }

	//打开新文件夹，重新生成页面
	function loadFolder(folderId, folderName, parentId) {
		if (folderId == 0) {
			folderName = $.fn.d3_t('Creative text');
		}
		$.ajax({
			type: 'GET',
			url: '/userlib/open-lib-folder?type=text&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId+'&target='+folderId,
			data: {},
			success: function(ret) {
				ret = JSON.parse(ret);
				if (ret.error != '') {
					alert($.fn.d3_t('some error occurs please retry.'));
					return;
				}
				currentFolderId = folderId;
				currentFolderName = folderName;
				buildPath(currentFolderId,currentFolderName,parentId);

				var HtmlCode = [];
				for(var index in ret.folders) {
					HtmlCode.push(
'<li class="table-item row">',
'<div class="col-md-3 col-xs-3">',
'<div class="row">',
'<div class="col-md-2 col-xs-1"><input type="checkbox" folder_id="'+ret.folders[index].id+'" class="folder_check item_check"></div>',
'<div class="col-md-10 col-xs-8"><a href="javascript:void(0)" class="folder-tit" redirect="'+ret.folders[index].id+'"><i class="icon icon-folder"></i>'+ret.folders[index].name+'</a></div>',
'</div>',
'</div>',
'<div class="col-md-1 col-xs-1">' + $.fn.d3_t('Folder') + '</div>',
'<div class="col-md-4 col-xs-4">--</div>',
'<div class="col-md-4 col-xs-4">--</div>',
'</li>'
					);
				}
				for(var index in ret.libs) {
					HtmlCode.push(
'<li class="table-item row">',
'<div class="col-md-3 col-xs-3">',
'<div class="row">',
'<div class="col-md-2 col-xs-1"><input type="checkbox" lib_id="'+ret.libs[index].id+'" class="lib_check item_check"></div>',
'<div class="col-md-10 col-xs-8">'+ret.libs[index].name+'</div>',
'</div>',
'</div>',
'<div class="col-md-1 col-xs-1">' + $.fn.d3_t('Text') + '</div>',
'<div class="col-md-4 col-xs-4">' + ret.libs[index].title + '</div>',
'<div class="col-md-4 col-xs-4">' + ret.libs[index].body + '</div>',
'</li>'
					);
				}
				$CONTAINER.find('.table-container li').not(':first').remove();
				$CONTAINER.find('.table-container').append(HtmlCode.join("\n"));
				buildFolderMenu();
			},
			error: function() {
				alert($.fn.d3_t('some error occurs please retry.'));
			}
		});
	}

	//to do	
	function buildFolderMenu() {
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

		$.each(folderPath, function(){
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

    function moveLibsAndFolders(folders, libs, target) {
        $.ajax({
            type: 'POST',
            url: '/userlib/move-libs?type=text&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
            data: {folders:folders, libs:libs, target:target, _csrf: $('#_csrf').val()},
            success: function(ret) {
                ret = JSON.parse(ret);
                if (ret.error != '') {
                    alert(ret.error);
                } else {
                    loadFolder(currentFolderId, currentFolderName, parentFolderId);
                }
            },
            error: function() {
                alert($.fn.d3_t('request failed please retry.'));
                return;
            }
        });
    }

/************************functions end*****************************************/

loadFolder(0);
	$('#account').change(function(){
		document.search_form.submit();	
	});

	$('body').on('click', 'a.folder-tit', function(){
		loadFolder(parseInt($(this).attr('redirect')), $(this).text(), currentFolderId);
	});
    $('body').on('click', '.path_redirect', function(e){
        loadFolder(parseInt($(this).attr('redirect')), $(this).attr('redirect_name'));
    });

/*****************************************************************************/

	 $(document).on('click', '#xs_check_all', function() {
			var thchk=$('#xs_check_all').prop("checked");
			$('.table-container .item_check').each(function(){
				$(this).prop('checked', thchk);
			});
	  });
	$('.addfolder-btn').on('click', function() {
		$('<li class="table-item row"><div><a href="javascript:void(0)" title=""><i class="icon icon-folder"></i></a><input class="type-cont" type="text" value="new folder" placeholder="input a folder name"><a href="javascript:void(0)" class="btn btn-sm btn-save addfolder-savebtn" style="margin-right: 5px;">'+$.fn.d3_t('save')+'</a><a href="javascript:void(0)" class="btn btn-sm btn-cancel addfolder-cancelbtn">'+$.fn.d3_t('cancel')+'</a></div></li>').insertAfter('.table-container li:first');

		$('.btn-cancel').on('click', function() {
			$(this).parents('li').remove();
		});
		$('.btn-save').on('click', function() {
			var foldername = $(this).prev('.type-cont').val();
			var data = {name: foldername, parentId: currentFolderId, _csrf: $('#_csrf').val()};
			$.ajax({
				type: 'POST',
				url: '/userlib/create-lib-folder?type=text&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
				data: data,
				success: function(ret) {
					ret = JSON.parse(ret);
					if (ret.error != '') {
						alert($.fn.d3_t('some error occurs please retry.'));
						return;
					}
					loadFolder(currentFolderId, currentFolderName, parentFolderId);
				},
				error: function() {
					alert($.fn.d3_t('some error occurs please retry.'));
				}
			});
		});
	 });
	  $('.modal .btn-close').on('click',function  () {
		  $(this).closest('.modal').modalHide();
	  });
	   $('.delete_lib_btn').on('click',function  () {
		  $('.delete_ensure_modal').modal();
	  });
	  $('.addtext-btn').on('click', function() {
	      $('.add-Txt-modal').modal();	
	  });
		$('.delete_ensure_modal .btn-del').on('click', function() {
			var data = {folders: [], libs: [], _csrf: $('#_csrf').val()};
			$('.table-container .folder_check:checked').each(function(){
				data.folders.push(parseInt($(this).attr('folder_id')));
			});
			$('.table-container .lib_check:checked').each(function(){
				data.libs.push(parseInt($(this).attr('lib_id')));
			});
	
			if  (data.folders.length == 0 && data.libs.length == 0) {
				alert($.fn.d3_t('please select at least one text or folder'));
				return;
			}
			
			$.ajax({
				type: 'POST',
				url: '/userlib/delete-lib?type=text&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
				data: data,
				success: function(ret) {
					ret = JSON.parse(ret);
					if (ret.error != "") {
						alert($.fn.d3_t('some error occurs please retry.'));
						return;
					}
					loadFolder(currentFolderId, currentFolderName, parentFolderId);
				},
				error: function() {
					alert($.fn.d3_t('request failed please retry.'));
					return;
				}	
			});
			//$('table tbody td>input:checked').parents('tr').remove();
		});
	 $('#obj-range').on('click mousemove', function() {
		$('#obj-range-label').text($('#obj-range').val()+'%');
	   if ($('#obj-range').val()==0) {
		 $('#obj-range-label').text($('#obj-range').val());
	   }
	 });
	//点击btn-move的逻辑
	$('.btn-move').on('click', function() {
		console.log(123);
        if ($('.table-container .item_check:checked').length == 0) {
            alert($.fn.d3_t('please select at least one text or folder.'));
            return;
        } else {
			$('.move-modal').modal();
		}
        $.ajax({
            type: 'GET',
            url: '/userlib/get-folder-tree?type=text&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
            data: {},
            success: function(ret){
                ret = JSON.parse(ret);
                $('#folder_js_tree_container').jstree({
                    core: {
                        data: ret.folders,
                        multiple: false,
                    },
                })
            },
            error: function(){
                alert('failed');
            }
        })	
	});
	
	$('.move-modal').on('click', '.btn-create', function(){
        var jsTree = $('#folder_js_tree_container').jstree(true);
        var target = jsTree.get_selected();
        if (target.length == 0) {
            return;
        }
        target = parseInt(target[0]);
        var folders = [];
        var libs = [];
        $('.folder_check:checked').each(function(i){
            folders.push(parseInt($(this).attr('folder_id')));
        });
        $('.lib_check:checked').each(function(i){
            libs.push(parseInt($(this).attr('lib_id')));
        });
        moveLibsAndFolders(folders, libs, target);	
	});

	$('.add-Txt-modal').on('click', '.create', function(){
		var data = {
			name: $('.add-Txt-modal').find('input[name="name"]').val(),
			title: [],
			body: [],
			fb_user_id: fbUserId,
			fb_account_id: fbAccountId,
			group_id: currentFolderId,
			language: 'en-us', 
		};
		var titles = [];
		var bodies = [];
		var hasErr = false;
		$('.add-Txt-modal .titles').find('input').each(function(){
			var text = $(this).val();
			if (text != '') {
				titles.push(text);
				if (text.length > 150) {
					hasErr = true;
					$(this).focus();
					alert($.fn.d3_t('text title must be less than 150 characters.'));
					return false;
				}
			}
		});

		if(hasErr) {
			return false;
		}

		$('.add-Txt-modal .bodies').find('input').each(function(){
			var text = $(this).val();
			if (text != '') {
				bodies.push(text);
				if (text.length > 500) {
					hasErr = true;
					$(this).focus();
					alert($.fn.d3_t('text body must be less than 500 characters.'));
					return false;
				}
			}
		});
		if (hasErr) {
			return false;
		}

		if (titles.length == 0 ) {
			alert($.fn.d3_t('text title should not be blank.'));
			return false;
		}
		if (bodies.length == 0) {
			alert($.fn.d3_t('text body should not be blank.'));
			return false;
		}
		data.title = titles;
		data.body = bodies;

		$.ajax({
			type: 'POST',
			url: "/userlib/add-creative-text",
			data: {data: data, _csrf: $('#_csrf').val()},
			success: function(ret) {
				$('.add-Txt-modal').modalHide();
				ret = JSON.parse(ret);
				if (ret.length > 0) {
					loadFolder(currentFolderId, currentFolderName, parentFolderId);
				} else {
					alert($.fn.d3_t('create creative text failed.'));	
				}
			},
			error: function(){
				alert($.fn.d3_t('create creative text failed, internet error.'));
			}
		});
	});	
});
