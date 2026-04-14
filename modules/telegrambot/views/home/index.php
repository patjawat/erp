<?php
$this->registerJsFile('https://telegram.org/js/telegram-web-app.js', ['position' => \yii\web\View::POS_HEAD]);


?>


  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 2rem;
      background-color: #f9f9f9;
      color: #333;
    }
    .card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
  </style>

  <div class="card">
    <h2>ข้อมูลผู้ใช้งาน Telegram</h2>
    <p><strong>ชื่อ:</strong> <span id="fullname">-</span></p>
    <p><strong>Username:</strong> <span id="username">-</span></p>
    <p><strong>User ID:</strong> <span id="userid">-</span></p>
  </div>
<?php

use yii\web\View;
$js = <<< JS

   const tg = window.Telegram.WebApp;
    const user = tg.initDataUnsafe?.user;

    if (user) {
      document.getElementById("fullname").textContent = user.first_name + (user.last_name ? ' ' + user.last_name : '');
      document.getElementById("username").textContent = user.username ?? '-';
      document.getElementById("userid").textContent = user.id;
    } else {
      alert('ไม่สามารถดึงข้อมูลผู้ใช้ Telegram ได้');
    }
JS;
$this->registerJS($js,View::POS_END);

?>