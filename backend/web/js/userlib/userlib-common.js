$('.show_library_menu,.show_analytics_menu').hover(function() {
	var name=navname($(this));
	$('.'+name+'_nav').show().siblings('div').hide();

},function  () {
	$('header').mouseleave(function() {
		makeshow();
	});
});
function navname(obj) {
	var name=obj.attr('class').split('_')[1];
	return name;
}
makeshow();
$('.ad-manager').hover(function() {
	$('.header-nav div').hide();
}, function() {
	makeshow();
});
function makeshow () {
	var pageName=$('header').attr('data-page-name');
	if (pageName=='userlib') {
		pageName='library';
	};
	if ($('.header-nav .'+pageName+'_nav').length==0) {
		$('.header-nav').find('div').hide();
	}else{
		$('.header-nav .'+pageName+'_nav').show().siblings('div').hide();
	}
}