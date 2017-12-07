/* *
 * Jquery plugin: dateRange, 一个基于datepicker的选择时间段的弹框 jQuery plugin
 * 
 * liuwenbo@domob.cn 2015-08-05
 */
;(function($){
	$(document).on('click', '.daterange_show',__showSelector);
	$(document).on('click', '.ac_daterange_hide',__hideSelector);
	$(document).on('click', '.ac_daterange_select',__selectDates);
	$(document).on('click', '.ac_daterange_quick_range',__quickRange);

	function __showSelector(){
		var $t=$(event.target), 
			$dateRange=$('#dateRange'),
			scroll = $(window).scrollTop()+$(window).height(),
			tPos = $t.offset()
			;
		$dateRange.toggleClass('dateRange_expanded');
		$dateRange.before('<div class="date-wrap-modal"></div>');
		ENVOBJ.plugin.dateRange['serving'] = $t;
		tPos.width = $t.width();
		tPos.height = $t.height();
		if($t.is('button, input')){
			tPos.height = $t.parent().height();
			tPos.width = $t.parent().width();
		}
		if(scroll<(tPos.top+$dateRange.height()+tPos.height) && $dateRange.height()<tPos.top-$(window).scrollTop()){
			$dateRange.css("top", tPos.top - $dateRange.height() - 6); //up
		}else{
			$dateRange.css("top", tPos.top + tPos.height + 6); //down
		}
		if(document.body.clientWidth/2>tPos.left){
			// $dateRange.css("left", tPos.left); //left
			$dateRange.css("margin-left", tPos.left); //left-zjj-修改
		}else{
			$dateRange.css("left", tPos.left - $dateRange.width() +tPos.width); //right
		}

		$(window).trigger('resize');
	}
	function __hideSelector(){
		$('#dateRange').removeClass('dateRange_expanded');
		$('.date-wrap-modal').remove();
	}
	function __selectDates(){
		var startDate = $('#dateRange-date-start').val(),
			endDate = $('#dateRange-date-end').val(),
			$serving = ENVOBJ.plugin.dateRange['serving'],
			onSelect = $serving.data('onSelect')
			;
		$serving.data({
			'rangeStart':startDate,
			'rangeEnd':endDate
		}).val(startDate + $serving.data('separator') + endDate).change();

		if(typeof onSelect == "function"){
			onSelect.call($serving, startDate, endDate);
		}
		$.cookie('xs-time', JSON.stringify({sourse_start : startDate, sourse_stop : endDate}), {path: '/', secure:false});
		__hideSelector();
		$.publish('xs.user.changeDate');
		$('.date-wrap-modal').remove();
	}
	function __setDates(start, end){
		if(start){
			ENVOBJ.plugin.dateRange['start'].datepicker("setDate", start)
		}
		if(end){
			ENVOBJ.plugin.dateRange['end'].datepicker("setDate", end)
		}
		$('#dateRange-date-start-text span').html($('#dateRange-date-start').val());
		$('#dateRange-date-end-text span').html($('#dateRange-date-end').val());
		$.cookie('xs-time', JSON.stringify({sourse_start : $('#dateRange-date-start').val(), sourse_stop : $('#dateRange-date-end').val()}), {path: '/', secure:false});
	}
	function __dateDays(date, days){
		return new Date((new Date(date)).valueOf() + days*24*3600*1000)
	}
	function __quickRange () {
		var $t=$(event.target), now = new Date(), y, m;

		switch($t.data('range')){
			case "today":
				__setDates(now, now);
				break;
			case "yesterday":
				__setDates(__dateDays(now, -1), __dateDays(now, -1));
				break;
			case "7days":
				__setDates(__dateDays(now, -6), now);
				break;
			case "30days":
				__setDates(__dateDays(now, -30), now);
				break;
			case "thisMonth":
				__setDates(new Date(now.setDate(1)), new Date());
				break;
			case "lastMonth":
				if(now.getMonth() < 1){
					y = now.getFullYear() - 1;
					m = 11;
				} else{
					y = now.getFullYear();
					m = now.getMonth() - 1;
				}
				__setDates(new Date(y, m, 1), __dateDays(new Date(now.setDate(1)), -1));
				break;
			case "90days":
				__setDates(__dateDays(now,-90), now);
				break;
			case "halfyear":
				if(now.getMonth() < 6){
					y = now.getFullYear() - 1;
					m = now.getMonth() + 6;
				} else{
					y = now.getFullYear();
					m = now.getMonth() - 6;
				}
				__setDates(new Date(y, m, now.getDate()), now);
				break;
			case "thisYear":
				__setDates(new Date(now.getFullYear(), 0, 1), now);
				break;
		}
		__selectDates();
		$('.date-wrap-modal').remove();
	}
	function __dateWhenZero(date){
		var date = new Date(date);
		date.setHours(0);
		return date;
	}
	var __quickRangeTexts = {
	};
	function __buildQuickRanges (ranges) {
		var tmpHTML=[];
		for (var key in ranges) {
			if(ranges.hasOwnProperty(key))
				tmpHTML.push('<li><a href="javascript:void(0)" data-range="'+key+'">'+ranges[key].text+'</a></li>')
		};
		return tmpHTML.join('');
	}

	$.fn.extend({
		dateRange:function(setting){
			var today = __dateWhenZero(new Date()),
			setting = $.extend({
					"minDate": undefined,
					"maxDate": undefined,
					"changeMonth": false,
					"changeYear": false,
					"quickRanges": {
					},
					"initRange":{
						 "start": new Date(),  
						 "end": new Date()
					},
					"separator": ' - ',
					"onSelect": function(){}
				}, setting);
				var dateRangeInputs = this;


			function initSelector(){
				var tmpHTML=[
					'<div id="dateRange" class="modal">',
						'<div class="modal-dialog daterange-selector">',
							'<input type="hidden" id="dateRange-date-start">',
							'<input type="hidden" id="dateRange-date-end">',
							'<div class="modal-header">',
								'<ul class="ac ac_daterange_quick_range">',
									__buildQuickRanges(setting.quickRanges),
								'</ul>',
							'</div>',
							'<div class="modal-content">',
								'<div class="modal-body">',
									'<div class="pikers row">',
										'<div class="picker col-md-6">',
											'<h4 id="dateRange-date-start-text">Start Date：<span>2015-0-07</span></h4>',
											'<div id="dateRange-date-start-selector"></div>',
										'</div>',
										'<div class="picker col-md-6">',
											'<h4 id="dateRange-date-end-text">End Date：<span>2015-8-07</span></h4>',
											'<div id="dateRange-date-end-selector"></div>',
										'</div>',
									'</div>',
								'</div>',
								'<div class="modal-footer row"><div class="col-md-3"></div>',
									'<div class="col-md-7"><button class="btn btn-default btn-sm ac ac_daterange_hide">Cancel</button>',
									'<button class="btn btn-primary btn-sm ac ac_daterange_select">OK</button></div>',
								'</div>',
							'</div>',
						'</div>',
					'</div>'
				].join("\n");

				$(document.body).append(tmpHTML);
				//时间
				var $startDateTextBox = $('#dateRange-date-start-selector');
				var $endDateTextBox = $('#dateRange-date-end-selector');
				$startDateTextBox.datepicker({
					inline: true,
					altField: $('#dateRange-date-start'),
					changeMonth: setting.changeMonth,
					changeYear: setting.changeYear,
					onSelect: function (selectedDateTime){
						$('#dateRange-date-start-text span').html($('#dateRange-date-start').val());
						if($('#dateRange-date-start').val() > $('#dateRange-date-end').val()){
							$endDateTextBox.datepicker('setDate', selectedDateTime);
							$('#dateRange-date-end-text span').html(selectedDateTime);
						}
					}
				});
				$endDateTextBox.datepicker({
					inline: true, 
					altField: $('#dateRange-date-end'),
					changeMonth: setting.changeMonth,
					changeYear: setting.changeYear,
					onSelect: function (selectedDateTime){
						$('#dateRange-date-end-text span').html($('#dateRange-date-end').val());
						if($('#dateRange-date-start').val() > $('#dateRange-date-end').val()){
							$startDateTextBox.datepicker('setDate', selectedDateTime);
							$('#dateRange-date-start-text span').html(selectedDateTime);
						}
					}
				});
				$('#dateRange-date-start-text span').html($('#dateRange-date-start').val());
				$('#dateRange-date-end-text span').html($('#dateRange-date-end').val());
				ENVOBJ.plugin = ENVOBJ.plugin || {};
				ENVOBJ.plugin.dateRange = ENVOBJ.plugin.dateRange || {};
				ENVOBJ.plugin.dateRange['start'] = $startDateTextBox;
				ENVOBJ.plugin.dateRange['end'] = $endDateTextBox;

				if(setting.initRange.start||setting.initRange.end){
						dateRangeInputs.each(function(){
							ENVOBJ.plugin.dateRange['serving'] = $(this);
							ENVOBJ.plugin.dateRange['start'].datepicker('setDate', setting.initRange.start);
                            ENVOBJ.plugin.dateRange['end'].datepicker('setDate', setting.initRange.end);
                         });
					__selectDates();
				}
			}
		
			if(!$('#dateRange').length){
				setTimeout(initSelector, 0);
			}
			return this.each(function(){
				var $this=$(this);
				$this.addClass('daterange_show').data({
					'rangeStart': setting.initRange.start,
					'rangeEnd': setting.initRange.end,
					'minDate':setting.minDate,
					'maxDate':setting.maxDate,
					'separator':setting.separator,
					'onSelect':setting.onSelect
				});
			});
		}
	});
})(jQuery);
