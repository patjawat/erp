<?php

declare(strict_types=1);

namespace app\modules\ai\services;

use app\modules\ai\contracts\AiProviderResponse;
use app\modules\ai\models\AiConversation;
use app\modules\ai\models\AiMessage;
use app\modules\ai\providers\OpenRouterProvider;
use app\modules\ai\security\PermissionChecker;
use app\modules\ai\tools\ToolRegistry;
use Yii;
use yii\web\BadRequestHttpException;

class AiChatOrchestrator
{
    public function __construct(
        private ?AiProviderFactory $providerFactory = null,
        private ?DatasetRegistry $datasetRegistry = null,
        private ?ToolRegistry $toolRegistry = null,
        private ?ConversationService $conversationService = null,
        private ?PermissionChecker $permissionChecker = null,
        private ?AuditLogger $auditLogger = null
    ) {
        $this->providerFactory = $providerFactory ?: new AiProviderFactory();
        $this->datasetRegistry = $datasetRegistry ?: new DatasetRegistry();
        $this->toolRegistry = $toolRegistry ?: new ToolRegistry();
        $this->conversationService = $conversationService ?: new ConversationService();
        $this->permissionChecker = $permissionChecker ?: new PermissionChecker();
        $this->auditLogger = $auditLogger ?: new AuditLogger();
    }

    /**
     * @return array<string, mixed>
     */
    public function sendMessage(string $message, ?string $conversationId = null, ?string $providerCode = null): array
    {
        $this->permissionChecker->requirePermission('ai.chat.use');
        $message = trim($message);
        if ($message === '') {
            throw new BadRequestHttpException('Message is required.');
        }

        $provider = $this->providerFactory->create($providerCode);
        $model = $provider instanceof OpenRouterProvider ? $provider->model : null;
        $conversation = $this->conversationService->getOrCreate(
            $conversationId,
            (int) Yii::$app->user->id,
            $provider->getCode(),
            $model,
            $message
        );

        $userMessage = $this->conversationService->addMessage(
            $conversation->id,
            AiMessage::ROLE_USER,
            $message,
            $provider->getCode()
        );

        $messages = $this->buildProviderMessages($conversation->id);
        $response = $provider->chat($messages, $this->toolRegistry->definitions());
        $finalResponse = $response;
        $toolResults = [];

        if ($response->hasToolCalls()) {
            $this->conversationService->addMessage(
                $conversation->id,
                AiMessage::ROLE_ASSISTANT,
                $response->getContent() ?: 'เรียกใช้เครื่องมือของระบบ',
                $provider->getCode(),
                null,
                null,
                ['tool_calls' => $response->getToolCalls(), 'provider' => $response->getMetadata()]
            );

            foreach ($response->getToolCalls() as $toolCall) {
                $result = $this->toolRegistry->execute(
                    $toolCall['name'],
                    (array) ($toolCall['arguments'] ?? []),
                    $conversation->id,
                    $provider->getCode()
                );
                $toolResults[] = $result;

                $this->conversationService->addMessage(
                    $conversation->id,
                    AiMessage::ROLE_TOOL,
                    json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    $provider->getCode(),
                    $toolCall['name'],
                    $toolCall['id'] ?? null
                );
            }

            $finalResponse = $provider->chat(
                $this->buildToolResultMessages($conversation->id, $toolResults),
                [],
                ['tool_choice' => 'none']
            );
        }

        $assistantMessage = $this->conversationService->addMessage(
            $conversation->id,
            AiMessage::ROLE_ASSISTANT,
            $finalResponse->getContent(),
            $provider->getCode(),
            null,
            null,
            $this->responseMetadata($finalResponse, $toolResults)
        );

        $this->auditLogger->log([
            'conversation_id' => $conversation->id,
            'message_id' => $assistantMessage->id,
            'provider' => $provider->getCode(),
            'action' => 'chat',
            'status' => 'success',
            'request' => ['message_id' => $userMessage->id],
            'response' => ['message_id' => $assistantMessage->id, 'tool_count' => count($toolResults)],
        ]);

        return [
            'conversation_id' => $conversation->id,
            'message_id' => $assistantMessage->id,
            'provider' => $provider->getCode(),
            'model' => $finalResponse->getMetadata()['model'] ?? $model,
            'fallback_from' => $finalResponse->getMetadata()['fallback_from'] ?? null,
            'content' => $finalResponse->getContent(),
            'tool_results' => $toolResults,
        ];
    }

