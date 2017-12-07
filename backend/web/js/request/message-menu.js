$(document).ready(function() {
	setInterval(getNewMessage, 10000);
	function getNewMessage(){
		$.ajax({
			url:'/message/get-new-message',// 跳转到 action
			type:'post',
			cache:false,  
			async:false,
			success:function(data) {  
				reloadNewMessage(data);
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {  
				console.log(XMLHttpRequest.status);
				console.log(XMLHttpRequest.readyState);
				console.log(textStatus);
			}
		})
	}


	/**
	 *	重新填充message数据
	 */
	function reloadNewMessage(response)
	{
		console.log(response);
		if(response.status == true)
		{
			$('#message-menu .dropdown-menu li').remove();
			$('#message-menu .badge').html(response.message_total_list['all_total']);

			message_html = buildingMessageHtml(response.message_total_list);
			$('#message-menu').append(message_html);
		}
	}


	/**
	 * 返回组装好的html
	 */
	function buildingMessageHtml(message_total_list)
	{
		var message_html = '<ul id="w12" class="dropdown-menu"><li><a href="/message/list?type=0" tabindex="-1"><span>开户</span><span style="background-color: #00aa00" class="badge badge-success">'+ message_total_list.create_account_total +'</span></a></li><li class="divider"></li> <li><a href="/message/list?type=1" tabindex="-1"><span>额度</span><span style="background-color: #00aa00" class="badge badge-success">'+ message_total_list.change_creditlimit_total +'</span></a></li><li class="divider"></li><li class="active"><a href="/message/list?type=2" tabindex="-1"><span>关联</span><span style="background-color: #00aa00" class="badge badge-success">'+ message_total_list.change_binding_total +'</span></a></li><li class="divider"></li><li><a href="/message/list?type=3" tabindex="-1"><span>更名</span><span style="background-color: #00aa00" class="badge badge-success">'+ message_total_list.change_account_name +'</span></a></li></ul>';
		return message_html;
	}
})
