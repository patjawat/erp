<?php

return [
    'adminEmail' => 'patjawat@gmail.com',
    'senderEmail' => 'patjawat@gmail.com',
    'senderName' => 'Example.com mailer',
    'bsVersion' => '5.x',
    'bsDependencyEnabled' => false,

    // Session / Cookie: idle + absolute + remember-me
    // ไม่เลือก remember → session อยู่ได้ประมาณ 1 ชม. (cookie + gc เท่ากัน)
    // เลือก remember → identity cookie 1 วัน; session หมดภายใน 1 ชม. แล้ว auto-login ได้ถ้ายังอยู่ในวันนั้น
    'user.idleTimeoutSeconds' => 3600,
    // นับจากเวลาล็อกอิน (absolute) — ให้เท่ากับ remember 1 วัน
    'user.workingDaySeconds' => 3600 * 24,
    'user.rememberMeDuration' => 3600 * 24,
    // อายุ PHP session id cookie + gc_maxlifetime — 1 ชม. (กรณีไม่ remember)
    'session.cookieLifetime' => 3600,

    // อัปเดตจากเว็บ: Docker pull + recreate (ใช้เมื่อรันบน host ที่มี docker หรือ mount docker.sock)
    'dockerUpdate' => [
        'image' => 'patjawat/erp:latest',
        'composePath' => null,  // โฟลเดอร์ที่มี docker-compose.yml เช่น /home/erp-production/run-production
        'serviceName' => 'app', // ชื่อ service ใน docker-compose (เช่น app = container app-erp)
    ],
];
