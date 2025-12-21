<?= \yii\helpers\Html::button(
    '  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-move-left-icon lucide-move-left">
        <path d="M6 8L2 12L6 16"></path>
        <path d="M2 12H22"></path>
    </svg> ย้อนกลับ',
    [
        'class' => 'btn btn-white border shadow-sm text-secondary d-flex align-items-center gap-2 btn-sm px-3 py-2 bg-white',
        'onclick' => 'history.back();'
    ]
) ?>