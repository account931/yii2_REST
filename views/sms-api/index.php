<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Sms Api';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?> <i class="fa fa-envelope-o" style="font-size:1.5em;"></i></h1>

   


	
	 <div class="alert alert-success">
        <?= nl2br("<h4><span class='glyphicon glyphicon-flag' style='font-size:38px;'></span> This Sms Api page</h4>" .
		             "<p> Requests are executed via cURL (does not work on localhost)</p>" .
					 "<p class='small font-italic'> It is working Yii2 Textbelt Sms Api version, but is deprecated. More functional version is outlined to stand-alone application to {https://github.com/account931/sms_Textbelt_Api} and to the most functional ReactJS version (https://github.com/account931/sms_Textbelt_Api_React_JS) .</p>" .
		             "<p class='small font-italic'>(Here, in Yii2 there is no regExp, sms delivery status, text length count, etc)</p>" .
					 "<hr>" .
					 $text .
					 "<br>"); 
		?>
     </div>
	
	
	
	
	
   <!------ FLASH Success from SmsApiController/actionIndex() ----->
   <?php if( Yii::$app->session->hasFlash('successX') ): ?>
    <div class="alert alert-success alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <?php echo Yii::$app->session->getFlash('successX'); ?>
    </div>
    <?php endif;?>
  <!------ END FLASH Successfrom SmsApiController/actionIndex() ----->
  
  
  <!------ FLASH FAIL from SmsApiController/actionIndex() Sms not send----->
   <?php if( Yii::$app->session->hasFlash('failX') ): ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <?php echo Yii::$app->session->getFlash('failX'); ?>
    </div>
    <?php endif;?>
  <!------ END FLASH FAIL from SmsApiController/actionIndex()) ----->
  
  
  
  
  <!-----------------------------Form --------------------------->
  <?php $form = ActiveForm::begin([
        'id' => 'sendsms',
        'layout' => 'horizontal',
        'fieldConfig' => [
            //'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            //'labelOptions' => ['class' => 'col-lg-1 control-label shadowX lavender'],
        ],
    ]); ?>


        <?= $form->field($model, 'cellNumber')->textInput(['autofocus' => true, 'value'=> '+38097664***','placeholder' => 'Phone in format 38097********']) ?>
        <?= $form->field($model, 'smsText')->textarea(['rows' => '6', 'maxlength' => 60, 'value'=>'Hello. Eng version. Русская версия', 'placeholder' => 'sms text....' ]) ?> 

        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton('Send', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>
  
  <!------------------------ END Form --------------------------->
  
  
  
  
  

</div>
