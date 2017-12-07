<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Tabs;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use kartik\datetime\DateTimePicker;
use backend\assets\AppAsset;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\record\AdCreativesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '广告创意列表';
$this->params['breadcrumbs'][] = $this->title;

AppAsset::register($this);
$this->registerCssFile('@web/css/jq-upload/jquery.fileupload.css');

$this->registerJsFile('@web/js/jq-upload/jquery.ui.widget.js');
$this->registerJsFile('@web/js/tmpl.min.js');
$this->registerJsFile('@web/js/jq-upload/jquery.fileupload.js');
$this->registerJsFile('@web/js/jq-upload/jquery.fileupload-process.js');
$this->registerJsFile('@web/js/jq-upload/jquery.fileupload-validate.js');
$this->registerJsFile('@web/js/jq-upload/jquery.fileupload-ui.js');
$this->registerJsFile('@web/js/request/layer.js');
$this->registerJsFile('@web/js/request/creatives.js');

?>

<legend><?= Html::encode($this->title) ?></legend>
<!-- 以下为列表搜索框 -->
<div class="creatives-search">
    <?php $form = ActiveForm::begin([
		'id'	=> 'creatives-view-form',
        'action' => ['creatives-view'],
        'method' => 'get',
		'options' => ['class' => 'form-inline well', 'style' => 'text-align:right;'],
		'fieldConfig' => [
	    	'template' => "{beginWrapper}\n{input}\n{hint}\n{endWrapper}",
		]
    ]); ?>


	<?= $form->field($searchModel, 'account_id_list')->textInput(['placeholder' => '请输入Account Id']); ?>
	<?= $form->field($searchModel, 'ad_id')->textInput(['placeholder' => '请输入Ad Id']); ?>

	<?= $form->field($searchModel, 'begin_time')->widget(DateTimePicker::classname(), [   
		'options' => ['placeholder' => '请选择起始时间'],   
		'pluginOptions' => [   
			'autoclose' => true,   
			'todayHighlight' => true,   
		]   
	]); ?>  

	<?= $form->field($searchModel, 'end_time')->widget(DateTimePicker::classname(), [   
		'options' => ['placeholder' => '请选择结束时间'],   
		'pluginOptions' => [   
			'autoclose' => true,   
			'todayHighlight' => true,   
		]   
	]); ?>
    <div class="form-group">
		<!-- <input id="fileupload" name="" type="file" class="file" multiple data-show-caption="false" style="inline-block;width:10px;"> -->
		<?= Html::Button('Search', ['id' => 'creative-search', 'class' => 'btn btn-primary']) ?>
		<?= Html::Button('Export', ['id' => 'creative-export', 'class' => 'btn btn-primary']) ?>
		<!-- The file upload form used as target for the file upload widget -->


    </div>
	<?php ActiveForm::end(); ?>

	<div class="">
		<!-- The file upload form used as target for the file upload widget -->
		<form id="fileupload" action="/admin-manager/file-upload" method="POST" enctype="multipart/form-data">
			<!-- Redirect browsers with JavaScript disabled to the origin page -->
			<noscript><input type="hidden" name="redirect" value="https://blueimp.github.io/jQuery-File-Upload/"></noscript>
			<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
			<div class="row fileupload-buttonbar">
				<div class="col-lg-7">
					<!-- The fileinput-button span is used to style the file input field as button -->
					<span class="btn btn-success fileinput-button">
                	    <i class="glyphicon glyphicon-plus"></i>
                    	<span>Add files...</span>
                    	<input type="file" name="CreativesUploadModel[upload_file]" multiple>
                	</span>
					<button type="submit" class="btn btn-primary start">
						<i class="glyphicon glyphicon-upload"></i>
						<span>Start upload</span>
					</button>
					<!-- The global file processing state -->
					<span class="fileupload-process"></span>
				</div>
				<!-- The global progress state -->
				<div class="col-lg-5 fileupload-progress fade">
					<!-- The extended global progress state -->
					<div class="progress-extended">&nbsp;</div>
				</div>
			</div>
			<!-- The table listing the files available for upload/download -->
			<table role="presentation" class="table table-striped" style="margin-top:10px;"><tbody class="files"></tbody></table>
		</form>
	</div>

	<!-- The template to display files available for upload -->
	<script id="template-upload" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
            <span class="preview"></span>
        </td>
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">Processing...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td>
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn-primary start" disabled>
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Start</span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel</span>
                </button>
            {% } %}
        </td>
    </tr>
	{% } %}
	</script>
	<!-- The template to display files available for download -->
	<script id="template-download" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td>
            <span class="preview">
                {% if (file.thumbnailUrl) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                {% } %}
            </span>
        </td>
        <td>
            <p class="name">
                {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                {% } else { %}
                    <span>{%=file.name%}</span>
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
            {% } %}
        </td>
        <td>
            <span class="size">{%=o.formatFileSize(file.size)%}</span>
        </td>
        <td>
            {% if (file.deleteUrl) { %}
                <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>Delete</span>
                </button>
                <input type="checkbox" name="delete" value="1" class="toggle">
            {% } else { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel</span>
                </button>
            {% } %}
        </td>
    </tr>
	{% } %}
	</script>
</div>




<div class="ad-creatives-index">

	<?php
		echo Tabs::widget([
			'items' => [
			[
				'label' => '等待审核',
				'headerOptions'	=> ['style' => 'width:100px;text-align:center;'],
				'options' => ['style' => 'margin:0 auto;'],
				'url' => Yii::$app->urlManager->createUrl(['admin-manager/creatives-view', 'audit_status' => $searchModel::CREATIVES_STATUS_WAIT]),
				'active' => ($searchModel->audit_status == $searchModel::CREATIVES_STATUS_WAIT)
			],
			[
				'label' => '审核成功',
				'headerOptions'	=> ['style' => 'width:100px;text-align:center;'],
				'options' => ['style' => 'margin:0 auto;'],
				'url' => Yii::$app->urlManager->createUrl(['admin-manager/creatives-view', 'audit_status' => $searchModel::CREATIVES_STATUS_SUCCESS]),
				'active' => ($searchModel->audit_status == $searchModel::CREATIVES_STATUS_SUCCESS)
			],
			[
				'label' => '审核失败',
				'headerOptions'	=> ['style' => 'width:100px;text-align:center;'],
				'options' => ['style' => 'margin:0 auto;'],
				'url' => Yii::$app->urlManager->createUrl(['admin-manager/creatives-view', 'audit_status' => $searchModel::CREATIVES_STATUS_FAILED]),
				'active' => ($searchModel->audit_status == $searchModel::CREATIVES_STATUS_FAILED)
			],
		],
	]);
	?>


	<?php Pjax::begin(['id' => 'creatives-view-list'])?>
	<?php 
		echo ListView::widget([
			'dataProvider'	=> $dataProvider,
			'pager'=>[
				'firstPageLabel'=>"First",
				'prevPageLabel'=>'Prev',
				'nextPageLabel'=>'Next',
				'lastPageLabel'=>'Last',
				'options' => ['class'=>'pager'],
			],
			'itemView' => '_item_view',
			'options' => ['class' => 'List-view', 'style' => 'float:left;margin-left:30px;'],
			'itemOptions' => ['style' => 'float:left;text-align:center;'] 
		]);
	?>
	<?php Pjax::end(); ?>
</div>
