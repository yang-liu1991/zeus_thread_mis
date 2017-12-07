$(document).ready(function(){
	$('#delete_interest').click(function(e){
		$interests = $('input:checkbox[name="selection[]"]:checked');
		if ($interests.length == 0) {
			alert('remove d3 interest failed, no interest has been selected yet!');
			return;
		}
		var ids = [];
		$interests.each(function(i){
			ids.push($(this).val());
		});
		console.log(ids);
		$.ajax({
			type: 'POST',
			url: '/interest-manager/delete-interests',
			data: {ids: ids},
			dataType: 'json',
			success: function(ret) {
				if (!ret.success) {
					alert('remove d3 interest failed:'+ret.errors);
					return;
				} else {
					alert('remove interest successfully');
					location.reload();
				}
			},
			error: function(){
				alert('internet error,please retry.');
			}	
		});
	});
});
