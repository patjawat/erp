<?php

declare(strict_types=1);

namespace app\modules\ai\controllers;

use app\modules\ai\models\AiMessage;
use app\modules\ai\security\PermissionChecker;
use app\modules\ai\services\AuditLogger;
use app\modules\ai\services\AiChatOrchestrator;
use app\modules\ai\services\ConversationService;
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
        ]);
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
                'error' => $exception->getMessage(),
            ];
        }
    }

    public function actionStream(): void
    {
        $payload = $this->requestPayload();
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'text/event-stream; charset=UTF-8');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->sendHeaders();

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        try {
            $result = (new AiChatOrchestrator())->streamMessage(
                (string) ($payload['message'] ?? ''),
                static function (string $delta): void {
                    echo 'event: delta' . "\n";
                    echo 'data: ' . json_encode(['delta' => $delta], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
                    flush();
                },
                $payload['conversation_id'] ?? null,
                $payload['provider'] ?? null
            );

            echo 'event: done' . "\n";
            echo 'data: ' . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
            flush();
        } catch (Throwable $exception) {
            (new AuditLogger())->log([
                'conversation_id' => $payload['conversation_id'] ?? null,
                'provider' => $payload['provider'] ?? null,
                'action' => 'chat_stream',
                'status' => 'error',
                'error_message' => $exception->getMessage(),
            ]);
            echo 'event: error' . "\n";
            echo 'data: ' . json_encode(['error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
            flush();
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
}
