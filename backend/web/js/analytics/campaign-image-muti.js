	// 导航
	$('.analytics_nav.subnav').show();
	resmargin();
	// 图片边距随页面宽度变化
	$(window).resize(function() {
		resmargin();
	});
	$('body').on('click', '.date-wrap-modal', function() {
    	$('#dateRange').removeClass('dateRange_expanded');
    	$(this).remove();
	});
	// 设置图片边距
	function resmargin () {
		var imginfosLi=$('#imginfos>li');
		var ml=$('#imginfos').width()-imginfosLi.width()*4.01;
		var imginfoLen=$('#imginfos>li').length;
		if ($('#imginfos').width()<imginfosLi.width()*4) {
		    $('#imginfos').css('min-width',imginfosLi.width()*4);
		    $('header,header>div').css('min-width', imginfosLi.width()*4);
		}
		for (var i = 1; i <imginfoLen; i++) {
		    imginfosLi.eq(i*4-2).css({
		        'margin-right': Math.floor(ml/3)+'px'
		    });
		    imginfosLi.eq(i*4-3).css({
		        'margin-right': Math.floor(ml/3)+'px'
		    });
		    imginfosLi.eq(i*4-4).css({
		        'margin-right': Math.floor(ml/3)+'px'
		    });
		}
	}

	// 显示图片
	function render (data) {
		var tabledom = '<div class="append"><div class="data-geo-details">'+
						'<div class="tit-item subnav"><ul class="tit-item-left clearfix">'+
						'<li class="date-data active"><a href="###" title="">Date</a></li>'+
						'<li class="geo-data"><a href="###" title="">Geo</a></li>'+
						'<li class="age-data"><a href="###" title="">Age</a></li>'+
						'</ul></div><div class="body-item body-item-date">'+
						'<table><thead><tr><th>Date</th><th>Result</th><th>Cost($)</th><th>Clicks</th>'+
						'<th>Impression</th><th>CTR(%)</th><th>CVR(%)</th><th>CPM</th>'+
						'<th>Amount Spend($)</th></tr></thead><tbody></tbody>'+
						'</table></div>'+
						'<div class="body-item body-item-geo hidden">'+
						'<table><thead><tr><th>Country</th><th>Result</th><th>Cost($)</th><th>Clicks</th>'+
						'<th>Impression</th><th>CTR(%)</th><th>CVR(%)</th><th>CPM</th>'+
						'<th>Amount Spend($)</th></tr></thead><tbody></tbody>'+
						'</table></div><div class="body-item body-item-data hidden"><table><thead><tr><th>Age</th><th>Result</th><th>Cost($)</th><th>'+
						'Clicks</th><th>Impression</th><th>CTR(%)</th><th>CVR(%)</th>'+
						'<th>CPM</th><th>Amount Spend($)</th></tr>'+
						'</thead><tbody>'+
						'</tbody></table></div></div></div>';
		for (var i = 0; i < data.length; i++) {
			var creative = data[i].creative;
			var dataitem = data[i].data;
			var group = data[i].group;
			var groupCont = '<div class="grouplev grouplev'+i+' clearfix row" creativeid = "'+creative.id+'"><h4>'+group+'</h4>';
			var imglen = creative.images.length;
			var imgInfoContent = "",showDetailContent = "";
			for (var j = 0; j < imglen; j++) {
				imgInfoContent += '<div class="img-info col-md-3"><div class="imgshow"'+
								  'style="background-image: url('+creative.images[j].url+')"><p class="contents-imgid">ID:'+creative.images[j].id+'</p></div></div>';
			}
			groupCont += imgInfoContent + tabledom +'<div></div></div>';
			$('#imginfos').append(groupCont);
			showDetailContent = '<tr>'+
								'<td></td>'+
								'<td>'+dataitem.mobile_app_install+'</td>'+
								'<td>'+dataitem.ecpa+'</td>'+
								'<td>'+dataitem.clicks+'</td>'+
								'<td>'+dataitem.impressions+'</td>'+
								'<td>'+xs_formate_float(dataitem.ctr)+'</td>'+
								'<td>'+xs_formate_float(dataitem.cvr)+'</td>'+
								'<td>'+dataitem.cpm+'</td>'+
								'<td>'+dataitem.spent+'</td></tr>';
			$('.date-data').trigger('click');
		}
	}
	// 获取图片数据show detail
	function getImgData () {
		var datepickerVal=$('#datepicker').val().split('-');
		$.ajax({
			type: "GET",
			url: '/analytics/get-carousel-stats-list',
			data: {
				campaignId : $('#xs_campaign_id').val(),
				time : {
					sourse_start :  datepickerVal[0],
					sourse_stop : datepickerVal[1],
				},
				order : $('#sort-result').val()
			},
			success: function(data) {
				$('#imginfos').empty();
				if (data.length>0) {
					render(data);
				};
				if(data.length < 12){
					$('#xs_next').hide();
				} else {
					$('#xs_next').show();
				}
			},
			dataType: 'json'
		});
	}

	// 根据图片id获取数据
	function getImgDataByImgId (obj,creativeid) {
		var datepickerVal=$('#datepicker').val().split('-');
		$.ajax({
			type: "GET",
			url: '/analytics/get-stats-by-carousel-id?groupBy=age',
			data: {
				campaignId : $('#xs_campaign_id').val(),
				creativeId : creativeid,
				time : {
					sourse_start :  datepickerVal[0],
					sourse_stop : datepickerVal[1],
				}
			},
			success: function(data) {
				var showDetailContent='';
				var i = obj.index();
				showDetailContent+='<tr>'+
									'<td>'+data[i].age_range.min+'-'+data[i].age_range.max+'</td>'+
									'<td>'+data[i].data.mobile_app_install+'</td>'+
									'<td>'+data[i].data.ecpa+'</td>'+
									'<td>'+data[i].data.clicks+'</td>'+
									'<td>'+data[i].data.impressions+'</td>'+
									'<td>'+xs_formate_float(data[i].data.ctr)+'</td>'+
									'<td>'+xs_formate_float(data[i].data.cvr)+'</td>'+
									'<td>'+data[i].data.cpm+'</td>' + 
									'<td>'+data[i].data.spent+'</td></tr>';
				obj.find('.body-item-data').removeClass('hidden').siblings('.body-item').addClass('hidden');
				obj.find('.body-item-data tbody').empty().append(showDetailContent);
			},
			dataType: 'json'
		});
	}
	

	
	
	// 获取Geo地址
	function getGeoImgData(obj,creativeid,thisobj) {
		var datepickerVal=$('#datepicker').val().split('-');
		$.ajax({
			url: '/analytics/get-stats-by-carousel-id?groupBy=geo',
			type: 'GET',
			dataType: 'json',
			data: {
				campaignId : $('#xs_campaign_id').val(),
				creativeId : creativeid,
				time : {
					sourse_start :  datepickerVal[0],
					sourse_stop : datepickerVal[1],
				}
			},
			success: function(data) {
				var showDetailContent='';
				for(var i in data){
					if (thisobj.hasClass('date-data')) {
						data[i].key = datepickerVal[0]+'-'+datepickerVal[1];
					}

					showDetailContent+='<tr>'+
										'<td>'+data[i].key+'</td>'+
										'<td>'+data[i].data.mobile_app_install+'</td>'+
										'<td>'+data[i].data.ecpa+'</td>'+
										'<td>'+data[i].data.clicks+'</td>'+
										'<td>'+data[i].data.impressions+'</td>'+
										'<td>'+xs_formate_float(data[i].data.ctr)+'</td>'+
										'<td>'+xs_formate_float(data[i].data.cvr)+'</td>'+
										'<td>'+data[i].data.cpm+'</td>' + 
										'<td>'+data[i].data.spent+'</td></tr>';
				}
				if (thisobj.hasClass('date-data')) {
					obj.find('.body-item-date').removeClass('hidden').siblings('.body-item').addClass('hidden');
					obj.find('.body-item-date tbody').empty().append(showDetailContent);
				}else{
					obj.find('.body-item-geo').removeClass('hidden').siblings('.body-item').addClass('hidden');
					obj.find('.body-item-geo tbody').empty().append(showDetailContent);
				}
			}
		});
	}


	function countfl (obj) {
		var $this= obj;
		var liDom = $('#imginfos>li').length;
		var closeImginfo=$this.closest('li');
		var index=closeImginfo.attr("data-index");
		var fl=Math.ceil(index/4);
		var lastfl=Math.ceil(liDom/4);
		var tmpl=$('.append').eq(0).clone().addClass('appendClone');
		if (index>(lastfl-1)*4) {
			$('#imginfos>li').eq(liDom-1).after(tmpl);
		}
		else if(index<=fl*4){
			if (fl===lastfl){
				$('#imginfos>li').eq(liDom-1).after(tmpl);
			}else{
				$('#imginfos>li').eq(fl*4-1).after(tmpl);
			}
		}
	}
	var lastTarget;
	// 内容改变获取数据
	$(document).on('change','#datepicker , #sort-result',function() {
		$('#xs_page').val(0);
		$('#xs_before').hide();
		getImgData($('#country-name').val());
	}).on('click','.geo-data', function() {
		var obj = $(this).parents('.grouplev');
		var creativeid = obj.attr('creativeid');
		//getGeoImgData(obj,creativeid);
		getGeoImgData(obj,creativeid,$(this));
	}).on('click', '.age-data', function(event) {
		var obj = $(this).parents('.grouplev');
		var creativeid = obj.attr('creativeid');
		getImgDataByImgId(obj,creativeid);
	}).on('click', '.date-data', function(event) {
		var obj = $(this).parents('.grouplev');
		var creativeid = obj.attr('creativeid');
		getGeoImgData(obj,creativeid,$(this));
	}).on('click','.tit-item-left li', function() {
		$(this).addClass('active').siblings('li').removeClass('active');
	}).on('click', '#xs_before', function(){
		$('#xs_page').val(parseInt($('#xs_page').val(), 10) - 1);
		$('#xs_next').show();
		if(parseInt($('#xs_page').val(), 10) > 0){
			$('#xs_before').show();
		} else {
			$('#xs_before').hide();
		}
		getImgData($('#country-name').val());
	}).on('click', '#xs_next',function(){
		$('#xs_page').val(1 + parseInt($('#xs_page').val(), 10));
		if(parseInt($('#xs_page').val(), 10) > 0){
			$('#xs_before').show();
		} else {
			$('#xs_before').hide();
		}
		getImgData();
	});
function xs_render_country(data){
	var html = '<option value="" selected>All</option>';
	for(var i in data){
		html += '<option value="['+i+']">'+data[i]+'</option>';
	}
	$('#country-name').html(html);
}
