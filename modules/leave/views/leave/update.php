<?= $this->render('_form', [
    'model' => $model,
    'employee' => $model->employee ?? null,
    'types' => [],
    'stats' => $stats ?? [],
    'roundLabel' => $roundLabel ?? '',
    'draftRef' => null,
    'leaveWorkSendInitText' => $leaveWorkSendInitText ?? '',
]) ?>