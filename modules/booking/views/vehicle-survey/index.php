<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\booking\models\VehicleDetail;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleDetail $model */
/** @var string $token */
/** @var string|null $error */

$ratings = VehicleDetail::listRating();
if ($ratings === []) {
    $ratings = [1 => 'ควรปรับปรุง', 2 => 'พอใช้', 3 => 'ปานกลาง', 4 => 'ดี', 5 => 'ดีมาก'];
}
?>
<div class="row justify-content-center">
    <div class="col-12 col-md-9 col-lg-7">
        <div class="card border-0 shadow rounded-4">
            <div class="card-body p-4">

                <div class="text-center mb-4">
                    <div class="display-6 text-warning mb-1"><i class="bi bi-star-fill"></i></div>
                    <h5 class="mb-1">แบบประเมินความพึงพอใจการใช้รถ</h5>
                    <p class="text-muted small mb-0">ความเห็นของท่านจะนำไปพัฒนาการให้บริการยานพาหนะ</p>
                </div>

                <?= $this->render('_trip', ['model' => $model]) ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i><?= Html::encode($error) ?>
                    </div>
                <?php endif; ?>

                <?= Html::beginForm(Url::to(['index', 'token' => $token]), 'post', ['id' => 'vehicle-survey-form']) ?>

                <div class="mt-4">
                    <label class="form-label fw-medium">ระดับความพึงพอใจ <span class="text-danger">*</span></label>
                    <div class="star-rating d-flex justify-content-center gap-2 mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="radio" class="btn-check" name="score" id="score-<?= $i ?>" value="<?= $i ?>" autocomplete="off">
                            <label class="star-item" for="score-<?= $i ?>" data-score="<?= $i ?>"
                                title="<?= Html::encode($ratings[$i] ?? '') ?>">
                                <i class="bi bi-star"></i>
                            </label>
                        <?php endfor; ?>
                    </div>
                    <div class="text-center text-muted small" id="score-caption">แตะที่ดาวเพื่อให้คะแนน</div>
                </div>

                <div class="mt-4">
                    <label for="survey-comment" class="form-label fw-medium">ข้อเสนอแนะเพิ่มเติม</label>
                    <textarea id="survey-comment" name="comment" rows="3" class="form-control"
                        placeholder="เช่น ความตรงต่อเวลา ความสุภาพของพนักงานขับรถ ความสะอาด/ความปลอดภัยของรถ"></textarea>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary rounded-pill">
                        <i class="bi bi-send me-1"></i>ส่งแบบประเมิน
                    </button>
                </div>

                <?= Html::endForm() ?>

            </div>
        </div>
    </div>
</div>

<?php
$captions = json_encode($ratings, JSON_UNESCAPED_UNICODE);
$css = <<<CSS
.star-rating .star-item {
    font-size: 2.2rem;
    line-height: 1;
    color: #d0d5dd;
    cursor: pointer;
    transition: color .15s ease, transform .15s ease;
}
.star-rating .star-item:hover { transform: scale(1.08); }
.star-rating .star-item.is-on { color: #f6b100; }
CSS;
$this->registerCss($css);

$js = <<<JS
(function () {
    var captions = {$captions};
    var \$items = $('.star-rating .star-item');

    function paint(score) {
        \$items.each(function () {
            var s = parseInt($(this).data('score'), 10);
            $(this).toggleClass('is-on', s <= score);
            $(this).find('i').attr('class', s <= score ? 'bi bi-star-fill' : 'bi bi-star');
        });
    }

    function selected() {
        return parseInt($('input[name="score"]:checked').val() || 0, 10);
    }

    \$items.on('mouseenter', function () {
        paint(parseInt($(this).data('score'), 10));
    }).on('mouseleave', function () {
        paint(selected());
    });

    $('input[name="score"]').on('change', function () {
        var score = selected();
        paint(score);
        $('#score-caption').text(captions[score] || '');
    });

    $('#vehicle-survey-form').on('submit', function (e) {
        if (selected() < 1) {
            e.preventDefault();
            $('#score-caption').addClass('text-danger').text('กรุณาเลือกระดับความพึงพอใจก่อนส่ง');
        }
    });
})();
JS;
$this->registerJs($js);
?>
