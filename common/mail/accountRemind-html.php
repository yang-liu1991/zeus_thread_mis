<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2017-02-04 16:41:08
 */

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"> 
	<title>API催单提醒函</title>
	<style>
		table{width:100%;border-collapse:collapse;border-spacing:0;border-left:1px solid #888;border-top:1px solid #888;background:#efefef;table-layout:fixed;text-align:center}
		th,td{border-right:1px solid #888;border-bottom:1px solid #888;padding:5px 15px;}
		th{font-weight:bold;background:#ccc;}	
	</style>
</head>
<body>

<p>
	您好，<br/>
	如下开户申请较为紧急，烦请帮助催单。详情如下：
</p>
<table>
	<caption>API催单提醒函</caption>
	<thead>
		<tr>
			<th style="width:10%;">催单日期</th>
			<th style="width:15%;">催单人</th>
			<th style="width:20%;">广告主中文名称</th>
			<th style="width:25%;">广告主英文名称</th>
			<th style="width:15%;">业务类型</th>
			<th style="width:15%;">Request ID</th>
		</tr>
	</thead>
	<tbody>
		<?= $content; ?>
	</tbody>
</table>
<p>
	Best regards,<br/>
	API system
</p>
</body>
</html>
