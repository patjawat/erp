<?php

declare(strict_types=1);

namespace app\modules\ai\controllers;

use app\modules\ai\models\AiMessage;
use app\modules\ai\providers\OpenRouterProvider;
use app\modules\ai\security\PermissionChecker;
use app\modules\ai\services\AuditLogger;
use app\modules\ai\services\AiChatOrchestrator;
use app\modules\ai\services\AiProviderFactory;
use app\modules\ai\services\ConversationService;
use app\modules\ai\services\OpenRouterCredentialStore;
use Throwable;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class ChatController extends Controller
{
    private PermissionChecker $permissionChecker;

    public function __construct($id, $module, $config = [])
    {
        $this->permissionChecker = new PermissionChecker();
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => fn ($rule, $action): bool => $this->permissionChecker->can('ai.chat.use'),
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'send' => ['POST'],
                    'stream' => ['POST'],
                    'openrouter-connection' => ['GET', 'POST'],
                    'openrouter-models' => ['GET', 'POST'],
                    'history' => ['GET'],
                    'conversations' => ['GET'],
                ],
            ],
        ];
    }

    public function actionIndex(?string $id = null): string
    {
        $orchestrator = new AiChatOrchestrator();
        $conversationService = new ConversationService();
        $userId = (int) Yii::$app->user->id;

        return $this->render('index', [
            'conversationId' => $id,
            'providerCodes' => $orchestrator->providerCodes(),
            'conversations' => $conversationService->listForUser($userId),
            'messages' => $id ? $conversationService->messagesForUser($id, $userId) : [],
            'openRouterConnection' => (new OpenRouterCredentialStore())->status(),
        ]);
    }

    public function actionOpenrouterConnection(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $store = new OpenRouterCredentialStore();
        if (Yii::$app->request->isGet) {
            return [
                'success' => true,
                'data' => $store->status(),
            ];
        }

        $payload = $this->requestPayload();
        if (array_key_exists('assistant_widget_enabled', $payload)) {
            $enabled = filter_var($payload['assistant_widget_enabled'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($enabled === null) {
                Yii::$app->response->statusCode = 400;

                return [
                    'success' => false,
                    'error' => 'ค่าการแสดงผู้ช่วย AI ไม่ถูกต้อง',
                ];
            }

            $store->saveAssistantWidgetEnabled($enabled);

            return [
                'success' => true,
                'data' => $store->status(),
            ];
        }

        $clear = ($payload['clear'] ?? false) === true || (string) ($payload['clear'] ?? '') === '1';
        if ($clear) {
            $store->clearApiKey();

            return [
                'success' => true,
                'data' => $store->status(),
            ];
        }

        $apiKey = trim((string) ($payload['api_key'] ?? ''));
        if ($apiKey === '') {
            Yii::$app->response->statusCode = 400;

            return [
                'success' => false,
                'error' => 'กรุณากรอก OpenRouter API key',
            ];
        }

        try {
            $config = Yii::$app->getModule('ai')->providers['openrouter'] ?? ['class' => OpenRouterProvider::class];
            $provider = Yii::createObject($config);
            if (!$provider instanceof OpenRouterProvider) {
                throw new \RuntimeException('OpenRouter provider is not configured.');
            }

            $provider->apiKey = $apiKey;
            $provider->validateConnection();
            $store->saveApiKey($apiKey);

            return [
                'success' => true,
                'data' => array_merge($store->status(), ['message' => 'เชื่อมต่อ OpenRouter สำเร็จ']),
            ];
        } catch (Throwable $exception) {
            Yii::$app->response->statusCode = 400;

            return [
                'success' => false,
                'error' => $this->userFacingAiError($exception),
            ];
        }
    }

    public function actionOpenrouterModels(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $store = new OpenRouterCredentialStore();
            if ($store->getApiKey() === null) {
                Yii::$app->response->statusCode = 400;

                return [
                    'success' => false,
                    'error' => 'กรุณาเชื่อมต่อ OpenRouter API key ก่อนเลือกโมเดล',
                ];
            }

            $provider = (new AiProviderFactory())->create('openrouter');
            if (!$provider instanceof OpenRouterProvider) {
                throw new \RuntimeException('ระบบยังไม่ได้ตั้งค่า OpenRouter provider');
            }

            $models = $this->openRouterModels($provider, $store->getApiKey());
            if ($models === []) {
                throw new \RuntimeException('ไม่พบโมเดลที่ใช้งานได้สำหรับ API key นี้');
            }

            if (Yii::$app->request->isPost) {
                $payload = $this->requestPayload();
                $selectedModel = trim((string) ($payload['model'] ?? ''));
                $availableModelIds = array_column($models, 'id');

                if ($selectedModel === '' || !in_array($selectedModel, $availableModelIds, true)) {
                    Yii::$app->response->statusCode = 400;

                    return [
                        'success' => false,
                        'error' => 'โมเดลที่เลือกไม่อยู่ในรายการของ API key นี้',
                    ];
                }

                $store->saveSelectedModel($selectedModel);

                return [
                    'success' => true,
                    'data' => ['selected_model' => $selectedModel],
                ];
            }

            $selectedModel = $store->getSelectedModel();
            $availableModelIds = array_column($models, 'id');
            if ($selectedModel === null || !in_array($selectedModel, $availableModelIds, true)) {
                $selectedModel = $models[0]['id'];
                $store->saveSelectedModel($selectedModel);
            }

            return [
                'success' => true,
                'data' => [
                    'models' => $models,
                    'selected_model' => $selectedModel,
                ],
            ];
        } catch (Throwable $exception) {
            Yii::$app->response->statusCode = 400;

            return [
                'success' => false,
                'error' => $this->userFacingAiError($exception),
            ];
        }
    }

    public function actionSend(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $payload = $this->requestPayload();

        try {
            return [
                'success' => true,
                'data' => (new AiChatOrchestrator())->sendMessage(
                    (string) ($payload['message'] ?? ''),
                    $payload['conversation_id'] ?? null,
                    $payload['provider'] ?? null
                ),
            ];
        } catch (Throwable $exception) {
            (new AuditLogger())->log([
                'conversation_id' => $payload['conversation_id'] ?? null,
                'provider' => $payload['provider'] ?? null,
                'action' => 'chat',
                'status' => 'error',
                'error_message' => $exception->getMessage(),
            ]);
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'error' => $this->userFacingAiError($exception),
            ];
        }
    }

    public function actionStream(): void
    {
        $payload = $this->requestPayload();
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'text/event-stream; charset=UTF-8');
        $response->headers->set('Cache-Control', 'no-cache, no-transform');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->content = '';
        $response->send();

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        try {
            $result = (new AiChatOrchestrator())->streamMessage(
                (string) ($payload['message'] ?? ''),
                function (string $delta): void {
                    $this->emitSseEvent('delta', ['delta' => $delta]);
                },
                $payload['conversation_id'] ?? null,
                $payload['provider'] ?? null,
                function (string $status): void {
                    $this->emitSseEvent('status', ['status' => $status]);
                }
            );

            $this->emitSseEvent('status', ['status' => 'ready']);
            $this->emitSseEvent('done', $result);
        } catch (Throwable $exception) {
            (new AuditLogger())->log([
                'conversation_id' => $payload['conversation_id'] ?? null,
                'provider' => $payload['provider'] ?? null,
                'action' => 'chat_stream',
                'status' => 'error',
                'error_message' => $exception->getMessage(),
            ]);
            $this->emitSseEvent('error', ['error' => $this->userFacingAiError($exception)]);
        }

        Yii::$app->end();
    }

    public function actionConversations(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $conversations = (new ConversationService())->listForUser((int) Yii::$app->user->id);

        return [
            'success' => true,
            'data' => array_map(static fn ($conversation): array => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'provider' => $conversation->provider,
                'status' => $conversation->status,
                'updated_at' => $conversation->updated_at,
            ], $conversations),
        ];
    }

    public function actionHistory(string $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $messages = (new ConversationService())->messagesForUser($id, (int) Yii::$app->user->id);

        return [
            'success' => true,
            'data' => array_map(static fn (AiMessage $message): array => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'tool_name' => $message->tool_name,
                'created_at' => $message->created_at,
            ], $messages),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function requestPayload(): array
    {
        $body = json_decode(Yii::$app->request->rawBody, true);
        if (!is_array($body)) {
            $body = [];
        }

        return array_merge(Yii::$app->request->post(), $body);
    }

    private function userFacingAiError(Throwable $exception): string
    {
        $message = trim($exception->getMessage());
        if (preg_match('/[\x{0E00}-\x{0E7F}]/u', $message) === 1) {
            return $message;
        }
        if (preg_match('/HTTP status 402|more credits|fewer max_tokens/i', $message) === 1) {
            return 'เครดิต OpenRouter ไม่เพียงพอสำหรับโมเดลนี้ กรุณาเลือกโมเดลฟรีหรือเติมเครดิตแล้วลองอีกครั้ง';
        }
        if (preg_match('/HTTP status 429|rate.?limit|too many requests/i', $message) === 1) {
            return 'OpenRouter กำลังมีคำขอหนาแน่น กรุณารอสักครู่แล้วลองใหม่ หรือเลือกโมเดลอื่น';
        }
        if (preg_match('/HTTP status (401|403)|api key|not configured/i', $message) === 1) {
            return 'เชื่อมต่อ OpenRouter ไม่สำเร็จ กรุณาตรวจสอบ API key และโมเดลที่เลือก';
        }
        if (preg_match('/HTTP status 5\d\d|provider (request|stream) failed/i', $message) === 1) {
            return 'OpenRouter ไม่สามารถตอบกลับได้ในขณะนี้ กรุณาลองอีกครั้ง';
        }

        return 'ไม่สามารถรับคำตอบจาก OpenRouter ได้ กรุณาลองอีกครั้ง';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function emitSseEvent(string $event, array $data): void
    {
        echo 'event: ' . $event . "\n";
        echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
        flush();
    }

    /**
     * @return array<int, array{id: string, name: string, context_length: int|null, is_free: bool}>
     */
    private function openRouterModels(OpenRouterProvider $provider, ?string $apiKey): array
    {
        $cacheKey = 'ai.openrouter.models.v2.' . hash('sha256', (string) $apiKey);

        return Yii::$app->cache->getOrSet($cacheKey, static fn (): array => $provider->listModels(), 300);
    }
}
