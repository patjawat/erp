<?php

use yii\helpers\Json;

/** @var array $data */

$json = Json::htmlEncode($data);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ThaiD</title>
</head>
<body style="font-family:sans-serif;text-align:center;padding:2rem;color:#333;">
    <p>ได้รับข้อมูลจาก ThaiD แล้ว กำลังส่งกลับไปยังฟอร์ม...</p>
    <script>
        (function () {
            var data = <?= $json ?>;
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage({ source: 'thaid-fill-form', data: data }, window.location.origin);
            }
            window.close();
        })();
    </script>
</body>
</html>
