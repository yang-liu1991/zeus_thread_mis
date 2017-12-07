;
(function($){
	$(document).ready(function(){
/*
		$('select.d-lang').change(function(){
			var language = $(this).val();
			$.cookie('language',language);
			window.location.reload();
		});
*/
		$('.d-lang').find('a').click(function(){
			if ($(this).attr('lang') == 'en') {
				$.cookie('language', 'en');
				//console.log('en',$.cookie('language'));
			} else if ($(this).attr('lang') == 'zh-CN') {
				$.cookie('language', 'zh-CN');
				//console.log('zh-CN', $.cookie('language'));
			}
			window.location.reload();
		})
		//console.log($.cookie('language'));


		$(document.body).on('error', 'input,select,textarea', function(e, msg){
			var $t = $(getTarget(e)).closest('.form-group'), $control = $(getTarget(e));

			if($t.length)$t.addClass('has-error'); 
			var $line = $control.parent();
			if( $line.prop('className').indexOf('group')>-1){
				$line = $line.parent();
			}
			$line.append([
				'<div class="alert alert-danger" role="alert">',
					'<i class="icon-danger"></i> ',
					(msg || $control.data('error')), 
				'</div>'
			].join("\n"));
		});
		if(typeof($.fn.tooltip) != "undefined"){
			$(document).tooltip({
				items : '.xs-tooltip',
				position : {
					my: "center bottom-10",
					at: "center top",
					using: function(position, feedback) {
						$(this).css(position);
						$(this).addClass('at-bottom')
					}
				},
				content: function(){
					return $(this).attr('tooltip');
				},
				show : false,
				hide : false,
			});
			/*
			$('.xs-tooltip').tooltip({
				items : '.xs-tooltip',
				position : {my: "center bottom-10", at: "center top"},
				content: function(){
					return $(this).attr('tooltip');
				},
			}).eq(0).tooltip('open');
			*/
		}
	 });
})(jQuery);
var common = common || {};
(function(common){
	common = common || {};
	var isFunction = function(obj){
		return (typeof(obj) == 'function');
	}

	common.bindRight = function(targetList){
		$.each(targetList, function(i, value){
			$('#' + i).change(function(event){
				if(isFunction(value)){
					return value(event);
				}else{
					$('#' + i + '_v').html($('#' + i).val());
				}
			});
			$('#' + i).trigger('change');
		});
	}

	common.goto = function(uri){
		window.location.href = window.location.origin + uri;
	}

	common.debug = function(data){
		console.log(data);
	}
})(common);
$.fn.d3_t = function(str, params) {
	if (typeof(str) != 'string') {
		console.log('translate failed',str,params);
		return '';
	}
	if ($.fn.language_dic == null) {
		$.fn.language_dic = {};	
	}
	var result = $.fn.language_dic[str];
	if (result == null) {
		result = str;
	}
	for(var key in params) {
		result = result.replace('{'+key+'}', params[key]);
	}
	return result;
}

$.fn.extend({
	modal:function (options) {
		if(!$('.modal-backdrop').hasClass('modal-backdrop')){
			$(document.body).append('<div class="modal-backdrop"></div>');
		}
		$(document.body).css('padding-right','17px').addClass('modal-open');
		$(this).show();
		return this;
	},
	modalHide:function () {
		$('.modal-backdrop').remove();
		$(document.body).css('padding-right','').removeClass('modal-open');
		$(this).hide();
		return this;
	}
});


//验证
function isValid($t){
	$t = $t.jquery? $t: $($t);
	var validateIf = $t.data('validateif') || $t.data('validate-if');
	if(validateIf){
		try{
			var validateOrNot = eval(validateIf);
			if(!validateOrNot)return true;
		}catch(e){console.log(e)}
	}
	return (new RegExp($t.data('validate'))).test($t.val());
}
//e: event
function getTarget (e) {
	return e.target || e.srcElement || e.originalTarget;
}

function xs_formate_float(num, append){	
	var str = String(num);
	if(str === 'N/A'){
		return num;
	}
	if(append){
		return parseFloat(num * 100).toFixed(2) + append;
	} else {
		return parseFloat(num * 100).toFixed(2);
	}
}
function getObjectKeys(object) {
	var keys = [];
	for (var property in object)
	  keys.push(property);
	return keys;
}

function getObjectValues(object) {
	var values = [];
	for (var property in object)
	  values.push(object[property]);
	return values;
}
