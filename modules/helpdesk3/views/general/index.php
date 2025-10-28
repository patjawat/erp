<?= $this->render('@app/modules/helpdesk3/views/service/list', [
     'active' => 'dashboard',
     'title' => $title,
     'icon' => $icon,
     'searchModel' => $searchModel,
     'dataProvider' => $dataProvider,
]) ?>