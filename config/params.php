<?php

return [
    'adminEmail' => 'patjawat@gmail.com',
    'senderEmail' => 'patjawat@gmail.com',
    'senderName' => 'Example.com mailer',
    'bsVersion' => '5.x',
    'bsDependencyEnabled' => false,

    // Ollama (AI สรุป PDF)
    // - รันใน Docker (compose): ตั้ง OLLAMA_URL=http://ollama:11434 (หรือไม่ตั้งก็ใช้ค่านี้)
    // - บน server จริง / production: ต้องตั้งใน .env เช่น OLLAMA_URL=http://127.0.0.1:11434 (ชื่อ ollama ใช้ได้แค่ใน Docker)
    'ollamaUrl' => getenv('OLLAMA_URL') ?: 'http://ollama:11434',
    'ollamaModel' => getenv('OLLAMA_MODEL') ?: 'llama3.2',
    // ความยาวของการสรุป: short (สั้น), medium (ปานกลาง), long (ยาว) — ตั้งใน .env หรือหน้า ตั้งค่า > AI สรุป
    'ollamaSummaryLength' => getenv('OLLAMA_SUMMARY_LENGTH') ?: 'medium',
];
