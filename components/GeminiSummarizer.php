<?php

namespace app\components;

use Yii;
use yii\base\Component;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * สรุปเนื้อหาด้วย Google Gemini API
 * ใช้สำหรับ DMS เมื่อเลือก "Gemini" — ต้องตั้ง GEMINI_API_KEY ใน .env
 * สร้าง API Key ได้ที่ https://aistudio.google.com/app/apikey
 */
class GeminiSummarizer extends Component
{
    /** @var string API Key (จาก .env: GEMINI_API_KEY) */
    public $apiKey;

    /** @var string โมเดล (ใช้ชื่อที่รองรับใน v1 เช่น gemini-1.5-flash-latest, gemini-2.5-flash) */
    public $model = 'gemini-1.5-flash-latest';

    /** @var string base URL (ใช้ v1 สำหรับ generateContent — v1beta บางโมเดลไม่รองรับ) */
    public $baseUrl = 'https://generativelanguage.googleapis.com/v1';

    /** ชื่อโมเดลเก่า/สั้น → ชื่อที่ API v1 รองรับ (generateContent) */
    protected static $modelAliases = [
        'gemini-1.5-flash' => 'gemini-1.5-flash-latest',
        'gemini-1.5-pro' => 'gemini-1.5-pro-latest',
    ];

    /** @var int timeout วินาที */
    public $timeout = 60;

    protected static $desMaxLength = [
        'short' => 800,
        'medium' => 2000,
        'long' => 4000,
    ];

    /** @var string */
    public $summaryLength = 'medium';

    public function init()
    {
        parent::init();
        if (empty($this->apiKey)) {
            $this->apiKey = getenv('GEMINI_API_KEY') ?: (Yii::$app->params['geminiApiKey'] ?? '');
        }
        if (isset(Yii::$app->params['geminiModel'])) {
            $this->model = Yii::$app->params['geminiModel'];
        }
    }

