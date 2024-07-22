<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
?>
    <?= $this->render('avatar',['model' => $model])?>
    <?php
    echo Yii::$app->user->id;
    ?>
     <?php if(!Yii::$app->user->isGuest):?>
     <?php
                     echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
                     . Html::submitButton(
                         '<i class="bx bx-power-off me-2"></i> ออกจากระบบ (' . Yii::$app->user->identity->username . ')',
                         ['class' => 'dropdown-item']
                     )
                     . Html::endForm();
                    ?>
                    <?php endif; ?>


