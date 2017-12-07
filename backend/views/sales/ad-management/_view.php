<?php 

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Conversion;
?>
<?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            'name_en',
            'name_zh',
            'address',
			[
				'attribute'	=> 'website',
				'format'	=> 'raw',
				'value'		=> Conversion::getWebsite($model->website, 'all'),
			],
            'industry',
            'payname',
            'contact',
			[
				'attribute' => 'authorization',
				'format'	=> 'raw',
				'value'		=> Html::img($model->authorization, ['style'=>'width:100px;height:150px'])
			],
			[
				'attribute' => 'businesslicense',
				'format'	=> 'raw',
				'value'		=> Html::img($model->businesslicense, ['style'=>'width:100px;height:150px'])
			],
			[
				'attribute' => 'audit_status',
				'format'	=> 'raw',
				'value'		=> Conversion::getAuditStatus($model->audit_status),
			]		
        ],
    ]) ?>

</div>
