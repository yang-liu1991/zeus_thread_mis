    function loadData() {
		var datepickerVal=$('#datepicker').val().split('-');
        $.get(
		'/analytics/get-campaign-age',
		{
			campaignId : $('#xs_campaign_id').val(),
			time : {
					sourse_start :  datepickerVal[0],
					sourse_stop : datepickerVal[1],
			},
		},
		function(data) {
			var age,t,html = '';
            for (var i in data) {
				t = data[i].data;
				age = JSON.parse(data[i].key);
				html += '<tr><td>' + age[0].min + '~' + age[0].max + '</td><td>' + t.mobile_app_install + '</td><td>' + t.ecpa + '</td><td>' + t.income + '</td><td>' + t.spent + '</td><td>' + xs_formate_float(t.roi) + '</td><td>' + t.impressions + '</td><td>' + xs_formate_float(t.ctr) + '</td><td>' + t.clicks + '</td><td>' + t.cpc + '</td><td>' + t.cpm + '</td><td>' + xs_formate_float(t.cvr) + '</td>';
            }
			$('#xs_table tbody').html(html);
			getDataType();
        });
    }

