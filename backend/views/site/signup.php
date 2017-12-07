<?php
use yii\helpers\Url;
use app\assets\D3Asset;
use app\assets\D3CssAsset;
D3Asset::register($this);
D3CssAsset::register($this);

$this->beginPage();
$this->registerCssFile('@web/redesign/css/site/login.css' . '?v=' . D3_VERSION);
$this->registerJsFile('@web/js/plugin/jquery.md5.js?v=' . D3_VERSION);
$this->registerJsFile('@web/js/site/signup.js');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>d3-login</title>
	<?php $this->head()?>
</head>
<?php $this->beginBody()?>
<body>
<div class="mark"></div>
<div class="wrap" id="mainCont">
	<div class="modal-login">
			<header ></header>
			<div class="mainContainer  clearfix">
				<form id="signup_form" method="post" accept-charset="utf-8">
					<ul class="login_error">
						<?php
							if($error = $model->getErrors()){
								foreach($error as $item){
									if($item){
										foreach($item as $li){
											echo "<li>$li</li>";
										}
									}
								}
							}
						?>
					</ul>
					<input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken()?>">
					<div class="group">
						<label class="col-lg-l control-label">邮箱：</label><input type="email" class="email" required="required" name="AddUserForm[email]" value="<?= $model->email?>" placeholder="User Email">
					</div>
					<div class="group">
						密码：<input type="password" required="required" class="pwd" name="AddUserForm[password]" value="<?= $model->password ?>" placeholder="Password">
					</div>
					<div class="group">
						重复密码：<input type="password" required="required" class="pwd" name="AddUserForm[repassword]" value="<?= $model->repassword ?>" placeholder="Password">
					</div>

					<div class="group group-login">
						<input type="button" class="btn btn-stable btn-xlg btn-submit" value="login" onclick="javascript:window.location.href='<?= Url::to(['login']); ?>'"/>
						<input type="submit" class="btn btn-stable btn-xlg btn-submit" value="signup" />
					</div>
				</form>
			</div>
	</div>
</div>
<style>
.login_error{
	padding: 0px 10px;
	margin: auto auto 20px auto;
	width:80%;	
}
.login_error li{
	color: red;
	padding: 5px 0px;
}
</style>
<script>
	jQuery(document).ready(function($) {
		$('.error-tips').hide();

		$('.btn-submit').on('click',function() {
			if ($('.email')[0].value=="") {
				$('.error-tips').show();
			}else{$('.error-tips').hide();}
			if ($('.pwd')[0].value=="") {
				$('.error-tips').show();
			}else{$('.error-tips').hide();}
		});
	});
</script>
</body>
<?php $this->endBody()?>
</html>
<?php $this->endPage()?>
