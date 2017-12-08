<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2017-02-04 16:41:08
 */

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"> 
	<title>Facebook开户信息更新通知</title>
	<style>
		table{width:100%;border-collapse:collapse;border-spacing:0;border-left:1px solid #888;border-top:1px solid #888;background:#efefef;table-layout:fixed;text-align:center}
		th,td{border-right:1px solid #888;border-bottom:1px solid #888;padding:5px 15px;}
		th{font-weight:bold;background:#ccc;}	
	</style>
</head>
<body>

<table>
	<caption>Facebook开户信息更新通知</caption>
	<thead>
		<tr>
			<th style="width:10%;">帐户ID</th>
			<th style="width:20%;">广告主中文名称</th>
			<th style="width:30%;">推广主品链接</th>
			<th style="width:10%;">状态</th>
			<th style="width:30%;">原因</th>
		</tr>
	</thead>
	<tbody>
		<?= $content; ?>
	</tbody>
</table>

</body>
</html>
