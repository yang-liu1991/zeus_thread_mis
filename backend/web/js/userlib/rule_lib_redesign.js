$(document).ready(function($) {
	var $CONTAINER = $('.adTxt-table');
	var currentFolderId = 0;
	var currentFolderName = $.fn.d3_t('Rule');
	var parentFolderId = currentFolderId;
	var parentFolderName = currentFolderName;
	var folderPath = [{name: currentFolderName, id: 0}];
/************************functions begin*************************************/
	function buildPath(parentId) {
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
			folderName = $.fn.d3_t('Rule');
		}
		$.ajax({
			type: 'GET',
			url: '/userlib/open-lib-folder?type=rule&target='+folderId,
			data: {},
			success: function(ret) {
				ret = JSON.parse(ret);
				if (ret.error != '') {
					alert($.fn.d3_t('some error occurs please retry.'));
					return;
				}
				currentFolderId = folderId;
				currentFolderName = folderName;
				buildPath(parentId);

				var HtmlCode = [];
				for(var index in ret.folders) {
					HtmlCode.push(
						'<tr>',
							'<td><input type="checkbox" folder_id="'+ret.folders[index].id+'" class="folder_check item_check"><a href="###" class="folder-tit" redirect="'+ret.folders[index].id+'"><i class="icon icon-folder"></i>'+ret.folders[index].name+'</a></td>',
							'<td>' + $.fn.d3_t('Folder') + '</td>',
							'<td>--</td>',
							'<td>--</td>',
							'<td>--</td>',
							'<td>' + ret.folders[index].create_time + '</td>',
							'<td>' + ret.folders[index].update_time + '</td>',
						'</tr>'
					);
				}
				for(var index in ret.libs) {
					HtmlCode.push(
						'<tr>',
							'<td><input type="checkbox" lib_id="'+ret.libs[index].id+'" class="lib_check item_check">'+ret.libs[index].name+'</td>',
							'<td>' + $.fn.d3_t('Rule') + '</td>',
							'<td>' + ret.libs[index].action_info + '</td>',
							'<td>' + ret.libs[index].condition_info + '</td>',
							'<td>' + ret.libs[index].lookback_window + '</td>',
							'<td>' + ret.libs[index].create_time + '</td>',
							'<td>' + ret.libs[index].update_time + '</td>',
						'</tr>'
					);
				}

				$CONTAINER.find('.table-body').find('table').find('tbody').empty().append(HtmlCode.join("\n"));
				buildFolderMenu();
			},
			error: function() {
				alert($.fn.d3_t('some error occurs please retry.'));
			}
		});
	}

	//生成路径导航 to do
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
			url: '/userlib/move-libs?type=rule',
			data: {folders:folders, libs:libs, target:target, _csrf:$('#_csrf').val()},
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


/************************functions end****************************************/
	loadFolder(0)

	$('body').on('click', '.path_redirect', function(e){
		loadFolder(parseInt($(this).attr('redirect')), $(this).attr('redirect_name'));
	});	

	$('body').on('click', 'a.folder-tit', function(e){
		loadFolder(parseInt($(this).attr('redirect')), $(this).text(), currentFolderId);
	});

/***********************************/

			$('.lit-bar').on('onmousemove', function() {
				alert('0');
			});
			 $('.main-table thead input[type="checkbox"]').on('click', function() {
				  var thchk=$('.main-table thead input[type="checkbox"]').prop("checked");
				  var tb=$('.main-table tbody input[type="checkbox"]');
				   if (thchk) {
					tb.each(function () {
					  if(!$(this).is(':checked'))
						$(this).click();
					})
				   }else {
					  tb.removeAttr('checked');
				   }
			  });
			 $('.sub-folder thead input[type="checkbox"]').on('click', function() {
				  var thchk=$('.sub-folder thead input[type="checkbox"]').prop("checked");
				  var tb=$('.sub-folder tbody input[type="checkbox"]');
				   if (thchk) {
					tb.each(function () {
					  if(!$(this).is(':checked'))
						$(this).click();
					})
				   }else {
					  tb.removeAttr('checked');
				   }
			  });
			 $('.addfolder-btn').on('click', function() {
				$('tr.new_folder').remove();
				$('<tr class="new_folder"><td><input type="checkbox"><a href="###" title=""><i class="icon icon-folder"></i></a><input class="type-cont" type="text" value="new folder" placeholder="input a folder name"><a href="###" class="btn btn-sm btn-save" style="margin-right: 5px;">save</a><a href="###" class="btn btn-sm btn-cancel">cancel</a></td></tr>').prependTo('tbody');
				$('.btn-cancel').on('click', function() {
					$(this).parents('tr').remove();
				});
				$('.btn-save').on('click', function() {
					var foldername = $(this).prev('.type-cont').val();
					var data = {name: foldername, parentId: currentFolderId, _csrf: $('#_csrf').val()};
					console.log(data);
					$.ajax({
						type: 'POST',
						url: '/userlib/create-lib-folder?type=rule',
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
			  $('.sigl-tab').on('click',function  () {
				$('.sigl').css('display', 'block');
				  $('.mult').css('display', 'none');
			  });
			   $('.mult-tab').on('click',function  () {
				   $('.mult').css('display', 'block');
				  $('.sigl').css('display', 'none');
			  });
			  $('.btn-close').on('click',function  () {
				  $('.modal-mask,.choose-modal').css('display', 'none');
			  });
			  $('.choose-img,.creat-custom-ad').on('click',function  () {
				  $('.chooseImg-modal').css('display', 'block');
			  });
			  $('.addrule-btn').on('click',function  () {
				  $('.modal-mask,.creat-rule-modal').css('display', 'block');
			  });
			  $('.creat-custom-ad').on('click',function  () {
				  $('.modal-mask,.creat-custom-ad-modal').css('display', 'block');
			  });
			$('.choose-from').on('click',function  () {
				if ($('table tbody td>input.item_check:checked').length == 0) {
					alert($.fn.d3_t('please select at least one audience or folder'));
					return;
				} else {
					$('.modal-mask,.chooseTxt-modal').css('display', 'block');
				}
			});
/*****custom******/
			  $('.chooseTxt-modal .btn-del').on('click', function() {
				var data = {folders: [], libs: [], _csrf: $('#_csrf').val()};
				$('table tbody td>input.folder_check:checked').each(function(){
					data.folders.push(parseInt($(this).attr('folder_id')));
				});
				$('table tbody td>input.lib_check:checked').each(function(){
					data.libs.push(parseInt($(this).attr('lib_id')));
				});
				console.log(data);
				if  (data.folders.length == 0 && data.libs.length == 0) {
					alert($.fn.d3_t('please select at least one audience or folder'));
					return;
				}
				
				$.ajax({
					type: 'POST',
					url: '/userlib/delete-lib?type=rule',
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
			});

	$('.btn-move').on('click', function() {
		console.log(123);
		if ($('table tbody td>input:checked').length == 0) {
			alert($.fn.d3_t('please select at least one audience or folder.'));
			return;
		} else {
			$('.modal-mask,.move-modal').css('display', 'block');
		}
		$.ajax({
			type: 'GET',
			url: '/userlib/get-folder-tree?type=rule',
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
	
	//todo
	function getRuleConditions(){
		return [];
	}

	$('.creat-rule-modal .btn-create').click(function(){
		console.log(123);
		var name = $('.creat-rule-modal').find('input[name="name"]').val();
		var action = $('.creat-rule-modal').find('input[name="action"]').val();
		var timeWindow = $('.creat-rule-modal').find('input[name="time_window"]').val();
		var conditions = getRuleConditions();
	
		conditions.push('dt < '+timeWindow);
		var rule = conditions.join('&&') + '::' + action;

		var data = {
			_csrf: $('#_csrf').val(),
			data: {
				ruleText: rule,
				ruleName: name,
				group_id: currentFolderId,
			}
		}
	
        $.ajax({                                                                       
            type: 'POST',                                                              
            url: '/userlib/add-rule-text',                                             
            data: data,
            success: function(ret) {
                ret = JSON.parse(ret);                                                 
                var data = ret.data;                                                   
                if (ret.error != '') {                                                
                    alert('Add rule failed，'+data.error);                             
                    return;
                }   
                alert('Success！');                                                    
                loadFolder(currentFolderId, currentFolderName, parentFolderId);
            },
            error: function() {
                alert('Internet error, add rule failed.');
            }
        });
	});
});
  
