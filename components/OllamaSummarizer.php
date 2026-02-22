<?php

namespace app\components;

use Yii;
use yii\base\Component;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * สรุปเนื้อหาข้อความด้วย Ollama (รันใน Docker)
 * ใช้สำหรับ DMS สรุปเรื่องและรายละเอียดจาก PDF
 */
class OllamaSummarizer extends Component
{
    /** @var string URL ของ Ollama (เช่น http://ollama:11434) */
    public $baseUrl;

    /** @var string โมเดลที่ใช้ (เช่น llama3.2, mistral) */
    public $model = 'llama3.2';

    /** @var int timeout วินาที */
    public $timeout = 120;

    public function init()
    {
        parent::init();
        if (empty($this->baseUrl)) {
            $this->baseUrl = Yii::$app->params['ollamaUrl'] ?? 'http://ollama:11434';
        }
        if (isset(Yii::$app->params['ollamaModel'])) {
            $this->model = Yii::$app->params['ollamaModel'];
        }
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

    protected function buildPrompt(string $text): string
    {
        return <<<PROMPT
คุณเป็นผู้ช่วยสรุปเอกสารราชการ/หนังสือราชการ จงสรุปข้อความด้านล่างเป็น 2 ส่วนเท่านั้น (ตอบเป็นภาษาไทย):

1) หัวเรื่อง: สรุปเป็นหนึ่งบรรทัดสั้นๆ ว่าเอกสารนี้เกี่ยวกับอะไร
2) รายละเอียด: สรุปเนื้อหาสำคัญ 2-5 ประโยค

ตอบเฉพาะในรูปแบบนี้เท่านั้น (ห้ามมีข้อความอื่นนำหน้าหรือตามหลัง):
หัวเรื่อง: <ข้อความหนึ่งบรรทัด>
รายละเอียด: <ข้อความสรุป>

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

        return [
            'topic' => mb_substr($topic, 0, 500),
            'des' => mb_substr($des, 0, 2000),
        ];
    }
}
