<?php

namespace app\components;

use Yii;
use yii\base\Component;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * สรุปเนื้อหาด้วย OpenAI API (หรือ API ที่ใช้รูปแบบเดียวกัน)
 * ใช้สำหรับ DMS เมื่อเลือก "API ข้างนอก" — ต้องตั้ง OPENAI_API_KEY ใน .env
 */
class OpenAISummarizer extends Component
{
    /** @var string API Key (จาก .env: OPENAI_API_KEY) */
    public $apiKey;

    /** @var string โมเดล (เช่น gpt-4o-mini, gpt-3.5-turbo) */
    public $model = 'gpt-4o-mini';

    /** @var string base URL (สำหรับ Azure หรือ proxy) */
    public $baseUrl = 'https://api.openai.com/v1';

    /** @var int timeout วินาที */
    public $timeout = 60;

    /** ความยาวสูงสุดของรายละเอียด (ตัวอักษร) ต่อโหมด — ตรงกับ OllamaSummarizer */
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
            $this->apiKey = getenv('OPENAI_API_KEY') ?: (Yii::$app->params['openaiApiKey'] ?? '');
        }
        if (isset(Yii::$app->params['openaiModel'])) {
            $this->model = Yii::$app->params['openaiModel'];
        }
    }

    /**
     * สรุปข้อความ คืนค่า topic และ des (รูปแบบเดียวกับ OllamaSummarizer)
     * @param string $text ข้อความจาก PDF
     * @param string|null $length short, medium, long
     * @return array ['topic' => string, 'des' => string] หรือ ['error' => string]
     */
    public function summarize(string $text, ?string $length = null): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'ยังไม่ได้ตั้งค่า OPENAI_API_KEY ใน .env สำหรับการสรุปด้วย API ข้างนอก'];
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
     * เรียก OpenAI Chat Completions API
     * @return array ['text' => string] หรือ ['error' => string]
     */
    protected function callApi(string $prompt): array
    {
        $url = rtrim($this->baseUrl, '/') . '/chat/completions';
        $client = new Client(['timeout' => $this->timeout]);

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ],
            ]);

            $body = (string) $response->getBody();
            $data = json_decode($body, true);
            $text = $data['choices'][0]['message']['content'] ?? '';
            if ($text === '') {
                return ['error' => 'API ไม่ได้คืนข้อความ'];
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
            Yii::warning('OpenAI request failed: ' . $msg, __METHOD__);
            return ['error' => 'เชื่อมต่อ API ไม่ได้: ' . $msg];
        } catch (\Exception $e) {
            Yii::warning('OpenAI request failed: ' . $e->getMessage(), __METHOD__);
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

    /** โมเดลที่เลือกใช้ได้ (สำหรับฟอร์มตั้งค่า) */
    public static function getModelOptions(): array
    {
        return [
            'gpt-4o-mini' => 'GPT-4o mini (เร็ว, ประหยัด)',
            'gpt-4o' => 'GPT-4o',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        ];
    }
}
