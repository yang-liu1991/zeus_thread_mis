 ;$(function  () {
    $(window).on('load resize', function(event) {
        $('#mainCont').css('min-height', $('body').height() + 'px');
    });
	
});
function getJsonLength(jsonObj) {
	if (jsonObj == null) {
		return 0;	
	}
	var len = 0;
	$.each(jsonObj, function(){
		len++;
	});
	return len
}

function dicToArray(dic) {
	if (dic == null) {
		return [];	
	}
	var arr = [];
	$.each(dic, function(){
		arr.push(this);
	});
	return arr;
}
