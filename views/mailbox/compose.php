<?php
/**
 * @var $this yii\web\View
 * @var $model \app\models\MessageForm
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::$app->mailer->getCredentials()->email;

$fieldOptions = ['options' => ['class' => 'form-group has-feedback']];
$textareaOptions = [
    'id' => 'compose-textarea',
    'placeholder' => $model->getAttributeLabel('body') . ':',
    'style' => 'height: 300px',
];
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Создать новое сообщение</h3>
            </div>

            <div class="box-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'compose-form', 'enableClientValidation' => false,
                    'options' => ['enctype' => 'multipart/form-data']
                ]) ?>

                <?= $form ->field($model, 'to', $fieldOptions)->label(false)
                    ->textInput(['placeholder' => $model->getAttributeLabel('to') . ':']) ?>

                <?= $form ->field($model, 'subject', $fieldOptions)->label(false)
                    ->textInput(['placeholder' => $model->getAttributeLabel('subject') . ':']) ?>

                <?= $form ->field($model, 'body', $fieldOptions)->label(false)
                    ->textarea($textareaOptions) ?>

                <div class="form-group">
                    <div class="btn btn-default btn-file">
                        <i class="fa fa-paperclip"></i> &nbsp; Приложить файлы

                        <?= Html::activeFileInput($model, 'files',
                            ['name' => 'MessageForm[files][]', 'multiple' => 'multiple']) ?>
                    </div>

                    <?= Html::button('<i class="fa fa-times"></i> &nbsp; Удалить файлы',
                        ['class' => 'btn btn-default', 'id' => 'dropfiles-btn']) ?>
                    <p class="help-block" id="fileinput-hintmessage">Не выбрано</p>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
            <!-- /.box-body -->

            <div class="box-footer text-center">
                <?= Html::button('<i class="fa fa-times"></i> &nbsp; Закрыть', [
                    'id' => 'closewindow-btn', 'class' => 'btn btn-default', 'style' => 'width:150px'
                ]) ?>

                <?= Html::button('<i class="fa fa-envelope-o"></i> &nbsp; Отправить', [
                    'id' => 'sendmessage-btn', 'class' => 'btn btn-primary', 'style' => 'width:150px; margin-left:15px;'
                ]) ?>
            </div>
            <!-- /.box-footer -->
        </div>
        <!-- /. box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->

<?php
$messageComposeJs = <<< JS
    $('#dropfiles-btn').hide();

    // Клик по кнопке "Приложить файлы"
    // Выводим хинт о приложенных файлах, показываем кнопку "Удалить файлы"
    $('#messageform-files').on('change', function() {
        let attachedFiles = $('#messageform-files')[0].files;
        var hintMessage = 'Не выбрано';
      
        if (attachedFiles.length > 0) {
            var files = [];
            $.each(attachedFiles, function(index, file) {
                files.push(file.name);
            });
            
            hintMessage = files.join(', ')
            $('#dropfiles-btn').show();   
        }
        else {
            $('#dropfiles-btn').hide();   
        }
        
        $('#fileinput-hintmessage').html(hintMessage);
    });

    // Клик по кнопке "Удалить файлы"
    // Выводим хинт что нет приложенных файлах, скрываем кнопку "Удалить файлы"
    $('#dropfiles-btn').on('click', function() {
        // Создаем input type file заново
        let id = 'messageform-files'
        document.getElementById(id).innerHTML = document.getElementById(id).innerHTML;
        
        $('#fileinput-hintmessage').html('Не выбрано');
        $(this).hide();
    });
    
    // Клик по кнопке "Закрыть"
    $('#closewindow-btn').on('click', function() {
        window.close(); 
        return false;
    });
    
    // Клик по кнопке "Отправить сообщение"
    // Закрываем окно если сообщение отправлено, или выводим сообщения об ошибках
    $('#sendmessage-btn').on('click', function() {
        let form = $('#compose-form');
        let url = form.attr('action');
        let formData = new FormData(form[0]);
 
        $.ajax({
            url: url,
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'JSON',
            success: function(response) {
                if (response.result !== undefined && response.result === "ok") {
                    alert('Сообщение успешно отправлено');
                    window.close(); 
                    return false;
                } else if (response.message !== undefined) {
                    alert(response.message);
                } else {
                    alert('Неизвестная ошибка при отправке формы');
                }
        
                return true;          
            }
        });
    });
JS;

$this->registerJs($messageComposeJs);
$this->registerJs('$("#compose-textarea").wysihtml5();');
