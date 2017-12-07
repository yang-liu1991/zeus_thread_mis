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
	
	$('#imginfos').on('click', 'li .imgshow',function() {
		$('.playbtn').show();
		var sib = $(this).parents('li').siblings().find('.imgshow');
		sib.removeClass('canplay');
		var index = $(this).parents('li').index();
		$(this).toggleClass('canplay');
		show_playbtn(index,sib);
	});
	function show_playbtn (index,sib) {
		var curVideo = document.getElementsByTagName('video');
		if ($('.imgshow').hasClass('canplay')) {
			$('.playbtn').hide();
			curVideo[index].play();
		}else{
			curVideo[index].pause();
		}
	}
	// 显示图片
	function render(d,data,i) {
		var i=i+1;
		var ecpa = String(data.ecpa) === 'N/A' ? data.ecpa : data.ecpa + '$';
		var imgInfoContent='<li class="col-md-3" data-index="'+i+'">'+
							'<div class="img-info">'+
							'<div class="imgshow" style="background-image: url()" id="'+d.id+'">'+
							'<video class="video" src="'+d.url+'" width="100%" height="100%" poster="" controls>您的浏览器不支持</video>'+
							'</div><div class="details"><p class="tip-contents"><span>ID</span><span>'+d.id+'</span></p>'+
							'<p class="tip-contents"><span>Length</span><span>'+d.length/1000+'s</span></p>'+
							'<p class="tip-contents"><span>Install</span>'+
							'<span>'+ data.mobile_app_install +'</span></p><p class="tip-contents"><span>CTR</span><span>'+ xs_formate_float(data.ctr, '%') + '</span></p>'+
							'<p class="tip-contents"><span>CVR</span><span>'+xs_formate_float(data.cvr, '%') + '</span></p>'+
							'<p class="tip-contents"><span>CPA</span><span>' + ecpa + '</span></p>'+
							'<div class="showDetail" data-cont='+JSON.stringify(data)+' data-video='+JSON.stringify(d)+'>Show Detail<span class="arrow"></span>'+
							'</div></div></div></li>';
		$('#imginfos').append(imgInfoContent);
		resmargin();
	}
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
	// 获取图片数据show detail
	function getVideoData () {
		var datepickerVal=$('#datepicker').val().split('-');
		$.ajax({
			type: "GET",
			url: '/analytics/get-video-stats-list',
			data: {
				campaignId : $('#xs_campaign_id').val(),
				time : {
					sourse_start :  datepickerVal[0],
					sourse_stop : datepickerVal[1],
				},
				page : $('#xs_page').val(),
				order : $('#sort-result').val(),
			},
			success: function(data) {
				$('#imginfos').empty();
				if (data.length>0) {
					for (var i = 0; i < data.length; i++) {
						render(data[i].video,data[i].data,i);
					}
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
	function getImgDataByImgId () {
		var datepickerVal=$('#datepicker').val().split('-');
		$.ajax({
			type: "GET",
			url: '/analytics/get-stats-by-videoid',
			data: {
				campaignId : $('#xs_campaign_id').val(),
				videoid : $('#img-search').val(),
				time : {
					sourse_start :  datepickerVal[0],
					sourse_stop : datepickerVal[1],
				},
			},
			success: function(data) {
				var video = data.video || {};
				var data = data.data || {};
				$('#imginfos').empty();
				if(data.impressions > 0){
					render(video, data, 0);
				}
			},
			dataType: 'json'
		});
	}
	// 获取Geo地址
	function getGeoImgData(videoid) {
		var datepickerVal=$('#datepicker').val().split('-');
		$.ajax({
			url: '/analytics/get-stats-by-videoid',
			type: 'GET',
			dataType: 'json',
			data: {
				campaignId : $('#xs_campaign_id').val(),
				time : {
					sourse_start :  datepickerVal[0],
					sourse_stop : datepickerVal[1],
				},
				groupBy: 'geo',
				videoid : videoid,
			},
			success: function(data) {
				var showDetailContent='';
					for(var i in data){
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
					$('#imginfos .body-item-geo').show();
					$('.body-item-geo:visible tbody').empty().append(showDetailContent);
			}
		});
	}
	// 显示图片详细信息 
	function showDetail (obj) {														
		var $this= obj;
		var video = JSON.parse($this.attr('data-video'));
		$this.find('.arrow').css('display', 'block');								  
		countfl(obj);
		var datepickerVal=$('#datepicker').val().split('-');
		$('.append:visible').attr('image-id', video.id);				   
		resmargin();																   
		$.get(
			'/analytics/get-stats-by-videoid',								  
			{   
				campaignId : $('#xs_campaign_id').val(),							   
				time : {
					sourse_start :  datepickerVal[0],								  
					sourse_stop : datepickerVal[1],									
				},
				videoid : video.id,
				groupBy : 'age',										
			},
			function(data){
				var age,item,showDetailContent = '';								   
				data = JSON.parse(data);
				if(data.length > 0){
					for(var i in data){
						item = data[i].data;
						age = JSON.parse(data[i].key);								 
						showDetailContent += '<tr>'+ 
							'<td>'+age[0].min + '~'+ age[0].max +'</td>'+			  
							'<td>'+item.mobile_app_install+'</td>'+					
							'<td>'+item.ecpa+'</td>'+
							'<td>'+item.clicks+'</td>'+
							'<td>'+item.impressions+'</td>'+
							'<td>'+xs_formate_float(item.ctr)+'</td>'+				 
							'<td>'+xs_formate_float(item.cvr)+'</td>'+				 
							'<td>'+item.cpm+'</td>'+
							'<td>'+item.spent+'</td></tr>';							
					}
					$('.body-item-data tbody').empty().append(showDetailContent);	  
				}																	  
		});																			
	}
/*
	// 显示图片详细信息
	function showDetail_ (obj,videoid) {
		var $this= obj;
		$this.find('.arrow').css('display', 'block');
		var data = JSON.parse($this.attr('data-cont'));
		var video_data = JSON.parse($this.attr('data-video'));
		var dateVal=$('#datepicker').val();
		var showDetailContent='<tr>'+
							'<td>'+dateVal+'</td>'+
							'<td>'+data.mobile_app_install+'</td>'+
							'<td>'+data.ecpa+'</td>'+
							'<td>'+data.clicks+'</td>'+
							'<td>'+data.impressions+'</td>'+
							'<td>'+xs_formate_float(data.ctr)+'</td>'+
							'<td>'+xs_formate_float(data.cvr)+'</td>'+
							'<td>'+data.cpm+'</td>'+
							'<td>'+data.spent+'</td></tr>';
		countfl(obj);
		$('.append:visible').attr('image-id', video_data.id);
		$('.body-item-data tbody').empty().append(showDetailContent);
		resmargin();
	}
*/
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
	$(document).on('change','#datepicker',function() {
		$('#xs_page').val(0);
		$('#xs_before').hide();
		getVideoData();
	}).on('change','#sort-result',function() {
		$('#xs_page').val(0);
		$('#xs_before').hide();
		getVideoData();
	}).on('keydown','#img-search',function() {
		if(event.keyCode === 13){
			getImgDataByImgId();
		}
	}).on('click','.geo-data', function() {
		getGeoImgData($(this).parents('.append').attr('image-id'));
	}).on('click','.showDetail',function(){
		var obj=$(this);
		if ($('.appendClone').length==0) {
			$('#imginfos').find('.append').remove();
			showDetail(obj);
		} else {
			if (obj[0]===lastTarget[0]) {
				$('.appendClone').toggle();
				obj.find('.arrow').toggle();
			}else{
				$('.appendClone').remove();
				$('.arrow').hide();
				showDetail(obj);
			}
		}
		lastTarget=obj;
	}).on('click','.tit-item-left li', function() {
		$(this).addClass('active').siblings('li').removeClass('active');
		var index=$(this).index();
		$('.body-item').eq(index)
		.removeClass('hidden')
		.siblings('.body-item')
		.addClass('hidden');
	}).on('click', '#xs_before', function(){
		$('#xs_page').val(parseInt($('#xs_page').val(), 10) - 1);
		$('#xs_next').show();
		if(parseInt($('#xs_page').val(), 10) > 0){
			$('#xs_before').show();
		} else {
			$('#xs_before').hide();
		}
		getVideoData();
	}).on('click', '#xs_next',function(){
		$('#xs_page').val(1 + parseInt($('#xs_page').val(), 10));
		if(parseInt($('#xs_page').val(), 10) > 0){
			$('#xs_before').show();
		} else {
			$('#xs_before').hide();
		}
		getVideoData();
	});
function xs_render_country(data){
	var html = '<option value="" selected>All</option>';
	for(var i in data){
		html += '<option value="['+i+']">'+data[i]+'</option>';
	}
	$('#country-name').html(html);
}
