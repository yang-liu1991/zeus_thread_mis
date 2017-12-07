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
			[
				'attribute' => 'address_en',
				'format'	=> 'raw',
				'value'		=> Conversion::getAddressEn($model->address_en),
			],
			'address_zh',
			[
				'attribute'	=> 'promotable_urls',
				'format'	=> 'raw',
				'value'		=> Conversion::getPromotableUrls($model->promotable_urls, 'all'),
			],
			'vertical',
			'subvertical',
			[
				'attribute'	=> 'is_smb',
				'format'	=> 'raw',
				'value'		=> Conversion::getIsSmb($model->is_smb),
			],
            'payname',
			'contact',
			'official_website_url',
			[
				'attribute' => 'promotable_page_ids',
				'format'	=> 'raw',
				'value'		=> Conversion::getPromotablePageIds($model->promotable_page_ids),
			],	
			[
				'attribute' => 'promotable_page_urls',
				'format'	=> 'raw',
				'value'		=> Conversion::getPromotablePageUrls($model->promotable_page_urls),
			],
			[
				'attribute'	=> 'promotable_app_ids',
				'format'	=> 'raw',
				'value'		=> Conversion::getPromotableAppIds($model->promotable_app_ids),
			],
			'advertiser_business_id',
			'business_registration_id',
			[
				'attribute' => 'business_registration',
				'format'	=> 'raw',
				'value'		=> Html::img(Yii::$app->params['ugcServer']['imgdir'].$model->business_registration, ['style'=>'width:150px;height:200px'])
			],
			[
				'attribute'	=> 'comment',
				'format'	=> 'raw',
				'value'		=> Conversion::getComment($model->comment),
			],
			'audit_message',
			[
				'attribute' => 'audit_status',
				'format'	=> 'raw',
				'value'		=> Conversion::getAuditStatus($model->audit_status),
			]		
        ],
    ]) ?>

</div>
