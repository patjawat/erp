<?php
$detail =  Yii::$app->request->get('detail');
?>
<?=$this->render('lists/'.$detail.'_list',['model' => $model])?>