    public function streamMessage(
        string $message,
        callable $onDelta,
        ?string $conversationId = null,
        ?string $providerCode = null,
        ?callable $onStatus = null
    ): array
    {
        $this->emitStatus($onStatus, 'connecting');
        $this->permissionChecker->requirePermission('ai.chat.use');
        $message = trim($message);
        if ($message === '') {
            throw new BadRequestHttpException('Message is required.');
        }

        $provider = $this->providerFactory->create($providerCode);
        $model = $provider instanceof OpenRouterProvider ? $provider->model : null;
        $conversation = $this->conversationService->getOrCreate(
            $conversationId,
            (int) Yii::$app->user->id,
            $provider->getCode(),
            $model,
            $message
        );
        $userMessage = $this->conversationService->addMessage(
            $conversation->id,
            AiMessage::ROLE_USER,
            $message,
            $provider->getCode()
        );

        $this->emitStatus($onStatus, 'thinking');
        $response = $provider->chat(
            $this->buildProviderMessages($conversation->id),
            $this->toolRegistry->definitions()
        );
        $finalResponse = $response;
        $toolResults = [];

        if ($response->hasToolCalls()) {
            $this->conversationService->addMessage(
                $conversation->id,
                AiMessage::ROLE_ASSISTANT,
                $response->getContent() ?: 'เรียกใช้เครื่องมือของระบบ',
                $provider->getCode(),
                null,
                null,
                ['tool_calls' => $response->getToolCalls(), 'provider' => $response->getMetadata()]
            );

            $this->emitStatus($onStatus, 'searching');
            foreach ($response->getToolCalls() as $toolCall) {
                $result = $this->toolRegistry->execute(
                    $toolCall['name'],
                    (array) ($toolCall['arguments'] ?? []),
                    $conversation->id,
                    $provider->getCode()
                );
                $toolResults[] = $result;

                $this->conversationService->addMessage(
                    $conversation->id,
                    AiMessage::ROLE_TOOL,
                    json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    $provider->getCode(),
                    $toolCall['name'],
                    $toolCall['id'] ?? null
                );
            }

            $this->emitStatus($onStatus, 'composing');
            $toolMessages = $this->buildToolResultMessages($conversation->id, $toolResults);
            if ($provider->supportsStreaming()) {
                $finalResponse = $provider->stream($toolMessages, $onDelta, [], ['tool_choice' => 'none']);
            } else {
                $finalResponse = $provider->chat($toolMessages, [], ['tool_choice' => 'none']);
                $this->emitProgressiveDeltas($finalResponse->getContent(), $onDelta);
            }
        } else {
            $this->emitStatus($onStatus, 'composing');
            $this->emitProgressiveDeltas($response->getContent(), $onDelta);
        }

        $assistantMessage = $this->conversationService->addMessage(
            $conversation->id,
            AiMessage::ROLE_ASSISTANT,
            $finalResponse->getContent(),
            $provider->getCode(),
            null,
            null,
            $this->responseMetadata($finalResponse, $toolResults)
        );

        $this->auditLogger->log([
            'conversation_id' => $conversation->id,
            'message_id' => $assistantMessage->id,
            'provider' => $provider->getCode(),
            'action' => 'chat_stream',
            'status' => 'success',
            'request' => ['message_id' => $userMessage->id],
            'response' => ['message_id' => $assistantMessage->id, 'tool_count' => count($toolResults)],
        ]);

        return [
            'conversation_id' => $conversation->id,
            'message_id' => $assistantMessage->id,
            'provider' => $provider->getCode(),
            'model' => $finalResponse->getMetadata()['model'] ?? $model,
            'fallback_from' => $finalResponse->getMetadata()['fallback_from'] ?? null,
            'content' => $finalResponse->getContent(),
            'tool_results' => $toolResults,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function providerCodes(): array
    {
        return $this->providerFactory->getProviderCodes();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildProviderMessages(string $conversationId): array
    {
        return array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt()]],
            $this->conversationService->providerMessages($conversationId)
        );
    }

