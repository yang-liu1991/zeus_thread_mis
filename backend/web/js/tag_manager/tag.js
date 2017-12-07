$(document).ready(function(){
	//好像并没有用的样子
	function initTagInputField() {
		$('#tag_id').val(0);
		$('#tag_name').val('');
		$('#tag_description').val('');
		$('#tag_parent_id').val(0);
		$('#parent_tag_field').empty().append('<label class="label label-info">No parent</label>');		
	}

	$('#save_tag').click(function(e) {
		var tagId = $('#tag_id').val();
		var tagName = $('#tag_modal').find('.tag_name').val();
		var tagDescription = $('#tag_modal').find('.tag_description').val();
		var parentTagId = $('#tag_modal').find('.tag_parent_id').val();
		if (tagName == '') {
			alert('tag name should not be empty');
			return;
		}	
		var tag = {
			id: tagId,
			name: tagName,
			description: tagDescription,
			parent_id: parentTagId,
		};
		$('#tag_modal').find('.save_tag').empty().text('Creating').attr('disabled', true);

		$.ajax({
			type: 'POST',
			url: $('#save_tag').attr('action') == 'create' ? '/tag-manager/create-tag' : '/tag-manager/update-tag',
			data: {tag: tag},
			dataType: 'json',
			success: function(ret) {
				if (ret.errors != '') {	
					alert('save tag failed, error reason: ' + ret.errors);
					$('#tag_modal').find('.save_tag').empty().text('Save').attr('disabled', false);
					return;
				} else {
					alert('add tag success!');
					$('#tag_modal').find('.save_tag').empty().text('Save').attr('disabled', false);
					$('#tag_modal').modal('hide');
					location.reload();
					
				}
			},
			error: function() {
				alert('internet error, please retry.');
				$('#tag_modal').find('.save_tag').empty().text('Save').attr('disabled', false);
			}
		});
	});

	$('#remove_tags').click(function(e){
		var $tags = $('input:checkbox[name="selection[]"]:checked');
		var ids = [];
		$tags.each(function(i){
			ids.push($(this).val());
		});
		if (ids.length == 0) {
			alert('remove tags failed, no tag has been selected yet!');
			return;
		}
		$.ajax({
			type: 'POST',
			url: '/tag-manager/delete-tags',
			data: {ids: ids},
			dataType: 'json',
			success: function(ret) {
				if (ret.errors != '') {
					alert('remove tags failed, '+ret.errors);
					return;
				} else {
					alert('remove tags success');
				}
			},
			error: function() {
				alert('internet error, please retry.');
				return;
			}
		});
	});

	$('#create_tag').click(function(e){
		$('#tag_id').val(0);
		$('#save_tag').attr('action','create');	
		$('#tag_modal').modal('show');
	});

	$('#update_tag').click(function(e){
		var $tags = $('input:checkbox[name="selection[]"]:checked');
		if ($tags.length == 0) {
			alert('Edit tag failed, no tag has been selected yet!');
			return;
		}
		var ids = [];
		ids.push($tags.val());
		$.ajax({
			type: 'POST',
			url: '/tag-manager/get-tags?ids='+ids,
			data: {},
			dataType: 'json',
			success: function(ret) {
				if (!ret.success) {
					alert('Read tag failed, please retry.');
					return;
				}
				var tag = ret.tags[ids[0]];
				
				$('#tag_id').val(tag.id);
				$('#tag_name').val(tag.name);
				$('#tag_description').val(tag.description);
				$('#tag_parent_id').val(tag.parent_id);
				$('#parent_tag_field').empty().append('<label class="label label-info">'+tag.parent_name+'</label>');	
				$('#save_tag').attr('action', 'update');
	
/*
				var html = [];
				html.push(													   
					'<div class="row">',										 
						'<input type="hidden" class="tag_id">',
						'<div class="col-sm-12">',							   
							'<label class="col-sm-2 control-label">Tag Name</label>',
							'<div class="col-sm-8">',							
								'<input type="text" value="'+tag.name+'" class="tag_name form-control">',			  
							'</div>',
						'</div>',												
						'<div class="col-sm-12">',							   
							'<label class="col-sm-2 control-label">Description</label>',	   
							'<div class="col-sm-8">',							
								'<input type="text" value="'+tag.description+'" class="tag_description form-control">', 
							'</div>',											
						'</div>',												
						'<div class="col-sm-12">',							   
							'<label class="col-sm-2 control-label">Parent</label>',			
							'<div class="col-sm-8">',							
								'<input type="hidden" class="tag_parent_id" value="'+tag.parent_id+'">',	  
								'<label class="label label-info">'+(tag.parent_name == '' ? 'No Parent' : tag.parent_name)+'</label>',					
								'<button type="button" class="btn-xs btn-success pull-right" class="change_parent">Change</button>',							  
							'</div>',											
						'</div>',												
					'</div>'													 
				);
				$('#edit_tag_modal').find('.modal-body').empty().append(html.join("\n"));
*/
				$('#tag_modal').modal('show');
			},
			error: function() {
				alert('internet error, please retry.');
				return;
			}
		});
		
	});


	$('body').on('click', '.open_tag_tree', function(e){
		$.ajax({
			type: 'GET',
			url: '/tag-manager/get-all-tags',
			data: {},
			dataType: 'json',
			success: function(ret) {
				$('#tag_js_tree_container').jstree({
					core: {
						data: ret.tags,
						multiple: false,
					},
				});
			},
			error: function() {
				alert('internet error,plz retry.');
				return;
			}
		});
	});

	$('body').on('click', '#save_parent_tag', function(e){
		var jsTree = $('#tag_js_tree_container').jstree(true);
		var parentTag = jsTree.get_selected(true);
		if (parentTag.length == 0) {
			return;
		}
		parentTag = parentTag[0];
		$('#tag_parent_id').val(parentTag.id);
		$('#parent_tag_filed').empty().append('<label class="label label-info">'+parentTag.text+'</label>');
		$('#tag_tree_modal').modal('hide');
	});
	
});
