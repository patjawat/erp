<?php
use yii\helpers\Html;
?>
<!doctype html>
<html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= Html::encode('QR ลงเวลา — ' . $model->name) ?></title></head>
<body>
<main>
    <h1><?= Html::encode($model->name) ?></h1>
    <p>สแกนเพื่อลงเวลาเข้า–ออก</p>
    <img src="<?= Html::encode($image) ?>" width="352" height="352" alt="QR ลงเวลาจุด <?= Html::encode($model->name) ?>">
    <p>เปิด GPS แล้วกดสแกนเวลา ระบบเทียบเวลางานให้ หากอยู่นอกพื้นที่ให้ระบุเหตุผลเพื่อส่งอนุมัติ</p>
    <p>รัศมีจุดลงเวลา <?= (int)$model->radius_m ?> เมตร</p>
    <p><?= Html::a('เปิดหน้าลงเวลาของจุดนี้', $url) ?></p>
    <p>พิมพ์ป้ายนี้ด้วยคำสั่งพิมพ์ของเบราว์เซอร์</p>
    <?php if (in_array(parse_url($url, PHP_URL_HOST), ['127.0.0.1', 'localhost', '::1'], true)): ?>
    <p><strong>ป้ายทดสอบบนเครื่องนี้:</strong> ก่อนใช้กับมือถือ ให้เปิดระบบผ่านชื่อเว็บไซต์ HTTPS ที่มือถือเข้าถึงได้ แล้วสร้างป้ายใหม่จากเว็บไซต์นั้น</p>
    <?php endif; ?>
</main>
</body></html>
