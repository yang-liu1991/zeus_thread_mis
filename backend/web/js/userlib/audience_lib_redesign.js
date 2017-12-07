jQuery(document).ready(function($) {
	var tmp = $('#account').val().split('-');
	var fbUserId = tmp[0];
	var fbAccountId = tmp[1];	
	/* for folder and audience lsit */
	var $CONTAINER = $('.adTxt-table');
	var currentFolderId = 0;
	var currentFolderName = $.fn.d3_t('Audience');
	var parentFolderId = currentFolderId;
	var parentFolderName = currentFolderName;
	var folderPath = [{name: currentFolderName, id: 0}];
	var FOLDERS = {};
	var LIBS = {};

/************************functions begin****************************************/
	$('.refresh-audience').click(function(){
		window.location.href = '/userlib/load-customaudiences?fbUserId='+fbUserId+'&fbAccountId='+fbAccountId;
	});

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

	function buildBody(folders, libs) {
		var htmlCode = [];
		$.each(folders, function() {
			htmlCode.push(
				'<tr class="folder">',
					'<td><input type="checkbox" folder_id="'+this.id+'" class="folder_check item_check"></td>',
					'<td class="has-keep-width"><a href="javascript:void(0)" class="folder-tit" redirect="' + this.id + '"><i class="icon icon-folder"></i>' + this.name + '</a></td>',
					'<td>' + $.fn.d3_t('Folder') + '</td>',
					'<td>--</td>',
					'<td>--</td>',
					'<td class="table-datetime">' + this.create_time + '</td>',
					'<td class="table-datetime">' + this.update_time + '</td>',
					'<td></td>',
				'</tr>'
			);
		});
		$.each(libs, function() {
			htmlCode.push(
				'<tr class="lib">',
					'<td><input type="checkbox" lib_id="' + this.id + '" class="lib_check item_check"></td>',
					'<td class="has-keep-width">' + this.name + '</td>',
					'<td>' + this.type + '</td>',
					'<td>' + this.size + '</td>',
					'<td>',
						(this.status_info.code == 200 ? '<label class="label label-success">' + $.fn.d3_t('Ready') + '</label>'
							: '<label class="label label-warning">' + $.fn.d3_t('Not Ready') + '</label>'),
						'<br>',
						'<span>' + this.status_info.description + '</span>',
					'</td>',
					'<td class="table-datetime">' + this.create_time + '</td>',
					'<td class="table-datetime">' + this.update_time + '</td>',
					'<td>'+(this.type_id == 2 ? '<a class="edit-custom-audience has-text-underline" href="javascript:void(0)">'+$.fn.d3_t('Edit')+'</a>' : '') +'</td>',
				'</tr>'
			);
		});

		$CONTAINER.find('.table-body').find('table').find('tbody').empty().append(htmlCode.join("\n"));
		buildFolderMenu();
		$('.edit-custom-audience').click(function(){
			if (buildEditCustomAudienceModal($(this).closest('tr'))) {
				$('.edit-custom-audience-modal').modal();
			}
		});
	}

	//打开新文件夹，重新生成页面
	function loadFolder(folderId, folderName, parentId) {
		if (folderId == 0) {
			folderName = $.fn.d3_t('Audience');
		}
		$.ajax({
			type: 'GET',
			url: '/userlib/open-lib-folder?type=audience&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId+'&target='+folderId,
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

				FOLDERS = {};
				LIBS = {};
				$.each(ret.folders, function() {
					FOLDERS[this.id] = this;
				});
				$.each(ret.libs, function(){
					LIBS[this.id] = this;
				})

				buildBody(FOLDERS, LIBS);

			},
			error: function() {
				alert($.fn.d3_t('some error occurs please retry.'));
			}
		});
	}

	function buildEditCustomAudienceModal($tr) {
		libData = LIBS[$tr.find('.lib_check').attr('lib_id')];
		if (!libData) {
			alert($.fn.d3_t('loading data failed, please reload the page and try again.'));
			return false;
		}
		$('.edit-custom-audience-modal input[name="id"]').val(libData.id);
		$('.edit-custom-audience-modal input[name="name"]').val(libData.name);
		$('.edit-custom-audience-modal input[name="description"]').val(libData.description);
		return true;
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

	function moveAudienceAndFolders(folders, audiences, target) {
		$.ajax({
			type: 'POST',
			url: '/userlib/move-libs?type=audience&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
			data: {folders:folders, libs:audiences, target:target, _csrf: $('#_csrf').val()},
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
	//$('.audience_name_hover').popover({trigger:'hover', placement:'top'});
	$('.btn-refresh').click(function(){
		refresh_audiences();
	});

	$('body').on('click', 'a.folder-tit', function(){
		$('#search_audience').val('');
		loadFolder(parseInt($(this).attr('redirect')), $(this).text(), currentFolderId);
	});
	$('body').on('click', '.path_redirect', function(e){
		$('#search_audience').val('');
		loadFolder(parseInt($(this).attr('redirect')), $(this).attr('redirect_name'));
	});

/*****************************************************************************/

	 $('.main-table thead input[type="checkbox"]').on('click', function() {
		  var thchk=$('.main-table thead input[type="checkbox"]').prop("checked");
		  var tb=$('.main-table tbody input[type="checkbox"]');
		   if (thchk) {
			tb.each(function () {
			  if(!$(this).is(':checked'))
				$(this).click();
			  // tb.attr('checked', 'true');
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
			  // tb.attr('checked', 'true');
			})
			  
		   }else {
			  tb.removeAttr('checked');
		   }
	  });
	$('.addfolder-btn').on('click', function() {
		$('<tr class="new_folder"><td colspan="5" class="show-user-cont"><input type="checkbox"><a href="javascript:void(0)" title=""><i class="icon icon-folder"></i></a><input class="type-cont" type="text" value="new folder" placeholder="input a folder name"><a href="javascript:void(0)" class="btn btn-sm btn-save" style="margin-right: 5px;">'+$.fn.d3_t('save')+'</a><a href="javascript:void(0)" class="btn btn-sm btn-cancel">'+$.fn.d3_t('cancel')+'</a></tr>').prependTo('tbody');

		$('.btn-cancel').on('click', function() {
			$(this).parents('tr').remove();
		});
		$('.btn-save').on('click', function() {
			var foldername = $(this).prev('.type-cont').val();
			var data = {name: foldername, parentId: currentFolderId, _csrf: $('#_csrf').val()};
			$.ajax({
				type: 'POST',
				url: '/userlib/create-lib-folder?type=audience&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
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
/*
		  var intvalue=$(this).prev('.type-cont').val();
		  $(this).prevUntil('folder-tit').children('.icon-folder').after(intvalue);
		  $(this).prev('.type-cont').remove();
		  $(this).next().remove();
		  $(this).remove();
		  // $(this).prev('.type-cont').replaceWith('<span class="type-cont">'+intvalue+'</span>');
*/
		});
	 });
/*
	 $('.folder-nav').hide();
	 $('.folder-tit').on('click', function() {
	   $('.main-table').hide();
	   $('.sub-folder-nav').text($(this).text());
	   $('.folder-nav,.sub-folder').show();
	 });
*/
		$('.modal .btn-close').on('click',function  () {
			//$('.choose-modal').css('display', 'none');
			$(this).closest('.modal').modalHide();
		});
		$('.create-lookalike-audience').on('click',function  () {
			$('.create-lookalike-audience-modal').modal();
		});
		$('.create-app-audience').on('click',function  () {
			$('.create-app-audience-modal').modal();
		});
		$('.create-custom-audience').on('click', function() {
			$('.create-custom-audience-modal').modal();
		});
		$('.delete_lib_btn').on('click',function  () {
			if ($('table tbody td>input.item_check:checked').length == 0) {
				alert($.fn.d3_t('please select at least one audience or folder'));
				$('.delete_ensure_modal').modalHide();
				return;
			} else {
				$('.delete_ensure_modal').modal();
			}
		});
		$('.delete_ensure_modal .btn-del').on('click', function() {
			var data = {folders: [], libs: [], _csrf: $('#_csrf').val()};
			$('table tbody td>input.folder_check:checked').each(function(){
				data.folders.push(parseInt($(this).attr('folder_id')));
			});
			$('table tbody td>input.lib_check:checked').each(function(){
				data.libs.push(parseInt($(this).attr('lib_id')));
			});
			if  (data.folders.length == 0 && data.libs.length == 0) {
				alert($.fn.d3_t('please select at least one audience or folder'));
				return;
			}
			$.ajax({
				type: 'POST',
				url: '/userlib/delete-lib?type=audience&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
				data: data,
				success: function(ret) {
					ret = JSON.parse(ret);
					if (ret.error != "") {
						alert($.fn.d3_t('some error occurs please retry.'));
						return;
					} else {
						alert($.fn.d3_t('delete audiences successfully'));
						loadFolder(currentFolderId, currentFolderName, parentFolderId);
					}
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
		if ($('table tbody td>input:checked').length == 0) {
			alert($.fn.d3_t('please select at least one audience or folder.'));
			return;
		} else {
			$('.move-modal').modal();
		}
		$.ajax({
			type: 'GET',
			url: '/userlib/get-folder-tree?type=audience&fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
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
		var audiences = [];
		$('.folder_check:checked').each(function(i){
			folders.push(parseInt($(this).attr('folder_id')));
		});
		$('.lib_check:checked').each(function(i){
			audiences.push(parseInt($(this).attr('lib_id')));
		});
		moveAudienceAndFolders(folders, audiences, target);	
	});

	$('.create-lookalike-audience-modal').on('click', 'input:checkbox[name="use_default_name"]', function(){
		if ($(this).prop('checked')) {
			$('.create-lookalike-audience-modal').find('input:text[name="name"]').hide();
		} else {
			$('.create-lookalike-audience-modal').find('input:text[name="name"]').show();
		}
	});
	
	$('.create-lookalike-audience-modal').on('change', 'select[name="type"]', function(){
		var $sourceContainer = $('.create-lookalike-audience-modal').find('[name="source"]').parent();
		$sourceContainer.empty();
		switch(Number($(this).val())) {
		case 42:
			var tmpHtml = [];
			tmpHtml.push(
				'<select name="source"></select>'
			);
			$sourceContainer.append(tmpHtml.join(''));
			$.ajax({
				type: 'GET',
				url: '/api/get-pages?fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
				data: {},
				success: function(ret){
					var pages = JSON.parse(ret);
					var tmpHtml = [];
					for(var index in pages) {
						tmpHtml.push('<option value="'+pages[index].id+'">'+pages[index].name+'</option>');
					}
					$sourceContainer.find('[name="source"]').append(tmpHtml.join(''));
				},
				error: function() {
					alert("read custom audiences failed，please retry.");
				}
			});
			break;	
		case 43:
			var tmpHtml = [];
			tmpHtml.push(
				'<input type="text" class="campaign_search" placeholder="enter id(d3 or facebook id) or name to search campaign" />',
				'<input type="hidden" name="source">'
			);
			$sourceContainer.append(tmpHtml.join(''));
			$sourceContainer.find('.campaign_search').autocomplete({
				serviceUrl: '/ads-management/auto-campaign',
				type: 'GET',
				params: {
					fbUserId: fbUserId,
					fbAccountId: fbAccountId,
				},
				paramName: 'term',
				showNoSuggestionNotice: true,
				noCache: false,
				deferRequestBy: 200,
				onSelect: function(selectedItem) {
					$sourceContainer.find('.campaign_search').val('');
					var selectedIds = $sourceContainer.find('input[name="source"]').val();
					selectedIds = selectedIds == '' ? [] : selectedIds.split(',');
					if ($.inArray(selectedItem.value, selectedIds) < 0) {
						selectedIds.push(selectedItem.data.fbCampaignId);
						var tmpHtml = [];
						tmpHtml.push(
							'<button class="campaigns" campaign="'+selectedItem.value+'" fbCampaignId="'+selectedItem.data.fbCampaignId+'">'+selectedItem.value+'<span style="color:red">&times;</span></button>'
						);
						$sourceContainer.append(tmpHtml.join(''));
						$sourceContainer.find('input[name="source"]').val(selectedIds.join(','));

						$('.campaigns').click(function(e){
						var fbCampaignId = $(this).attr('fbCampaignId');
							$(this).remove();
							var totalIds = $sourceContainer.find('[name="source"]').val() == '' ? [] : $sourceContainer.find('[name="source"]').val().split(',');
							var index = $.inArray(fbCampaignId, totalIds)
							if (index >= 0) {
								totalIds.splice(index, 1);
							}
						$sourceContainer.find('[name="source"]').val(totalIds.join(','));
					});
					}
				}
			});
			break;
		case 41:
		default:
			var tmpHtml = [];
			tmpHtml.push(
				'<select name="source"></select>'
			);
			$sourceContainer.append(tmpHtml.join(''));
			$.ajax({
				type: 'GET',
				url: '/userlib/get-customaudiences?fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
				data: {},
				success: function(ret){
					var audiences = JSON.parse(ret);
					var tmpHtml = [];
					for(var id in audiences) {
						tmpHtml.push('<option value='+audiences[id].fbId+'>'+audiences[id].name+'</option>');
					}
					$sourceContainer.find('select[name="source"]').append(tmpHtml.join(''));
				},
				error: function() {
					alert("Read custom audiences info failed，please retry.");
				}
			});
		}
	});
	$('.create-lookalike-audience-modal').find('select[name="type"]').change();

	$('.country_search').autocomplete({
		serviceUrl: '/api/auto-location?fbUserId='+fbUserId,
		params: {
			fbUserId: fbUserId,
		},
		paramName: 'term',
		showNoSuggestionNotice: true,
		noCache: false,
		deferRequestBy: 200,
		onSelect: function(selectedItem) {
			$('.country_search').attr("country_code", selectedItem.data.country_code);
		}
	});

	$('.create-lookalike-audience-modal').find('.btn-create').click(function(e){
		var $modal = $('.create-lookalike-audience-modal');
		if ($modal.find('input[name="country"]').val() == '' || $modal.find('input[name="country"]').attr('country_code') == '') {
			alert($.fn.d3_t('country id nessary for lookalike audience'));
			//return;
		}
		$(this).html('creating ...').attr('disabled','disabled');				   
		var name = $modal.find('input[name="name"]').val();								
		var audienceType = Number($modal.find('select[name="type"]').val());
		var data = {
			'description': $modal.find('input[name="description"]').val(),
			'audience_type': audienceType,
			'ratio': $('#obj-range').val(),
			'fb_origin_obj_id': $modal.find('[name="source"]').val(),
			'country': $modal.find('input[name="country"]').attr('country_code')
		};
		if (!$modal.find('input:checkbox[name="use_default_name"]').prop('checked')) {
			data['name'] = name;
		}
		$.ajax({
			type: 'POST',
			url: '/userlib/add-lookalikeaudience?fbUserId='+fbUserId+'&fbAccountId='+fbAccountId+'&folder='+currentFolderId,
			data: {data: data, _csrf: $('#_csrf').val()},
			success: function(ret) {
				ret = JSON.parse(ret);
				$modal.find('btn-create').html('Create').removeAttr('disabled');
				if (ret.error == '') {
					alert($.fn.d3_t('success!'));
					$modal.find('btn-create').html('Create').html('Create').removeAttr('disabled');
					loadFolder(currentFolderId, currentFolderName, parentFolderId);
				} else {
					alert('Create audience failed.'+ret.error);
				}
			},
			error: function() {
				$modal.find('btn-create').html('Create').html('Create').removeAttr('disabled');
				alert('Internet error, Create audience failed.')
			}
		});
	});

	$('.create-app-audience-modal').on('click', '.btn-create', function(){
		var $modal = $('.create-app-audience-modal');
		$(this).html('creating ...').attr('disabled','disabled');
		var name = $modal.find('input[name="name"]').val();
		if ($modal.find('input:checkbox[name="use_default_name"]').prop('checked')) {
			name = $modal.find('select[name="app"]').find('option:selected').text() + '-' + $modal.find('select[name="app_event"]').find('option:selected').text() + '-' + $modal.find('select[name="retention_days"]').val() + 'days';
		}
		var data = {
			name: name,
			app_id: $modal.find('select[name="app"]').val(),
			app_event_name: $modal.find('select[name="app_event"]').val(),
			retention_days: $modal.find('select[name="retention_days"]').val(),
			description: $modal.find('input[name="description"]').val(),
			audience_type:1,
		};
		$.ajax({
			type: 'POST',
			url: '/userlib/add-appaudience?fbUserId='+fbUserId+'&fbAccountId='+fbAccountId+'&folder='+currentFolderId,
			data: {data: data, _csrf: $('#_csrf').val()},
			success: function(ret) {
				ret = JSON.parse(ret);
				if (ret.error != '') {
					$modal.find('btn-create').html('create').removeAttr('disabled');
					alert(ret.error);
				} else {
					alert('success！');
					$modal.find('btn-create').html('create').removeAttr('disabled');
					loadFolder(currentFolderId, currentFolderName, parentFolderId);
				}
			},	
			error: function() {
				$modal.find('btn-create').html('create').removeAttr('disabled'); 
				alert($.fn.d3_t('Internet error, Create audience failed.'));
			}
		});	
	});

	var appsEvents = {}
	function load_app_and_event() {
		$('.create-app-audience-modal').find('select[name="app"]').empty().attr('disabled', 'disabled').append('<option>loading...</option>');
		$('.create-app-audience-modal').find('select[name="app_event"]').empty().attr('disabled', 'disabled').append('<option>loading...</option>');
		$.ajax({
			type: 'GET',
			url: '/api/get-apps-with-events?fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
			data: {},
			success: function(ret) {
				appsEvents = JSON.parse(ret);
				var appHtml = [];
				for(var appId in appsEvents) {
					appHtml.push(
						'<option value="'+appId+'">'+appsEvents[appId].app.name+'</option>'
					);
				}
				$('.create-app-audience-modal').find('select[name="app"]').removeAttr("disabled").empty().append(appHtml.join(''));
				var eventsHtml = [];
				var appId = $('.create-app-audience-modal').find('select[name="app"]').val();
				if (appsEvents[appId]) {
					for(var index in appsEvents[appId].events) {
						var event = appsEvents[appId].events[index];
						eventsHtml.push(
							'<option value="'+event.eventName+'">'+event.displayName+'</option>'
						);
					}
				}
				$('.create-app-audience-modal').find('select[name="app_event"]').removeAttr("disabled").empty().append(eventsHtml.join(''));
			},
			error: function() {
				alert($.fn.d3_t("load app's data failed，please refresh and retry."));
			}
		});
	}
	load_app_and_event();

	$('.create-app-audience-modal').on('change', 'select[name="app"]', function(){
		var appId = $(this).val();
		var events = appsEvents[appId].events;
		var html = [];
		for(var eventName in events) {
			var event = events[eventName];
			html.push('<option value='+eventName+'>'+event.displayName+'</option>');
		}
		$('.create-app-audience-modal select[name="app_event"]').empty().append(html.join('\n'));
	});

/*create custom audience*/
	$('.create-custom-audience-modal').on('change', 'select[name="type"]', function(){
		if ($(this).val() == 'appuser') {
			$.ajax({
				type: 'get',
				url: '/api/get-apps?fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
				data: {},
				success: function(ret) {
					ret = JSON.parse(ret);
					if (!ret) {
						alert($.fn.d3_t('get app list failed.'));
						return;
					}
					var html = [];
					$.each(ret, function(){
						html.push('<option value="'+this.id+'">'+this.name+'</option>');
					});
					$('.create-custom-audience-modal .app_select').empty().append(html.join('\n'));
				},
				error: function() {
					alert($.fn.d3_t('get app list failed, internet error.'));
				}
			});
			$('.create-custom-audience-modal .app-field').show();
		} else {
			$('.create-custom-audience-modal .app-field').hide();
		}
		switch($(this).val()) {
		case 'email':
			var code = [];
			code.push(
				'<span style="font-size:16px">'+$.fn.d3_t('Example')+':</span><br>',
				'<div class="format-example">',
				'name1@example.com'+'<br>',
				'name2@example.com'+'<br>',
				'name3@example.com;name4@example.com',
				'</div>'
			);
			$('.create-custom-audience-modal').find('.format-field').empty().append(code.join('\n'));
			break;
		case 'appuser':
			var code = [];
			code.push(
				'<span>'+$.fn.d3_t('Example')+':</span><br>',
				'<div class="format-example">',
				'1234567890'+'<br>',
				'2345678901'+'<br>',
				'3456789012;4567890123',
				'</div>'
			);
			$('.create-custom-audience-modal').find('.format-field').empty().append(code.join('\n'));
			break;
		case 'phonenumber':
			var code = [];															 
			code.push(																 
				'<span>'+$.fn.d3_t('Rule')+':</span></br>',
				'<div class="format-example">',
					'<span>-'+$.fn.d3_t('Phone number should be Country code + Area Code + Number.')+'</span><br>',
					'<span>-'+$.fn.d3_t('No leading zeros from country code.')+'</span><br>',
					'<span>-'+$.fn.d3_t('Only numeric characters is allowed.')+'</span><br>',
				'</div>',
				'<span>'+$.fn.d3_t('Example')+':</span><br>',
				'<div class="format-example">',
				'16505551234'+'<br>',
				'16505551235'+'<br>',
				'16505551236;16505551237',
				'</div>'
			);
			$('.create-custom-audience-modal').find('.format-field').empty().append(code.join('\n'));
			break;
		case 'mobileid':
			var code = [];
			code.push(
				'<span>'+$.fn.d3_t('Support list')+':</span></br>',
				'<div class="format-example">',
					'<span>-'+$.fn.d3_t('Android\' advertising ID.')+'</span><br>',
					'<span>-'+$.fn.d3_t('Apples\' Advertising Identifier(IDFA).')+'</span><br>',
					'<span>-'+$.fn.d3_t('Facebook App User ID.')+'</span><br>',
				'</div>',
				'<span>'+$.fn.d3_t('Example(facebook user id)')+':</span><br>',
				'<div class="format-example">',
				'1234567890'+'<br>',
				'2345678901'+'<br>',
				'3456789012;4567890123',
				'</div>'
			);
			$('.create-custom-audience-modal').find('.format-field').empty().append(code.join('\n'));
			break;	
		}
	});
	$('.create-custom-audience-modal select[name="type"]').change();

	$('.create-custom-audience-modal').on('change', 'select[name="source"]', function(){
		if($(this).val() == 'file') {
			$('.create-custom-audience-modal .customer-list').hide();
			$('.create-custom-audience-modal .data-info').hide();
			$('.create-custom-audience-modal .customer-file').show();
			$('.create-custom-audience-modal .file-info').show();
		} else {
			$('.create-custom-audience-modal .customer-file').hide();
			$('.create-custom-audience-modal .file-info').hide();
			$('.create-custom-audience-modal .customer-list').show();
			$('.create-custom-audience-modal .data-info').show();
		}
	});

	$('.create-custom-audience-modal .btn-create').click(function(){
		var form = document.getElementById('create-custom-audience-form');
		var action = '/userlib/add-custom-audience?fbUserId='+fbUserId+'&fbAccountId='+fbAccountId+'&folder='+currentFolderId;
		var data = new FormData(form);
		data = new FormData();
		data.append('a','b')
/*
		$.ajax({
			type: 'get',
			contentType: false,
			processData: false,
			url: action,
			data: data,
			success: function(ret){
				console.log(ret);	
			},
			error:function() {
				console.log('err');
			}
		});		
		return;
*/
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function() {
			if (xhr.readyState == 4) {
				if (xhr.status == 200) {
					try {
						var ret = JSON.parse(xhr.responseText);
						if (ret['info']) {
							alert(ret['info']);
						}
						if (ret['errors'] && ret['errors'].length > 0) {
							$.each(ret['errors'], function(i){
								alert(this);
							});
						}
						alert($.fn.d3_t('create custom audience over'));
						loadFolder(currentFolderId, currentFolderName, parentFolderId);
					} catch(e) {
						alert($.fn.d3_t('some error occurs, please retry later.'));
					}
				} else {
					alert($.fn.d3_t('create failed, internet error.'));
				}
			}
		}
		xhr.open('POST',action);
		var data = new FormData(form)
		xhr.send(new FormData(form));
	});

/***update custom audience***/
	$('.edit-custom-audience-modal').on('change', 'select[name="type"]', function(){
		if ($(this).val() == 'appuser') {
			$.ajax({
				type: 'get',
				url: '/api/get-apps?fbUserId='+fbUserId+'&fbAccountId='+fbAccountId,
				data: {},
				success: function(ret) {
					ret = JSON.parse(ret);
					if (!ret) {
						alert($.fn.d3_t('get app list failed.'));
						return;
					}
					var html = [];
					$.each(ret, function(){
						html.push('<option value="'+this.id+'">'+this.name+'</option>');
					});
					$('.edit-custom-audience-modal .app_select').empty().append(html.join('\n'));
				},
				error: function() {
					alert($.fn.d3_t('get app list failed, internet error.'));
				}
			});
			$('.edit-custom-audience-modal .app-field').show();
		} else {
			$('.edit-custom-audience-modal .app-field').hide();
		}
		switch($(this).val()) {
		case 'email':
			var code = [];
			code.push(
				'<span style="font-size:16px">'+$.fn.d3_t('Example')+':</span><br>',
				'<div class="format-example">',
				'name1@example.com'+'<br>',
				'name2@example.com'+'<br>',
				'name3@example.com;name4@example.com',
				'</div>'
			);
			$('.edit-custom-audience-modal').find('.format-field').empty().append(code.join('\n'));
			break;
		case 'appuser':
			var code = [];
			code.push(
				'<span>'+$.fn.d3_t('Example')+':</span><br>',
				'<div class="format-example">',
				'1234567890'+'<br>',
				'2345678901'+'<br>',
				'3456789012;4567890123',
				'</div>'
			);
			$('.edit-custom-audience-modal').find('.format-field').empty().append(code.join('\n'));
			break;
		case 'phonenumber':
			var code = [];
			code.push(
				'<span>'+$.fn.d3_t('Rule')+':</span></br>',
				'<div class="format-example">',
					'<span>-'+$.fn.d3_t('Phone number should be Country code + Area Code + Number.')+'</span><br>',
					'<span>-'+$.fn.d3_t('No leading zeros from country code.')+'</span><br>',
					'<span>-'+$.fn.d3_t('Only numeric characters is allowed.')+'</span><br>',
				'</div>',
				'<span>'+$.fn.d3_t('Example')+':</span><br>',
				'<div class="format-example">',
				'16505551234'+'<br>',
				'16505551235'+'<br>',
				'16505551236;16505551237',
				'</div>'
			);
			$('.edit-custom-audience-modal').find('.format-field').empty().append(code.join('\n'));
			break;
		case 'mobileid':
			var code = [];
			code.push(
				'<span>'+$.fn.d3_t('Support list')+':</span></br>',
				'<div class="format-example">',
					'<span>-'+$.fn.d3_t('Android\' advertising ID.')+'</span><br>',
					'<span>-'+$.fn.d3_t('Apples\' Advertising Identifier(IDFA).')+'</span><br>',
					'<span>-'+$.fn.d3_t('Facebook App User ID.')+'</span><br>',
				'</div>',
				'<span>'+$.fn.d3_t('Example(facebook user id)')+':</span><br>',
				'<div class="format-example">',
				'1234567890'+'<br>',
				'2345678901'+'<br>',
				'3456789012;4567890123',
				'</div>'
			);
			$('.edit-custom-audience-modal').find('.format-field').empty().append(code.join('\n'));
			break; 
		}
	});
	$('.edit-custom-audience-modal select[name="type"]').change();

	$('.edit-custom-audience-modal').on('change', 'select[name="source"]', function(){
		if($(this).val() == 'file') {
			$('.edit-custom-audience-modal .customer-list').hide();
			$('.edit-custom-audience-modal .data-info').hide();
			$('.edit-custom-audience-modal .customer-file').show();
			$('.edit-custom-audience-modal .file-info').show();
		} else {
			$('.edit-custom-audience-modal .customer-file').hide();
			$('.edit-custom-audience-modal .file-info').hide();
			$('.edit-custom-audience-modal .customer-list').show();
			$('.edit-custom-audience-modal .data-info').show();
		}
	});

	$('.edit-custom-audience-modal .btn-create').click(function(){
		var form = document.getElementById('edit-custom-audience-form');
		var action = '/userlib/update-custom-audience?fbUserId='+fbUserId+'&fbAccountId='+fbAccountId+'&audienceId='+$(form).find('input[name="id"]').val();
		var data = new FormData(form);
		data = new FormData();
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function() {
			if (xhr.readyState == 4) {
				if (xhr.status == 200) {
					try {
						var ret = JSON.parse(xhr.responseText);
						if (ret['info']) {
							alert(ret['info']);
						}
						if (ret['errors'] && ret['errors'].length > 0) {
							$.each(ret['errors'], function(i){
								alert(this);
							});
						}
						alert($.fn.d3_t('update custom audience over.'));
						loadFolder(currentFolderId, currentFolderName, parentFolderId);
					} catch(e) {
						alert($.fn.d3_t('some error occurs, please retry later.'));
					}
				} else {
					alert($.fn.d3_t('create failed, internet error.'));
				}
			}
		}
		xhr.open('POST',action);
		xhr.send(new FormData(form));
	});
	

	$('#search_audience').on('input',function(){
		var str = $(this).val().toLowerCase();
		var folders = {};
		var libs = {};
		if (str == '') {
			folders = FOLDERS;
			libs = LIBS;
		} else {
			$.each(FOLDERS, function(){
				if (this.name.toLowerCase().indexOf(str) >= 0) {
					folders[this.id] = this;
				}
			});
	
			$.each(LIBS, function(){
				if (this.name.toLowerCase().indexOf(str) >= 0) {				
					libs[this.id] = this;
				}
			});
		}
		buildBody(folders, libs);
	});
});
