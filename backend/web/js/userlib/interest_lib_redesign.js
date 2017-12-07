var $CONTAINER = null;
var currentFolderId = 0;
var currentFolderName = '';
var parentFolderId = currentFolderId;
var parentFolderName = currentFolderName;
var folderPath = null;
var FOLDERS = {};
var LIBS = {};

function clearAddInterestModal() {
	$('.add-new-modal input[name="name"]').val('');
	$('.add-new-modal input[name="include"]').val('{}');
	$('.add-new-modal input[name="exclude"]').val('{}');
	$('.add-new-modal .interests').empty();
	$('.add-new-modal input[name="id"]').val(0);
}

//field,items: selector
function bindInterestTooltips(field, items) {
	if ($(field).tooltip( "instance" )) {
		$(field).tooltip("destroy");
	}
	$(field).tooltip({
		items: items,
		content: function() {
			return $(this).attr('tooltip');
		},
		position: {
			my:'left top', 
			at: 'right+10 top-5',
			using: function(position, feedback) {
				$(this).css(position);
				$(this).addClass('at-left');
			}
		}
	});
}

function getInterestTooltip(interest) {
	var html = [];
	if (!interest.path) {
		interest.path = [];	
	}
	if (!interest.audience_size) {
		interest.audience_size = $.fn.d3_t('unknown');	
	}
	html.push(
		'<div style=\'width:300px;white-space:normal\'>',
			'<span>'+$.fn.d3_t('{size} people.', {size: interest.audience_size})+'</span>',
			'<br><br>',
			'<span>'+interest.path.join(' >> ')+'</span>',
		'</div>'
	);
	return html.join('');
}

function getFacebookInterestCode(interest) {
	var html = [];
	var tooltip = getInterestTooltip(interest);
	html.push(
		'<a class="facebook_interests btn btn-stable btn-sm" interest_id='+interest.id+' tooltip="'+tooltip+'">',
			interest.name,
			'<i class="remove_interest icon-close"></i>',
		'</a>'
	);
	return html.join('\n');
}

function renderZeusInterest(interestLib) {
	var includeHtml = [];
	var excludeHtml = [];
	var include = {};
	var exclude = {};
	$('.add-new-modal input[name="id"]').val(interestLib.id);
	$('.add-new-modal input[name="name"]').val(interestLib.name);
	if (interestLib.interests.include) {
		$.each(interestLib.interests.include, function(){
			includeHtml.push(getFacebookInterestCode(this));
			include[this.id] = this;
		});
	}
	if (interestLib.interests.exclude) {
		$.each(interestLib.interests.exclude, function(){
			excludeHtml.push(getFacebookInterestCode(this));
			exclude[this.id] = this;
		});
	}
	$('.add-new-modal input[name="include"]').val(JSON.stringify(include));
	$('.add-new-modal input[name="exclude"]').val(JSON.stringify(exclude));
	$('.add-new-modal .include-interest-group .interests').append(includeHtml.join('\n'));
	$('.add-new-modal .exclude-interest-group .interests').append(excludeHtml.join('\n'));
	bindInterestTooltips('.add-new-modal .interests', '.facebook_interests');
}

