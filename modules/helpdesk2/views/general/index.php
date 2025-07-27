<?= $this->render('@app/modules/helpdesk2/views/service/list', [
     'active' => 'dashboard',
     'title' => $title,
     'icon' => $icon,
     'searchModel' => $searchModel,
     'dataProvider' => $dataProvider,
]) ?>