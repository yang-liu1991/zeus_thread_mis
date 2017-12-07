window.ENVOBJ = {
};

$(document).ready(function() {
	function dateDays(date, days){
		return new Date((new Date(date)).valueOf() + days*24*3600*1000);
	}
	var dateTable=function($dom) {
		var today=new Date();
		var quickRanges = {
			'today': {text:"今天"},
			'yesterday': {text:"昨天"},
			'7days': {text:"近7天"},
			'30days': {text:"近30天"},
			'90days': {text:"近90天"},
			'thisMonth': {text:"本月"},
			'lastMonth': {text:"上个月"},
			'halfyear' : {text:"半年"},
			'thisYear' : {text:"今年"},
		};
		var time;
		if($.cookie('xs-time')){
			var range = JSON.parse($.cookie('xs-time'));
			time = {
				start : new Date(range.sourse_start), 
				end : new Date(range.sourse_stop)
			};
		} else {
			time = {
				start : dateDays(today, -6), 
				end : today
			};
			$.cookie('xs-time', JSON.stringify({sourse_start : time.start.toLocaleDateString(), sourse_stop : time.end.toLocaleDateString()}), {path: '/', secure:false});
		}

		$dom.dateRange({
			quickRanges: quickRanges,
			initRange: time,
			changeMonth: true,
			changeYear: true,
			onSelect: function(){},
		});
	};
	dateTable($('#datepicker'));
});
