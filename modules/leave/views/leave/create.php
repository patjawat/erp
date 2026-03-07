<?= $this->render('_form', [
    'model'      => $model,
    'employee'   => $employee ?? null,
    'types'      => $types ?? [],
    'stats'      => $stats ?? [],
    'roundLabel' => $roundLabel ?? '',
    'draftRef'   => $draftRef ?? '',
]) ?>