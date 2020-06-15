<?php
/**
 * @var $this yii\web\View
 * @var $messageData array
 * @var $messageBody string
 */

use yii\helpers\Html;

$this->title = Yii::$app->mailer->getCredentials()->email;
$attachedFiles = Yii::$app->mailer->getAttachedFiles($messageData['uid']);
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-body no-padding">
                <div class="mailbox-read-info">
                    <h3><?= $messageData['subject'] ?></h3>
                    <h5>From: <?= $messageData['from'] ?>
                        <span class="mailbox-read-time pull-right">
                            <?= Yii::$app->formatter->asDatetime($messageData['date'], 'short') ?>
                        </span>
                    </h5>
                    <br />
                </div>
                <!-- /.mailbox-read-info -->

                <div class="mailbox-read-message">
                    <?= $messageBody ?>
                    <br /><br />
                </div>
                <!-- /.mailbox-read-message -->
            </div>
            <!-- /.box-body -->

            <?php if(!empty($attachedFiles)) : ?>
            <div class="box-footer">
                <div style="padding-bottom: 15px">Приложенные файлы: </div>

                <ul class="mailbox-attachments clearfix">

                    <?php foreach ($attachedFiles as $file): ?>
                    <li>
                        <div class="mailbox-attachment-info">
                            <?php
                                $url = str_replace(Yii::getAlias('@webroot'), '', $file);
                                $text = '<i class="fa fa-paperclip"></i> &nbsp; ' . urlencode(basename($file));
                                echo Html::a($text, $url, ['class' => 'mailbox-attachment-name', 'target' => '_blank'])
                            ?>

                            <span class="mailbox-attachment-size">
                                <?= number_format(filesize($file)/1024, 0) . ' KB' ?>
                            </span>
                        </div>
                    </li>
                    <?php endforeach; ?>

                </ul>
            </div>
            <!-- /.box-footer -->
            <?php endif; ?>

            <div class="text-center" style="padding-bottom: 15px;">
                <?= Html::button('<i class="fa fa-sign-out"></i> &nbsp; Выйти', [
                    'class' => 'btn btn-sm btn-default', 'onclick' => 'window.close(); return false;', 'style' => 'width: 150px'
                ]) ?>
            </div>
        </div>
        <!-- /. box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->