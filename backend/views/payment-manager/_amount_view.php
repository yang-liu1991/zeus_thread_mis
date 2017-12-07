<?php
/**
 * Author: liuyang@domob.cn
 * Created Time: 2017-01-06 15:48:57
 */

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use backend\models\record\ThAccountInfoSearch;
?>

<div>
	<tr>
		<td style="width:120px;"><?= $model->fbaccount_id; ?></td>
		<td style="width:180px;"><?= $model->entityInfo->name_zh; ?></td>
		<td style="width:220px;"><?= $model->entityInfo->payname; ?></td>
		<td style="width:120px;"><?= ThAccountInfoSearch::getPaymentType($model->pay_type); ?></td>
		<td style="width:180px;"><?= $model->pay_name_real;?></td>
		<td style="width:100px;"><?= $model->spend_cap; ?></td>
		<td style="width:100px;"><?= Html::button('查看帐户明细', ['name' =>'amount-detail-button', 'class' => 'btn-success btn-xs', 'id' => 'amount-detail-button']); ?></td>
    </tr>
<div>
