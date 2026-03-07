<?= $this->render('_form', [
    'model' => $model,
    'employee' => $model->employee ?? null,
    'types' => [],
    'stats' => [],
    'roundLabel' => '',
    'draftRef' => null,
    'leaveWorkSendInitText' => $leaveWorkSendInitText ?? '',
]) ?>