<?php

namespace app\modules\jd\services;

use app\modules\jd\models\JdTemplate;
use app\modules\jd\models\JdTemplateBlock;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class JdAiDraftService
{
    public static function isConfigured(): bool
    {
        return (getenv('JD_AI_API_KEY') ?: getenv('OPENAI_API_KEY') ?: '') !== '';
    }

    public function generate(JdTemplate $template): array
    {
        $endpoint = getenv('JD_AI_ENDPOINT') ?: 'https://api.openai.com/v1/chat/completions';
        $apiKey = getenv('JD_AI_API_KEY') ?: getenv('OPENAI_API_KEY') ?: '';
        $model = getenv('JD_AI_MODEL') ?: getenv('OPENAI_MODEL') ?: 'gpt-4.1-mini';
        if ($apiKey === '') {
            throw new \RuntimeException('ยังไม่ได้ตั้งค่า API key สำหรับ AI');
        }

        $schema = [];
        foreach (JdTemplateBlock::definitions() as $code => [$title, $type]) {
            $schema[$code] = [
                'intro' => '',
                'items' => [array_fill_keys(array_keys(JdTemplateBlock::editorColumns($type)), '')],
            ];
        }
        $prompt = 'จัดทำร่าง Job Description ภาษาไทยสำหรับตำแหน่ง "' . $template->getPositionTitle() . '" '
            . 'ชื่อรูปแบบ "' . $template->name . '" สำหรับโรงพยาบาลภาครัฐไทย '
            . 'ตอบเป็น JSON object เท่านั้น ใช้ key ตาม schema นี้ และให้ value ของแต่ละ key เป็น object/array ที่แก้ไขต่อได้: '
            . json_encode($schema, JSON_UNESCAPED_UNICODE) . '. '
            . 'ข้อมูลเป็นเพียงร่าง ห้ามสร้างข้อกฎหมาย ใบอนุญาต หรือค่ามาตรฐานที่ไม่มีหลักฐาน ให้ใส่ข้อความว่าต้องตรวจสอบแทนเมื่อไม่แน่ใจ';

        $client = new Client(['timeout' => 90]);
        try {
            $response = $client->post($endpoint, [
                'headers' => ['Authorization' => 'Bearer ' . $apiKey, 'Content-Type' => 'application/json'],
                'json' => [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'คุณเป็นผู้ช่วยร่างคำบรรยายลักษณะงาน ตอบ JSON ที่ถูกต้องเท่านั้น'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                    'response_format' => ['type' => 'json_object'],
                ],
            ]);
        } catch (RequestException $e) {
            $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            if ($status === 401) {
                throw new \RuntimeException('OpenAI API key ใช้งานไม่ได้ กรุณาเปลี่ยน OPENAI_API_KEY แล้วเริ่ม container ใหม่');
            }
            if ($status === 429) {
                throw new \RuntimeException('บัญชี AI ถึงขีดจำกัดการใช้งานหรือไม่มีเครดิต กรุณาตรวจสอบบัญชี OpenAI');
            }
            throw new \RuntimeException($status ? 'เชื่อมต่อ AI ไม่สำเร็จ (HTTP ' . $status . ')' : 'ไม่สามารถเชื่อมต่อบริการ AI ได้');
        }
        $body = json_decode((string) $response->getBody(), true);
        $content = $body['choices'][0]['message']['content'] ?? null;
        $result = is_string($content) ? json_decode($content, true) : null;
        if (!is_array($result)) {
            throw new \RuntimeException('AI ส่งข้อมูลกลับมาในรูปแบบที่ระบบอ่านไม่ได้');
        }
        return $result;
    }
}
