$(document).ready(function() {
	resMargin();
    $(window).resize(function  () {
        resMargin();
    });
    $('.calcul-sel').on('click', function() {
        $('.cal-dropDown').toggleClass('hidden');
    });
    $('body').on('click', '.date-wrap-modal', function() {
    	$('#dateRange').removeClass('dateRange_expanded');
    	$(this).remove();
	});
	$('.xs-graph :checkbox').change(function(){
		var checked = $(this).prop('checked');
		var li = $('#imginfos li[data-type="'+$(this).val()+'"]');
			if(checked){
				li.show();
				$('.xs-graph', li).highcharts(get_options($(this).val()));
			} else {
				li.hide();
			}
		resMargin();
	});
	$('#datepicker, #account-name').on('change',function() {
		loadData();
	});
});
    // 与最高li的高度保持一致
    function resetHeight () {
    	for (var i = 1; i < $('#imginfos>li').length; i++) {
	    	var currHei=$('#imginfos>li').eq(i-1).height();
	    	var nextHei=$('#imginfos>li').eq(i).height();
            var tmp;
            if (currHei>nextHei) {
            	tmp=currHei;
            }else{
            	tmp=nextHei;
            }
            $('#imginfos>li').height(tmp+'px');
    	}
    }

    // 设置图片边距
    function resMargin() {
        var visibleLi=$('#imginfos>li:visible');
        var borderVal=parseInt(visibleLi.eq(0).css('border-width'))*2||0;
        var ml=$('#imginfos').width()-(visibleLi.width()+borderVal)*3.22;
        if ($('#imginfos').width()<visibleLi.width()*4) {
            $('#imginfos').css('min-width', visibleLi.width()*4);
            $('header,header>div').css('min-width', visibleLi.width()*4);
        }
        var imginfoLen=visibleLi.length;
        for (var i = 1; i < imginfoLen; i++) {
            visibleLi.eq(i*3-1).css({
                 'margin-right':'0'
             });
             visibleLi.eq(i*3-2).css({
                 'margin-right': Math.floor(ml/2)+'px'
             });
             visibleLi.eq(i*3-3).css({
                 'margin-right': Math.floor(ml/2)+'px'
             });
        }
    }

	function getDataType(){
		//var flag = $('#xs_table tbody tr').length < 1;
		$('#imginfos li').each(function(){
			//if(flag) $(this).hide();
			$('.xs-graph', this).highcharts(get_options($(this).attr('data-type')));
		});
	}

	function get_data(index, num){
		var txt, data = [];
		$('#xs_table tbody tr').each(function(i){
			if(i< num){
				txt = $('td', this).eq(index).text();
				if(txt === 'N/A'){
					data.push([$('td', this).eq(0).text(), 0]);
				} else {
					data.push([$('td', this).eq(0).text(), parseFloat(txt, 10)]);
				}
			}
		});
		return data;
	}

	function get_options(field){
		var data = [];
		var num = 10;
		var option = {
			chart: {
				type: 'column'
			},
			credits : {
				enabled : false,
			},
			title: {
				text: ''
			},
			subtitle: {
				text: ''
			},
			lang : {
				noData : '暂无数据',
			},
			noData : {
				style: {
					fontWeight: 'bold',
					fontSize: '15px',
					color: '#b1b1b8'
				},
			},
			xAxis: {
				type: 'category',
				labels: {
					rotation: -45,
					style: {
						fontSize: '13px',
						fontFamily: 'Verdana, sans-serif'
					}
				}
			},
			yAxis:{
				min : 0,
				title:{
					text:'',
				}
			},
			legend: {
				enabled: false
			},
			tooltip: {
				pointFormat: '{point.y:.1f}'
			},
		};
		if (field === 'ecpa'){
			data = get_data(2, num);
		} else if (field === 'spent'){
			data = get_data(4, num);
		} else if (field === 'roi'){
			data = get_data(5, num);
			option.yAxis = {
				title:{
					text:'',
				}
			}
		} else if (field === 'impressions'){
			data = get_data(6, num);
		} else if (field === 'ctr'){
			data = get_data(7, num);
		} else if (field === 'clicks'){
			data = get_data(8, num);
		} else if (field === 'cpc'){
			data = get_data(9, num);
		} else if (field === 'cpm'){
			data = get_data(10, num);
		} else if (field === 'cvr'){
			data = get_data(11, num);
		}
		option.series = [{
			name: '',
			data: data,
			dataLabels: {
				enabled: true,
				color: '#FFFFFF',
				align: 'center',
				format: '{point.y}', // one decimal
				y: 10, // 10 pixels down from the top
				style: {
					fontSize: '13px',
				}
			}
		}];
		if(field === 'mobile_app_install'){
			data = [];
			var other = 0;
			$('#xs_table tbody tr').each(function(i){
				if(i< 7){
					data.push([$('td', this).eq(0).text(), parseFloat($('td', this).eq(1).text())]);
				} else {
					other += parseFloat($('td', this).eq(1).text());
				}
			});
			if(other > 0){
				data.push(['other', other]);
			}
			option = {
				credits : {
					enabled : false,
				},
				chart: {
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false
				},
				title: {
					text: ''
				},
				tooltip: {
					pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
				},
				plotOptions: {
					pie: {
						allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: false
						},
						showInLegend: true
					}
				},
				series: [{
					type: 'pie',
					name: '',
					data : data,
				}],
			};
		}
		if (!data.length) {
			option.title = {
				text: '暂无数据',
				style: {
					fontWeight: 'bold',
					fontSize: '15px',
					color: '#b1b1b8'
				},
			};
		}
		return option;
	}

    resMargin();
