<?php

return [
    'adminEmail' => 'patjawat@gmail.com',
    'senderEmail' => 'patjawat@gmail.com',
    'senderName' => 'Example.com mailer',
    'bsVersion' => '5.x',
    'bsDependencyEnabled' => false,

    // Ollama (AI สรุป PDF) - ใน Docker ใช้ http://ollama:11434 รันบน host ใช้ http://localhost:11434
    'ollamaUrl' => getenv('OLLAMA_URL') ?: 'http://ollama:11434',
    'ollamaModel' => getenv('OLLAMA_MODEL') ?: 'llama3.2',
];
