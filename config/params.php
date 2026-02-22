<?php

return [
    'adminEmail' => 'patjawat@gmail.com',
    'senderEmail' => 'patjawat@gmail.com',
    'senderName' => 'Example.com mailer',
    'bsVersion' => '5.x',
    'bsDependencyEnabled' => false,

    // Ollama (AI สรุป PDF)
    'ollamaUrl' => getenv('OLLAMA_URL') ?: 'http://ollama:11434',
    'ollamaModel' => getenv('OLLAMA_MODEL') ?: 'llama3.2',
    'ollamaSummaryLength' => getenv('OLLAMA_SUMMARY_LENGTH') ?: 'medium',

    // API ข้างนอก (OpenAI) — ใช้เมื่อเลือก "OpenAI" ในตั้งค่า DMS
    'openaiApiKey' => getenv('OPENAI_API_KEY') ?: '',
    'openaiModel' => getenv('OPENAI_MODEL') ?: 'gpt-4o-mini',
    'openaiBaseUrl' => getenv('OPENAI_BASE_URL') ?: 'https://api.openai.com/v1',

    // Google Gemini — ใช้เมื่อเลือก "Gemini" ในตั้งค่า DMS (สร้าง Key ได้ที่ https://aistudio.google.com/app/apikey)
    'geminiApiKey' => getenv('GEMINI_API_KEY') ?: '',
    'geminiModel' => getenv('GEMINI_MODEL') ?: 'gemini-1.5-flash',
];
