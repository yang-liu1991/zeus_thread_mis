;
$(function () {
	var dataStore;
	checkCalculations();
	$('body').on('click',function(event) {
		if (!$('.cal-dropDown').hasClass('hidden') && $(event.target).closest(".dropDown-coner").length === 0 && $(event.target).parents('.cal-dropDown').length === 0) {
			$('.cal-dropDown').addClass('hidden');
		};
		if ($(event.target).closest('#datepicker').get(0)!=$('#datepicker').get(0) && !$(event.target).closest('#dateRange').get(0)) {
			$('#dateRange').removeClass('dateRange_expanded');
		}
	}).on('click','.calcul-sel', function() {
		$('.cal-dropDown').toggleClass('hidden');
	}).on('change', '.cal-dropDown input,#calculations', function() {
		checkCalculations();
		if(getCategories().length > 0){
			render();
		}
	}).on('change','.notiframe-ingsights #account-name',function () {
		loadData();
	}).on('change','.notiframe-ingsights #datepicker',function () {
		loadData();
	}).on('change','.iframe-ingsights #account-name',function () {
		getIframeData();
	}).on('change','.iframe-ingsights #datepicker',function () {
		getIframeData();
	});
	Highcharts.setOptions({
	global:{
			timezoneOffset : -8 * 60,
		},
		'lang' : {
			resetZoom: 'reset',
		},
	});

	// iframe insights
	function getIframeData() {
		var datepickerVal= $('#datepicker').val().split('-')||"",
			timeType,stime=datepickerVal[0].replace(/(^\s+)|(\s+$)/g,""),
			etime=datepickerVal[1].replace(/(^\s+)|(\s+$)/g,""),
			days=(Date.parse(etime)-Date.parse(stime))/86400000,
			emonths=new Date(etime).getMonth(),
			smonths=new Date(stime).getMonth(),
			eyears=new Date(etime).getYear(),
			syears=new Date(stime).getYear();
		if (days<=2) {
			timeType="HR";
		}else if ((eyears-syears)*12+(emonths-smonths)<2) {
			timeType="DT";
		}else{
			timeType="MH";
		}
		$.get('/analytics/get-dashbord-by-campaign',
		{
			campaignId : $('#campaignId').val(),
			time : {
				sourse_start : datepickerVal[0],
				sourse_stop : datepickerVal[1],
				type : timeType,
				//MH代表月份,DT代表日期,HR代表小时
			},
		},
		function(data){
			dataStore= data;
			var obj={
				"timestamp":null,
				"income": 0.00,
				"spent": 0.00,
				"results": 0,
				"clicks": 0,
				"impressions": 0,
			};
			$('#datatable tbody').empty();
			for (var i = 0; i < dataStore.length; i++) {
				var index=i;
				renderTable(dataStore[index],timeType);
				obj.results += Number(dataStore[i].mobile_app_install);
				obj.impressions += Number(dataStore[i].impressions);
				obj.clicks += Number(dataStore[i].clicks);
				obj.spent += Number(dataStore[i].spent);
				obj.income += Number(dataStore[i].income);
			}
			obj.income = obj.income.toFixed(2);
			obj.cpc = isNaN(obj.spent / obj.clicks) ? 'NaN' : (obj.spent / obj.clicks).toFixed(2);
			obj.ecpa = isNaN(obj.spent / obj.results) ? 'NaN' : (obj.spent / obj.results).toFixed(2);
			obj.cpm = isNaN(obj.spent / obj.impressions) ? 'NaN' : (1000 * obj.spent / obj.impressions).toFixed(2);
			obj.ctr = isNaN( obj.clicks / obj.impressions) ? 'NaN' : ( obj.clicks * 100 / obj.impressions).toFixed(2);
			obj.cvr = isNaN(obj.results / obj.clicks) ? 'NaN' : (100 * obj.results / obj.clicks).toFixed(2);
			obj.profit = (obj.income - obj.spent).toFixed(2);
			obj.spent = obj.spent.toFixed(2);

			for (item in obj) {
				if (item==="clicks"||"impressions"||"results") {
					obj[item]=obj[item];
				}else{
					obj[item]=Number(obj[item]).toFixed(2);
				}
			}
			totalCount(obj);
			render();
		});
	}

	function loadData() {
		var datepickerVal= $('#datepicker').val().split('-')||"",
			timeType,stime=datepickerVal[0].replace(/(^\s+)|(\s+$)/g,""),
			etime=datepickerVal[1].replace(/(^\s+)|(\s+$)/g,""),
			days=(Date.parse(etime)-Date.parse(stime))/86400000,
			emonths=new Date(etime).getMonth(),
			smonths=new Date(stime).getMonth(),
			eyears=new Date(etime).getYear(),
			syears=new Date(stime).getYear();
		if (days<=2) {
			timeType="HR";
		}else if ((eyears-syears)*12+(emonths-smonths)<2) {
			timeType="DT";
		}else{
			timeType="MH";
		}
		$.get(
			'/analytics/get-dashbord-by-account',
			{
				accountId : $('#account-name').val(),
				time : {
					sourse_start : datepickerVal[0],
					sourse_stop : datepickerVal[1],
					type : timeType,
					//MH代表月份,DT代表日期,HR代表小时
				},
			},
			function(data){
				dataStore= data.data;
				timezone = data.timezone;
				var obj={
					"timestamp":null,
					"income": 0.00,
					"spent": 0.00,
					"results": 0,
					"clicks": 0,
					"impressions": 0,
				};

				if (timezone == 8) {
					$('#timezone_info').hide();
				} else {
					$('#timezone_info').show();
				}
				$('#datatable tbody').empty();
				for (var i = 0; i < dataStore.length; i++) {
					var index=i;
					renderTable(dataStore[index],timeType);
					obj.results += Number(dataStore[i].mobile_app_install);
					obj.impressions += Number(dataStore[i].impressions);
					obj.clicks += Number(dataStore[i].clicks);
					obj.spent += Number(dataStore[i].spent);
					obj.income += Number(dataStore[i].income);
				}
				obj.income = obj.income.toFixed(2);
				obj.cpc = isNaN(obj.spent / obj.clicks) ? 'NaN' : (obj.spent / obj.clicks).toFixed(2);
				obj.ecpa = isNaN(obj.spent / obj.results) ? 'NaN' : (obj.spent / obj.results).toFixed(2);
				obj.cpm = isNaN(obj.spent / obj.impressions) ? 'NaN' : (1000 * obj.spent / obj.impressions).toFixed(2);
				obj.ctr = isNaN( obj.clicks / obj.impressions) ? 'NaN' : ( obj.clicks * 100 / obj.impressions).toFixed(2);
				obj.cvr = isNaN(obj.results / obj.clicks) ? 'NaN' : (100 * obj.results / obj.clicks).toFixed(2);
				obj.profit = (obj.income - obj.spent).toFixed(2);
				obj.spent = obj.spent.toFixed(2);

				for (item in obj) {
					if (item==="clicks"||"impressions"||"results") {
						obj[item]=obj[item];
					}else{
						obj[item]=Number(obj[item]).toFixed(2);
					}
				}
				totalCount(obj);
				render();
			}
		);
	}
	function totalCount(obj) {
		var objCont={}, objitem="";
		$('#datatable tbody tr.data-total').remove();
		objCont={
				"ecpa": obj.ecpa,
				"income": obj.income,
				"profit": obj.profit,
				"spent": obj.spent,
				"results": obj.results,
				"clicks": obj.clicks,
			    "impressions": obj.impressions,
			    "cpc": obj.cpc,
			    "cpm": obj.cpm,
			    "ctr": obj.ctr,
			    "cvr": obj.cvr,
		}
		for (item in objCont) {
			objitem+='<td>'+objCont[item]+'</td>';
		}
		var totalTmpl="";
		totalTmpl='<tr class="data-total"><td>Total</td>'+objitem+'</tr>';
		$('#datatable tbody').prepend(totalTmpl);
	}
	function getLocalTime(nS,timeType) {
	    var date=new Date(parseInt(nS) * 1000);
	    var day=date.getDate();
	    var month=date.getMonth() + 1;
	    var year=date.getFullYear();
	    var hour=date.getHours();
	    var minute=date.getMinutes();
	    var d=year;
	    if (timeType=="MH") {
		    if (month >= 10 ){
	            d += "-"+month;
	        }
	        else{
	        	d += "-0" + month;
	        }
     	}else if (timeType=="DT") {
		    if (month >= 10 ){
	         d += "-"+month;
	        }
	        else{
	         d += "-0" + month;
	        }
	        if (day >= 10 ){
	         d += "-"+day;
	        }
	        else{
	         d += "-0" + day;
	        }
        }else if (timeType=="HR") {
        	var d="";
		    if (month >= 10 ){
	         	d += month;
	        }
	        else{
	         	d += "0" + month;
	        }
	        if (day >= 10 ){
	         	d += "-"+day;
	        }
	        else{
	         	d += "-0" + day;
	        }
        	if (hour>=10) {
        		d += " "+hour;
        	}else{
        		d += " 0" + hour;
        	}
        	if (minute>=10) {
        		d += ":"+minute;
        	}else{
        		d += ":0" + minute;
        	}
        };
	    return d;
	}
	function renderTable(data,timeType) {
		var tmpl='<tr>'+
				'<td time="'+getLocalTime(data.timestamp,timeType)+'">'+getLocalTime(data.timestamp,timeType)+'</td>'+
				'<td>'+Number(data.ecpa).toFixed(2)+'</td>'+
				'<td>'+Number(data.income).toFixed(2)+'</td>'+
				'<td>'+Number(data.income-data.spent).toFixed(2)+'</td>'+
				'<td>'+Number(data.spent).toFixed(2)+'</td>'+
				'<td>'+Number(data.mobile_app_install)+'</td>'+
				'<td>'+Number(data.clicks)+'</td>'+
				'<td>'+Number(data.impressions)+'</td>'+
				'<td>'+Number(data.cpc).toFixed(2)+'</td>'+
				'<td>'+Number(data.cpm).toFixed(2)+'</td>'+
				'<td>'+(Number(data.ctr)*100).toFixed(2)+'</td>'+
				'<td>'+(Number(data.cvr)*100).toFixed(2)+'</td>'+
				'</tr>';
		$('#datatable tbody').append(tmpl);
	}
	function getCat() {
		var cat=[];
		$('#datatable tbody tr').not('.data-total').find('td:first-child').each(function(index, el) {
			cat.push($(this).text());
		});
		return cat.reverse();
	}
//默认执行函数
	function render(){
		var cols = getCategories();
		var series = getData(cols);
		var cat=getCat ();
		var align="left";
		var layout="";
		var verticalAlign="bottom";
		if ($('.iframe-insights').length>0) {
			align="left";
			// verticalAlign="bottom";
		};
		var contWidth = $('#dashbord-chart').width()-60;
		var defaultOption = {
			chart : {
				spacingBottom: 100
			},
			credits : {
				enabled : false,
	        },
			lang : {
				noData : 'no data!',
			},

			noData : {
				style: {
	                fontWeight: 'bold',
				    fontSize: '15px',
			        color: '#303030'
				},
			},
	        title : {
				text : '',
	        },
			xAxis : {
				categories: cat
			},

			yAxis: [
				{
					title : {
						text : cols[0]
					},
					labels : {
						align : 'left',
						x : 3,
						y : 16,
						format : '{value:.,0f}'
					},
					lineWidth : 1,
					showFirstLabel : false
				}, 
				{
					title : {
						text : cols.length > 2 ? '' : cols[1]
					},
					opposite : true,
					labels : {
						align : 'right',
						x : 30,
						y : 16,
						format : '{value:.,0f}'
					},
					lineWidth : 1,
					showFirstLabel : false
				}
			],
			legend: {
				// align : align,
				// verticalAlign: verticalAlign,
				y : 40,
				x : 0, 
				floating : true,
				borderWidth : 0,
				backgroundColor : '#F6F6F6',
				// itemWidth: 100,
				// padding: 7,
				itemDistance: 50,
				width: contWidth
			},
			tooltip: {
				shared : true,
				crosshairs: [{            // 设置准星线样式
				    width: 1,
				    color: 'rgb(124, 181, 236)'
				}, {
				    width: 0,
				    color: "transparent",
				    dashStyle: 'longdashdot',
				    zIndex: 100 
				}]
			},
			plotOptions: {
				spline : {
					dataLabels : {
						enabled : false,
						padding : 0,
					},
				},
				series : {
					cursor : 'pointer',
					point : {
						events : {
							click : function (e) {
								$('.cal-dropDown').addClass('hidden');
							}
						}
					},
					marker : {
						lineWidth : 1
					},
					events : {
							click : function (e) {
								$('.cal-dropDown').addClass('hidden');
							}
						}
				}
			},
			series : series
		};
		dash_render(defaultOption);
	}
	function dash_render(option) {
		console.log(option);
		$('#dashbord-chart').highcharts(option);
	}
	function getData(cols){
		var time,serData,series=[],y,heads=[],data=[],dataSeries=[],dataset=dataStore;
		$('.cal-dropDown  input:checked').each(function(index, el) {
			serData = {};
			serData.name=$(this).parent().text();
			serData.data=[];
			var item=$(this).val();
			for (var i = 0; i < dataset.length; i++) {
				var num=Number(dataset[i][item]);
				if (isNaN(num)) {
					num=0;
				};
				serData.data.push(num);
			}
			serData.data.reverse();
			series.push(serData);
		});
		return series;
		// var time, y, $tds, i =0, title, heads={}, body=[], series=[];
		// 	$('#datatable thead th').each(function(){
		// 		title = $(this).text();
		// 		heads[title] = i;
		// 		i++;
		// 	});

		// 	for(i = 0; i < cols.length ; i++){
		// 		series.push({
		// 			name : cols[i],
		// 			data : [],
		// 			yAxis: cols.length > 2 ? 0 : i,
		// 		});	
		// 	}

		// 	$('#datatable tbody tr').each(function(){
		// 		$tds = $(this).find('td');
		// 		time = $tds.eq(0).text();
		// 		if(time === 'total'){
		// 			return ;
		// 		}
		// 		time = format($tds.eq(0).attr('time'), 'Date');
		// 		for(i in cols){
		// 			y = format($tds.eq(heads[cols[i]]).text(), cols[i]);
		// 			series[i].data.push([time, y]);
		// 		}
		// 	});
		// 	return series;
	}
	function format(text, column){
		if(column === 'Date'){
			return new Date(text.replace(/-/,"/")).getTime();
		} else {
			return parseFloat(text.replace(/,/,''));
		}
	}
	function getCategories(){
		var cols = [];
		if (!$('.cal-dropDown')) {
			$('#calculations input:checked').each(function(){
				cols.push($(this).siblings('span').text());
			});
		}else{
			$('.cal-dropDown input:checked').each(function(){
				cols.push($(this).siblings('span').text());
			});
		}
		return cols;
	}
	function checkCalculations(){
		var list = $('.cal-dropDown  input:checked');
		$('.cal-dropDown input').attr('disabled', false);
		if(list.length > 1){
		} else {
			$('.cal-dropDown input:checked').each(function(){
				$(this).attr('disabled', true);
			});
		}
	}
});
