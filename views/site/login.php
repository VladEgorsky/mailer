<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \app\models\LoginForm */

$this->title = 'Авторизация';

$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
];

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-envelope form-control-feedback'></span>"
];

$fieldOptions3 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>"
];
?>

<div class="login-box">
    <div class="login-logo">
        <a href="#">Авторизация</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">Заполните реквизиты почтового ящика</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>

        <?= $form->field($model, 'providerId')
            ->label(false)
            ->dropDownList(\app\models\Provider::getItems(), ['prompt' => 'Выберите провайдера']) ?>

        <?= $form
            ->field($model, 'email', $fieldOptions2)
            ->label(false)
            ->textInput(['placeholder' => $model->getAttributeLabel('email')]) ?>

        <?= $form
            ->field($model, 'password', $fieldOptions3)
            ->label(false)
            ->passwordInput(['placeholder' => $model->getAttributeLabel('password')]) ?>

        <div class="text-center">
            <?= Html::submitButton('ОК', ['class' => 'btn btn-primary', 'style' => 'width: 80px']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
    <!-- /.login-box-body -->
</div><!-- /.login-box -->
