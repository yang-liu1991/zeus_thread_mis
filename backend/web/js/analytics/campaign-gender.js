    function loadData() {
		var datepickerVal=$('#datepicker').val().split('-');
        $.get(
		'/analytics/get-campaign-gender',
		{
			campaignId : $('#xs_campaign_id').val(),
			time : {
					sourse_start :  datepickerVal[0],
					sourse_stop : datepickerVal[1],
			},
		},
		function(data) {
			var t,html = '';
			var gender = {'1':'男','2':'女'};
            for (var i in data) {
				t = data[i].data;
				html += '<tr><td>' + gender[data[i].key] + '</td><td>' + t.mobile_app_install + '</td><td>' + t.ecpa + '</td><td>' + t.income + '</td><td>' + t.spent + '</td><td>' + xs_formate_float(t.roi) + '</td><td>' + t.impressions + '</td><td>' + xs_formate_float(t.ctr) + '</td><td>' + t.clicks + '</td><td>' + t.cpc + '</td><td>' + t.cpm + '</td><td>' + xs_formate_float(t.cvr) + '</td>';
            }
			$('#xs_table tbody').html(html);
			getDataType();
        });
    }