    /**
     * @param array<int, array<string, mixed>> $toolResults
     * @return array<int, array<string, mixed>>
     */
    private function buildToolResultMessages(string $conversationId, array $toolResults): array
    {
        $messages = $this->buildProviderMessages($conversationId);
        $messages[] = [
            'role' => 'user',
            'content' => "ผลลัพธ์จาก Yii2 tools อยู่ด้านล่าง ให้ตอบผู้ใช้เป็นภาษาไทย กระชับ ตรวจสอบว่ามี error หรือ permission issue ก่อนสรุป ห้ามเดา field/table ที่ไม่มี:\n"
                . json_encode($toolResults, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        return $messages;
    }

    private function systemPrompt(): string
    {
        return implode("\n", [
            'คุณคือ AI Assistant ภายในระบบ Yii2 ERP ขององค์กร',
            'คุณต้องตอบเป็นภาษาไทย เว้นแต่ผู้ใช้ขอภาษาอื่น',
            'ห้ามสร้าง SQL เอง ห้ามอ้างชื่อตารางจริง ห้ามเขียนข้อมูลลงฐานข้อมูล และห้ามข้าม permission',
            'เมื่อต้องใช้ข้อมูลจากระบบ ให้เรียก tool query_dataset หรือ export_excel เท่านั้น',
            'ขอบเขต v1 รองรับ AI Chat, Query Dataset และ Export Excel เท่านั้น ยังไม่รองรับ image, PDF, PowerPoint, Word, การแก้ไขข้อมูล หรือการอนุมัติรายการ',
            'Dataset ที่ใช้งานได้มีเฉพาะ registry ต่อไปนี้:',
            json_encode($this->datasetRegistry->asPromptCatalog(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ถ้าข้อมูลไม่พอ ให้ถามกลับหรืออธิบายว่าต้องเพิ่ม AI view/register dataset ก่อน',
            'วันที่ระบบปัจจุบัน: ' . date('Y-m-d'),
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $toolResults
     * @return array<string, mixed>
     */
    private function responseMetadata(AiProviderResponse $response, array $toolResults = []): array
    {
        return [
            'provider' => $response->getMetadata(),
            'finish_reason' => $response->getFinishReason(),
            'tool_results' => $toolResults,
        ];
    }

    private function emitStatus(?callable $onStatus, string $status): void
    {
        if ($onStatus !== null) {
            $onStatus($status);
        }
    }

    private function emitProgressiveDeltas(string $content, callable $onDelta): void
    {
        if ($content === '') {
            return;
        }

        preg_match_all('/\\X/u', $content, $matches);
        $characters = $matches[0] ?? [];
        if ($characters === []) {
            $onDelta($content);
            return;
        }

        $chunks = [];
        $buffer = '';
        $length = 0;
        foreach ($characters as $character) {
            $buffer .= $character;
            $length++;

            $hardBoundary = preg_match('/[\\n.!?。！？]\\s*$/u', $buffer) === 1;
            $softBoundary = preg_match('/[\\s,;:ๆฯ…]$/u', $buffer) === 1;
            if (($hardBoundary && $length >= 14) || ($softBoundary && $length >= 28) || $length >= 44) {
                $chunks[] = $buffer;
                $buffer = '';
                $length = 0;
            }
        }

        if ($buffer !== '') {
            $chunks[] = $buffer;
        }

        $lastIndex = count($chunks) - 1;
        foreach ($chunks as $index => $chunk) {
            $onDelta($chunk);
            if ($index !== $lastIndex) {
                usleep(24000);
            }
        }
    }
}
