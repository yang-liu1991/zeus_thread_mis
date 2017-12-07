$(document).ready(function(){
	var formName = $('#d3_interest_form').attr('formName');

	//调整suggestion框的位置。
	$('#interest_suggestions').css('left', $('#interests_search').parent().position().left);
	$('#interest_suggestions').css('width', $('#interests_search').css('width'));
	$('#interest_suggestions').css('top', $('#interests_search').css('height'))

	var suggestList = null;
	$('#suggest_btn').click(function(e){
		$('#interests_search').val('').autocomplete().hide();
		$('#interest_suggestions').empty().append('loading...');

		var interests = $('#interests').val();
		interests = interests == '' ?  {} : JSON.parse(interests);
		var data = [];
		$.each(interests, function(i){
			data.push(interests[i].name);
		});
		$.ajax({
			type: 'GET',
			url: '/api/get-interest-suggestion',
			data: {list: JSON.stringify(data)},
			dataType: 'json',
			success: function(ret) {
				var html = [];
				$.each(ret.data, function(i) {
					html.push(
						'<li interest_id = "'+ret.data[i].id+'"><a class="suggest" href="javascript:void(0)">'+ret.data[i].name+' - '+ret.data[i].audience_size+'</a></li>'
					);
				});
				suggestList = ret.data;
				if (html.length > 0) {
					$('#interest_suggestions').empty().append(html.join('\n'));
				}
			},
			error: function() {
				alert("Get interest suggestion from server failed.");
			},
		});
	});

	$('#interest_suggestions').on('click', '.suggest', function(e){
		var id = $(this).parent().attr('interest_id');
		var currentInterests = $('#interests').val();
		currentInterests = currentInterests == '' ? {} : JSON.parse(currentInterests);
		if (currentInterests[id] == null) {
			var interest = suggestList[id];
			currentInterests[id] = {
				id: id,
				name: interest.name,
			};

			var html = [];
			html.push(
				'<button type="button" class="interests" id="interests_'+interest.id+'">'+interest.name+' - '+interest.audience_size+'<span class="remove_interests" style="color:red">x</span></button>'
			);
			$('#interests_field').append(html.join('\n'));
			$('#interests').val(JSON.stringify(currentInterests));
		}
	});


	$('#interests_search').autocomplete({
		serviceUrl:'/api/auto-interests',
		paramName: 'term',
		showNoSuggestionNotice: true,
		noCache: false,
		deferRequestBy: 200,
		onSelect: function(select){									  
			var tmpHtml = [];
			var interests_id = select.data.id;							 
			var interests_name = select.data.name;						 
			var audience_size = select.data.audience_size;
				
			if ($('#interests_'+interests_id).length > 0) {
				$(this).val("");
				return;
			}
			tmpHtml.push(
				'<button type="button" class="interests" id="interests_'+interests_id+'">'+interests_name+' - '+audience_size+'<span class="remove_interests" style="color:red">x<span></button>'
			);
			$('#interests_field').append(tmpHtml.join(''));						 		
			var currentInterests = $('#interests').val();
			
			if (currentInterests == '' || currentInterests == []) {
				currentInterests = '{}';
			}
			currentInterests = JSON.parse(currentInterests);
			currentInterests[parseInt(interests_id)] = {
				id: interests_id,
				name: interests_name,
			};

			$('#interests').val(JSON.stringify(currentInterests));
			$(this).val("");													 
	
		},																	   
	});

	$('body').on('click', '.remove_interests', function(e){
		var interest_id = parseInt($(this).parent().attr("id").split('_')[1]);
		var currentInterests = JSON.parse($('#interests').val());
		delete currentInterests[interest_id];
		$('#interests').val(JSON.stringify(currentInterests));
		$(this).parent().remove();
	});

	$('body').on('click', '#add_tag', function(){
		$.ajax({
			type: 'GET',
			url: '/tag-manager/get-all-tags',
			data: {},
			dataType: 'json',
			success: function(ret) {
				$('#tag_js_tree_container').jstree({
					core: {
						data: ret.tags,
					},
					checkbox: {
						//visible: false,
						three_state: false,
					},
					plugins: ["checkbox"],
				});
			},
			error: function() {
				alert('internet error, plz retry.');
				return;
			}
		});
	});

	$('body').on('click', '#select_tag_tree', function(e){
		var jsTree = $('#tag_js_tree_container').jstree(true);
		var selectedTags = jsTree.get_selected(true);
		
		var currentTags = $('#tag_ids').val();
		currentTags = currentTags == '' ? [] : currentTags.split(',');
		$.each(selectedTags, function(i){
			if ($.inArray(this.id.toString(), currentTags) < 0) {
				currentTags.push(this.id.toString());
				var html = [];
				html.push(
					'<button type="button" class="tag" tag_id="'+this.id+'">'+this.text+'<span class="remove_tag" style="color:red">x</span></button>'
				);	
				$('#tags_field').append(html.join('\n'));
			}
		});
		
		$('#tag_ids').val(currentTags.join(','));
		$('#tag_tree_modal').modal('hide');
	});

	$('body').on('click', '.remove_tag', function(e){
		var tagId = $(this).parent().attr('tag_id');
		var currentTags = $('#tag_ids').val().split(',');
		currentTags.splice($.inArray(tagId, currentTags), 1);
		
		currentTags = currentTags.length == 0 ? '' : currentTags.join(',');
		$('#tag_ids').val(currentTags);

		$(this).parent().remove();
	});

	$('#auto_fill_suggestions').click(function(e){
        var interests = $('#interests').val();
        interests = interests == '' ?  {} : JSON.parse(interests);
        var data = [];
        $.each(interests, function(i){
            data.push(interests[i].name);
        });
        $.ajax({
            type: 'GET',
            url: '/api/get-interest-suggestion',
            data: {list: JSON.stringify(data)},
            dataType: 'json',
            success: function(ret) {
				var html = [];
				var limit = 5;
                $.each(ret.data, function(i) {
					var interest = ret.data[i];
					if (interest.audience_size < 50000 || limit <= 0) {
						//continue;
						return true;
					}
					var id = interest.id;
					if (interests[id] == null) {
						interests[id] = {
							id: id,
							name: interest.name,
						};
						html.push(
							'<button type="button" class="interests" id="interests_'+interest.id+'">'+interest.name+' - '+interest.audience_size+'<span class="remove_interests" style="color:red">x</span></button>'
						);
					}
					limit--;
                });

				$('#interests_field').append(html.join('\n'));
				$('#interests').val(JSON.stringify(interests));
            },
            error: function() {
                alert("Get interest suggestion from server failed.");
            },
        });
	});

	$('#clear_interests').click(function(e){
		$('#interests_field').empty();
		$('#interests').val('{}');
	});

});