    /**
     * สรุปข้อความ คืนค่า topic และ des (รูปแบบเดียวกับ OllamaSummarizer)
     */
    public function summarize(string $text, ?string $length = null): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'ยังไม่ได้ตั้งค่า GEMINI_API_KEY ใน .env สำหรับการสรุปด้วย Gemini'];
        }

        $text = trim($text);
        if (mb_strlen($text) < 50) {
            return ['error' => 'ข้อความสั้นเกินไปสำหรับสรุป'];
        }

        $saveLength = $this->summaryLength;
        if ($length !== null && isset(self::$desMaxLength[$length])) {
            $this->summaryLength = $length;
        }

        $maxChars = 12000;
        if (mb_strlen($text) > $maxChars) {
            $text = mb_substr($text, 0, $maxChars) . "\n[... ข้อความตัดท้าย ...]";
        }

        $prompt = $this->buildPrompt($text);
        $responseText = $this->callApi($prompt);
        if (isset($responseText['error'])) {
            $this->summaryLength = $saveLength;
            return $responseText;
        }

        $result = $this->parseResponse($responseText['text']);
        $this->summaryLength = $saveLength;
        return $result;
    }

    protected function getLengthInstructions(): string
    {
        $instructions = [
            'short' => 'สรุปสั้นมาก: รายละเอียดใช้ 1–3 ประโยคเท่านั้น เน้นเฉพาะประเด็นหลัก',
            'medium' => 'สรุปปานกลาง: รายละเอียดใช้ 3–6 ประโยค ครอบคลุมวัตถุประสงค์ สาระสำคัญ และสิ่งที่ต้องดำเนินการ',
            'long' => 'สรุปยาว/ครบถ้วน: รายละเอียดใช้ 5–10 ประโยค ครอบคลุมวัตถุประสงค์ ประเด็นหลัก สาระสำคัญ สิ่งที่ต้องดำเนินการ และข้อสรุป',
        ];
        return $instructions[$this->summaryLength] ?? $instructions['medium'];
    }

    protected function buildPrompt(string $text): string
    {
        $lengthGuide = $this->getLengthInstructions();
        return "คุณเป็นผู้ช่วยสรุปเอกสารราชการ/หนังสือราชการ จงสรุปข้อความด้านล่างเป็นภาษาไทย:\n\n"
            . "1) หัวเรื่อง: สรุปเป็นหนึ่งบรรทัดว่าเอกสารนี้เกี่ยวกับอะไร\n"
            . "2) รายละเอียด: {$lengthGuide}\n\n"
            . "ตอบเฉพาะในรูปแบบนี้เท่านั้น (ห้ามมีข้อความอื่นนำหน้าหรือตามหลัง):\n"
            . "หัวเรื่อง: <ข้อความหนึ่งบรรทัด>\n"
            . "รายละเอียด: <ข้อความสรุปใจความ>\n\n"
            . "ข้อความที่จะสรุป:\n---\n{$text}\n---";
    }

    /**
     * เรียก Gemini generateContent API
     * @return array ['text' => string] หรือ ['error' => string]
     */
    /** คืนชื่อโมเดลที่ส่งไป API (แมปชื่อเก่าเป็นชื่อที่ v1 รองรับ) */
    protected function getResolvedModel(): string
    {
        return self::$modelAliases[$this->model] ?? $this->model;
    }

    protected function callApi(string $prompt): array
    {
        $model = $this->getResolvedModel();
        $url = rtrim($this->baseUrl, '/') . '/models/' . $model . ':generateContent?key=' . urlencode($this->apiKey);
        $client = new Client(['timeout' => $this->timeout]);

        try {
            $response = $client->post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                ],
            ]);

            $body = (string) $response->getBody();
            $data = json_decode($body, true);
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if ($text === '') {
                return ['error' => 'Gemini ไม่ได้คืนข้อความ'];
            }

            return ['text' => trim($text)];
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            if ($e->hasResponse()) {
                $body = (string) $e->getResponse()->getBody();
                $decoded = json_decode($body, true);
                if (!empty($decoded['error']['message'])) {
                    $msg = $decoded['error']['message'];
                }
            }
            Yii::warning('Gemini request failed: ' . $msg, __METHOD__);
            return ['error' => 'เชื่อมต่อ Gemini ไม่ได้: ' . $msg];
        } catch (\Exception $e) {
            Yii::warning('Gemini request failed: ' . $e->getMessage(), __METHOD__);
            return ['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }
    }

    protected function parseResponse(string $text): array
    {
        $topic = '';
        $des = '';

        if (preg_match('/หัวเรื่อง\s*:\s*(.+?)(?=\n|รายละเอียด|$)/us', $text, $m)) {
            $topic = trim(preg_replace('/\s+/', ' ', $m[1]));
        }
        if (preg_match('/รายละเอียด\s*:\s*(.+)/us', $text, $m)) {
            $des = trim(preg_replace('/\s+/', ' ', $m[1]));
        }

        if ($topic === '' && $des === '') {
            $lines = array_filter(explode("\n", $text));
            $topic = $lines[0] ?? $text;
            $des = implode(' ', array_slice($lines, 1)) ?: $text;
        }

        $maxDes = self::$desMaxLength[$this->summaryLength] ?? 2000;
        return [
            'topic' => mb_substr($topic, 0, 500),
            'des' => mb_substr($des, 0, $maxDes),
        ];
    }

    /** โมเดลที่เลือกใช้ได้ (สำหรับฟอร์มตั้งค่า — ชื่อต้องตรงกับ API v1) */
    public static function getModelOptions(): array
    {
        return [
            'gemini-1.5-flash-latest' => 'Gemini 1.5 Flash (เร็ว)',
            'gemini-1.5-pro-latest' => 'Gemini 1.5 Pro',
            'gemini-2.5-flash' => 'Gemini 2.5 Flash',
            'gemini-2.5-pro' => 'Gemini 2.5 Pro',
            'gemini-1.0-pro' => 'Gemini 1.0 Pro',
        ];
    }
}
