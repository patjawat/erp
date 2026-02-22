<?php

namespace app\components;

use Yii;
use yii\base\Component;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * สรุปเนื้อหาข้อความด้วย Ollama (รันใน Docker)
 * ใช้สำหรับ DMS สรุปเรื่องและรายละเอียดจาก PDF
 * ความยาวการสรุป: short, medium, long (ตั้งใน params หรือหน้า ตั้งค่า > AI สรุป)
 */
class OllamaSummarizer extends Component
{
    /** @var string URL ของ Ollama (เช่น http://ollama:11434) */
    public $baseUrl;

    /** @var string โมเดลที่ใช้ (เช่น llama3.2, mistral) */
    public $model = 'llama3.2';

    /** @var int timeout วินาที */
    public $timeout = 120;

    /** @var string ความยาวการสรุป: short, medium, long */
    public $summaryLength = 'medium';

    /** ความยาวสูงสุดของรายละเอียด (ตัวอักษร) ต่อโหมด */
    protected static $desMaxLength = [
        'short' => 800,
        'medium' => 2000,
        'long' => 4000,
    ];

    public function init()
    {
        parent::init();
        if (empty($this->baseUrl)) {
            $this->baseUrl = Yii::$app->params['ollamaUrl'] ?? 'http://ollama:11434';
        }
        if (isset(Yii::$app->params['ollamaModel'])) {
            $this->model = Yii::$app->params['ollamaModel'];
        }
        $this->summaryLength = $this->resolveSummaryLength();
    }

    /** อ่านค่าความยาวจากไฟล์ตั้งค่า (DMS) หรือ params */
    protected function resolveSummaryLength(): string
    {
        $file = Yii::getAlias('@runtime/dms-ai-summary-settings.json');
        if (is_file($file) && is_readable($file)) {
            $data = @json_decode(file_get_contents($file), true);
            if (!empty($data['summary_length']) && isset(self::$desMaxLength[$data['summary_length']])) {
                return $data['summary_length'];
            }
        }
        $p = Yii::$app->params['ollamaSummaryLength'] ?? 'medium';
        return isset(self::$desMaxLength[$p]) ? $p : 'medium';
    }

    /**
     * สรุปข้อความแล้วคืนค่า topic (หัวเรื่องหนึ่งบรรทัด) และ des (รายละเอียดสรุป)
     * @param string $text ข้อความจาก PDF (หรือที่อื่น)
     * @return array ['topic' => string, 'des' => string] หรือ ['error' => string]
     */
    public function summarize(string $text): array
    {
        $text = trim($text);
        if (mb_strlen($text) < 50) {
            return ['error' => 'ข้อความสั้นเกินไปสำหรับสรุป'];
        }

        // ลดความยาวถ้ายาวมาก (Ollama จำ context)
        $maxChars = 12000;
        if (mb_strlen($text) > $maxChars) {
            $text = mb_substr($text, 0, $maxChars) . "\n[... ข้อความตัดท้าย ...]";
        }

        $prompt = $this->buildPrompt($text);
        $responseText = $this->callOllama($prompt);
        if (isset($responseText['error'])) {
            return $responseText;
        }

        return $this->parseResponse($responseText['text']);
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
        return <<<PROMPT
คุณเป็นผู้ช่วยสรุปเอกสารราชการ/หนังสือราชการ จงสรุปข้อความด้านล่างเป็นภาษาไทย:

1) หัวเรื่อง: สรุปเป็นหนึ่งบรรทัดว่าเอกสารนี้เกี่ยวกับอะไร
2) รายละเอียด: {$lengthGuide}

ตอบเฉพาะในรูปแบบนี้เท่านั้น (ห้ามมีข้อความอื่นนำหน้าหรือตามหลัง):
หัวเรื่อง: <ข้อความหนึ่งบรรทัด>
รายละเอียด: <ข้อความสรุปใจความ>

ข้อความที่จะสรุป:
---
{$text}
---
PROMPT;
    }

    /**
     * เรียก Ollama API
     * @return array ['text' => string] หรือ ['error' => string]
     */
    protected function callOllama(string $prompt): array
    {
        $url = rtrim($this->baseUrl, '/') . '/api/generate';
        $client = new Client(['timeout' => $this->timeout]);

        try {
            $response = $client->post($url, [
                'json' => [
                    'model' => $this->model,
                    'prompt' => $prompt,
                    'stream' => false,
                ],
            ]);

            $body = (string) $response->getBody();
            $data = json_decode($body, true);
            $text = $data['response'] ?? '';
            if ($text === '') {
                return ['error' => 'Ollama ไม่ได้คืนข้อความ'];
            }

            return ['text' => $text];
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            if ($e->hasResponse()) {
                $msg = (string) $e->getResponse()->getBody();
            }
            Yii::warning('Ollama request failed: ' . $msg, __METHOD__);
            return [
                'error' => 'เชื่อมต่อ Ollama ไม่ได้ ตรวจสอบว่า container ollama รันอยู่ (docker compose up -d ollama) และโหลดโมเดลแล้ว (docker exec -it ... ollama pull llama3.2)',
            ];
        } catch (\Exception $e) {
            Yii::warning('Ollama request failed: ' . $e->getMessage(), __METHOD__);
            return [
                'error' => 'เชื่อมต่อ Ollama ไม่ได้: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * แยกข้อความตอบเป็น topic และ des
     */
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

    /** คืนค่าตัวเลือกความยาวสำหรับฟอร์มตั้งค่า */
    public static function getSummaryLengthOptions(): array
    {
        return [
            'short' => 'สั้น (1–3 ประโยค)',
            'medium' => 'ปานกลาง (3–6 ประโยค)',
            'long' => 'ยาว/ครบถ้วน (5–10 ประโยค)',
        ];
    }
}
