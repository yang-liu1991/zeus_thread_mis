$(document).ready(function(){
	
	$.subscribe('click_detail_group_toggle', __toggleDetailGroup);
	function __toggleDetailGroup(target){
		var $detail_group = $(target).closest('.detail_group');
		var shouldExpand = $(target).hasClass('detail_expand');
		var _id = $detail_group.attr('id');
		if (shouldExpand) {
			$detail_group.addClass('detail_group_expanded');
			if (_id) $.cookie('D3.DG.' + _id, '1');
		}else{
			$detail_group.removeClass('detail_group_expanded')
			if (_id) $.cookie('D3.DG.' + _id, '0');
		}
	}

	
	$('dl.detail_group').each(function(i){
		var remembered = $.cookie('D3.DG.' + this.id) || '1';
		if (remembered == '1') {
			$.publish('click_detail_group_toggle', $(this).find('a.detail_expand')[0]);
		} else if (remembered == '0') {
			$.publish('click_detail_group_toggle', $(this).find('a.detail_close')[0]);
		}
	});
	$('.detail_toggler').click(function(e){
		$.publish('click_detail_group_toggle', $(this))
	})

	
})