function editInterest(id) {
	clearAddInterestModal();
	$('.add-new-modal .subnav h2').text($.fn.d3_t('Update D3 Interest'));
	$('.add-new-modal').modal();

	$.ajax({
		type: 'get',
		url: '/userlib/get-interest-lib?ids=' + id,
		success: function(ret) {
			ret = JSON.parse(ret);
			if (ret.errors != '') {
				alert(ret.errors);
				return;
			}
			var interestLib = ret.data[id];
			renderZeusInterest(interestLib);
			
		},	
		error: function() {
			alert($.fn.d3_t('internet error, please retry later.'));
			return;
		}
	});	
}
$(document).ready(function($) {
	$CONTAINER = $('.adTxt-table');
	currentFolderId = 0;
	currentFolderName = $.fn.d3_t('Interest');
	parentFolderId = currentFolderId;
	parentFolderName = currentFolderName;
	folderPath = [{name: currentFolderName, id: 0}];
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

	function buildBody(folders, libs) {
				var HtmlCode = [];
				$.each(folders, function() {
					HtmlCode.push(
						'<tr>',
							'<td><input type="checkbox" folder_id="' + this.id + '" class="folder_check item_check"><a href="###" class="folder-tit" redirect="' + this.id + '"><i class="icon icon-folder"></i>' + this.name + '</a></td>',
							'<td>' + $.fn.d3_t('Folder') + '</td>',
							'<td>--</td>',
							'<td>' + this.create_time + '</td>',
							'<td>' + this.update_time + '</td>',
							'<td></td>',
						'</tr>'
					)
				});
				$.each(libs, function() {
					HtmlCode.push(
						'<tr>',
							'<td><input type="checkbox" lib_id="' + this.id + '" class="lib_check item_check">' + this.name + '</td>',
							'<td>' + $.fn.d3_t('Interest') + '</td>',
							'<td>' + this.keywords + '</td>',
							'<td>' + this.create_time + '</td>',
							'<td>' + this.update_time + '</td>',
							'<td><a class="update-link" href="javascript:void(0)" onclick="editInterest(' + this.id + ')">' + $.fn.d3_t('Update') + '</a></td>',
						'</tr>'
					)
				});
				$CONTAINER.find('.table-body').find('table').find('tbody').empty().append(HtmlCode.join("\n"));
				buildFolderMenu();
	}

	//打开新文件夹，重新生成页面
	function loadFolder(folderId, folderName, parentId) {
		if (folderId == 0) {
			folderName = $.fn.d3_t('Interest');
		}
		$.ajax({
			type: 'GET',
			url: '/userlib/open-lib-folder?type=interest&target='+folderId,
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
				FOLDERS = {};
				LIBS = {};
				$.each(ret.folders, function(){
					FOLDERS[this.id] = this;
				});
				$.each(ret.libs, function(){
					LIBS[this.id] = this;
				});
				buildBody(ret.folders, ret.libs);
				
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
			url: '/userlib/move-libs?type=interest',
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
		$('#search_interest').val('');
		loadFolder(parseInt($(this).attr('redirect')), $(this).attr('redirect_name'));
	});	

	$('body').on('click', 'a.folder-tit', function(e){
		$('#search_interest').val('');
		loadFolder(parseInt($(this).attr('redirect')), $(this).text(), currentFolderId);
	});

/***********************************/
			  $('.suggest-cont').hide();
			  $('.btn-suggest').on('click', function() {
				
				$('.suggest-cont').toggle();
				$('.suggest-cont p').on('click', function() {
				  $(this).parents('.suggest-cont').hide().siblings('input').attr('placeholder', $(this).text());
				});
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
			 $('.addfolder-btn').on('click', function() {
				$('tr.new_folder').remove();
				$('<tr class="new_folder"><td colspan="5" class="show-user-cont"><input type="checkbox"><a href="javascript:void(0)" title=""><i class="icon icon-folder"></i></a><input class="type-cont" type="text" value="new folder" placeholder="input a folder name"><a href="javascript:void(0)" class="btn btn-sm btn-save" style="margin-right: 1px;">'+$.fn.d3_t('save')+'</a><a href="javascript:void(0)" class="btn btn-sm btn-cancel">'+$.fn.d3_t('cancel')+'</a></td></tr>').prependTo('tbody');
				$('.btn-cancel').on('click', function() {
					$(this).parents('tr').remove();
				});
				$('.btn-save').on('click', function() {
					var foldername = $(this).prev('.type-cont').val();
					var data = {name: foldername, parentId: currentFolderId, _csrf: $('#_csrf').val()};
					$.ajax({
						type: 'POST',
						url: '/userlib/create-lib-folder?type=interest',
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
			  $('.btn-close').on('click',function  () {
				  $(this).closest('.modal').modalHide();
			  });
			  $('.addinterest-btn').on('click',function  () {
				  clearAddInterestModal();
				  $('.add-new-modal .subnav h2').text($.fn.d3_t('Create D3 Interest'));
				  $('.add-new-modal').modal();
			  });
			$('.delete_btn').on('click',function  () {
				if ($('table tbody td>input.item_check:checked').length == 0) {
					alert($.fn.d3_t('please select at least one audience or folder'));
					$('.delete_ensure_modal').modalHide();
					return;
				} else {
					$('.delete_ensure_modal').modal();
				}
			});
/*****custom******/
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
					url: '/userlib/delete-lib?type=interest',
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
		if ($('table tbody td>input:checked').length == 0) {
			alert($.fn.d3_t('please select at least one audience or folder.'));
			return;
		} else {
			$('.move-modal.modal').modal();
		}
		$.ajax({
			type: 'GET',
			url: '/userlib/get-folder-tree?type=interest',
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

	function getCurrentFacebookInterests() {
			var include = $('.add-new-modal [name="include"]').val();
			var exclude = $('.add-new-modal [name="exclude"]').val();
			if (include == '') {
				include = '{}';
			}
			if (exclude == '') {
				exclude = '{}';
			}
			var interests = {
				include: JSON.parse(include),
				exclude: JSON.parse(exclude),
			}
			return interests;
	}

	$('.add-new-modal .interest_search').each(function() {
		var groupId = $(this).attr('group_id');
		var containerSelector = '.'+groupId+'-interest-group';
		$(this).autocomplete({
			serviceUrl: function() {
				var url = '/api/auto-interests?';
				if ($(this).autocomplete().queryInfo.isBatchQuery) {
					url += 'check_list=true';
				}
				url += ($.cookie('language') ? '&lang='+$.cookie('language') : '');
				return url;
			},
			paramName: 'term',
			showNoSuggestionNotice: true,
			noCache: false,
			deferRequestBy: 200,
			appendTo: $(containerSelector + ' .interest_autocomplete_field'),
			muilt_select: true,
			querySplit: ',',
			autoSelect: 'selectAll',
			batchQuery: true,
			formatResult: function(suggestion, query) {
				var interest = suggestion.data;
				var tooltip = [];
				tooltip.push(
					'<div style=\'width:300px;white-space:normal\'>',
						'<span>'+$.fn.d3_t('{size} people.', {size: interest.audience_size})+'</span>',
						'<br><br>',
						'<span>'+interest.path.join(' >> ')+'</span>',
					'</div>'
				);
				return '<span style="display:block;width:100%" class="suggestion_has_tooltip" tooltip="'+tooltip.join('')+'">'+interest.name+'</span>';
			},
			onSearchComplete: function(query, suggestions) {
				$('.add-new-modal .interest_autocomplete_field').tooltip({
					items: '.add-new-modal .suggestion_has_tooltip',
					content: function() {
						return $(this).attr('tooltip');
					},
					position: {
						my:'left top',
						at: 'right+10 top-5',
						using: function(position, feedback) {
							$(this).css(position);
							$(this).addClass('at-left');
						}
					}
				});
			},
			onSelect: function(selectedItem,e){
				var tmpHtml = [];
				var interests_id = selectedItem.data.id;
				var interests_name = selectedItem.data.name;
				var interests_audience_size = selectedItem.data.audience_size;

				var tooltip = getInterestTooltip(selectedItem.data);
				var groupId = $(this).attr('group_id');
				tmpHtml.push(
					'<a tooltip="'+tooltip+'" class="facebook_interests btn btn-stable btn-sm" interest_id="'+interests_id+'">'+interests_name+'<i  class="remove_interest icon-close"></i></a>'
				);
				currentInterests = getCurrentFacebookInterests();
				currentInterests = currentInterests[groupId];
				if (currentInterests[parseInt(interests_id)] == null) {
					$('.'+groupId+'-interest-group .interests').append(tmpHtml.join(''));
					bindInterestTooltips('.add-new-modal .interests', '.facebook_interests');
					currentInterests[parseInt(interests_id)] = {id:  interests_id, name: interests_name, audience_size: interests_audience_size};
					$('.add-new-modal').find('input[name="'+groupId+'"]').val(JSON.stringify(currentInterests));
				}
				$(this).val("");

			},
		});
	});
	
	$('body').on('click', '.remove_interest', function(){
		var interestId = $(this).parent().attr('interest_id');
		var groupId = $(this).closest('.interest-group').attr('group_id');
		var currentInterests = getCurrentFacebookInterests();
		currentInterests = currentInterests[groupId];
		delete currentInterests[interestId];
		$('.add-new-modal').find('input[name="'+groupId+'"]').val(JSON.stringify(currentInterests));
		$(this).parent().remove();
	});

	$('.add-new-modal .create').click(function(){
		var id = $('.add-new-modal input[name="id"]').val();
		var name = $('.add-new-modal').find('input[name="name"]').val();
		var fbInterests = getCurrentFacebookInterests();
		if (name == '' || fbInterests == '') {
			alert($.fn.d3_t('Name and facebook interests are both necessary to create D3 interest!'));
			return;
		}
		var interestData = {
			name: name,
			group_id: currentFolderId,
			interests: fbInterests,
			audience_size: 0,
		};
		if (id > 0) {
			interestData.id = id;
		}
		var data = {
			data: interestData,
			_csrf: $('#_csrf').val(),
		}
		$.ajax({
			type: 'POST',
			url: '/userlib/add-interest-group',
			data: data,
			success: function(ret) {
				ret = JSON.parse(ret);
				var data = ret.data;
				if (data.error != '') {
					alert("Save interest failed，"+data.error);
					return;
				}
				$('.add-new-modal .btn-create').html('Save').removeAttr('disabled');
				loadFolder(currentFolderId, currentFolderName, parentFolderId);

				alert('Save interest successfully！');
				$('.add-new-modal').modalHide();
			},
			error: function() {
				$('.add-new-modal .btn-create').html('Save').removeAttr('disabled');
			}
		});
	});

	var suggestList = null;
	$('.btn-suggest').click(function(e){
		$('.interest_search').val('').autocomplete().hide();
		$('.suggest-cont').empty().append('loading...');


		var data = [];
		var currentInterests = getCurrentFacebookInterests();
		$.each(currentInterests.include, function(i){
			data.push(this.name);
		});
		$.ajax({
			type: 'GET',
			url: '/api/get-interest-suggestion'+($.cookie('language') ? '?lang='+$.cookie('language') : ''),
			data: {list: JSON.stringify(data)},
			dataType: 'json',
			success: function(ret) {
				var html = [];
				$.each(ret.data, function(i) {
					var tooltip = getInterestTooltip(this);
					html.push(
						'<p tooltip="'+tooltip+'" interest_id = "'+ret.data[i].id+'" class="suggest suggestion_has_tooltip">'+ret.data[i].name+'</p>'
					);
				});
				suggestList = ret.data;
				if (html.length > 0) {
					$('.suggest-cont').empty().append(html.join('\n'));
					bindInterestTooltips('.suggest-cont', '.suggest-cont .suggestion_has_tooltip');
				}
			},
			error: function() {
				alert("Get interest suggestion from server failed.");
			},
		});
	});

	$('.suggest-cont').on('click', '.suggest', function(e){
		var id = $(this).attr('interest_id');
		var interest = suggestList[id];

		var interests_id = interest.id;
		var interests_name = interest.name;
		var interests_audience_size = interest.audience_size;

		var tmpHtml = [];
		var currentInterests = getCurrentFacebookInterests();
		currentInterests = currentInterests.include;
		if (currentInterests[parseInt(interests_id)] == null) {
			var tooltip = getInterestTooltip(interest);
			tmpHtml.push(
				'<a tooltip="'+tooltip+'" class="facebook_interests btn btn-stable btn-sm" interest_id="'+interests_id+'">'+interests_name+':'+interests_audience_size+'<span  class="remove_interest icon-close" ></i></a>'
			);
			currentInterests[parseInt(interests_id)] = interest;
			$('.add-new-modal').find('input[name="include"]').val(JSON.stringify(currentInterests));
		}

		$('.include-interest-group .interests').append(tmpHtml.join(''));
		bindInterestTooltips('.add-new-modal .interests', '.facebook_interests');
		$(this).hide();
	});
	
	$(document).click(function(){
		if (!$(event.srcElement).is('.suggest-cont,.btn-suggest,.suggest')) {
			$('.suggest-cont').hide();
		}	
	});

	//for search
	$('#search_lib').on('input',function(){
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
				if (this.name.toLowerCase().indexOf(str) >= 0 || this.keywords.toLowerCase().indexOf(str) >= 0) {
					libs[this.id] = this;
				}
			});
		}
		buildBody(folders, libs);
	});
});
