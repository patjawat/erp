<?php

return [
    'adminEmail' => 'patjawat@gmail.com',
    'senderEmail' => 'patjawat@gmail.com',
    'senderName' => 'Example.com mailer',
    'bsVersion' => '5.x',
    'bsDependencyEnabled' => false,

    // อัปเดตจากเว็บ: Docker pull + recreate (ใช้เมื่อรันบน host ที่มี docker หรือ mount docker.sock)
    'dockerUpdate' => [
        'image' => 'patjawat/erp:latest',
        'composePath' => null,  // โฟลเดอร์ที่มี docker-compose.yml เช่น /home/erp-production/run-production
        'serviceName' => 'app', // ชื่อ service ใน docker-compose (เช่น app = container app-erp)
    ],
];
