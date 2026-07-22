<?php

declare(strict_types=1);

namespace app\modules\ai\services;

use app\modules\ai\models\AiConversation;
use app\modules\ai\models\AiMessage;
use yii\web\NotFoundHttpException;

class ConversationService
{
    public function getOrCreate(?string $conversationId, int $userId, string $provider, ?string $model, string $message): AiConversation
    {
        if ($conversationId !== null && $conversationId !== '') {
            return $this->findForUser($conversationId, $userId);
        }

        $conversation = new AiConversation();
        $conversation->user_id = $userId;
        $conversation->title = $this->makeTitle($message);
        $conversation->provider = $provider;
        $conversation->model = $model;
        $conversation->status = 'active';
        $conversation->metadata_json = '{}';
        $conversation->save(false);

        return $conversation;
    }

    public function findForUser(string $conversationId, int $userId): AiConversation
    {
        $conversation = AiConversation::find()
            ->where(['id' => $conversationId, 'user_id' => $userId])
            ->one();

        if (!$conversation instanceof AiConversation) {
            throw new NotFoundHttpException('AI conversation was not found.');
        }

        return $conversation;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function addMessage(
        string $conversationId,
        string $role,
        ?string $content,
        ?string $provider = null,
        ?string $toolName = null,
        ?string $toolCallId = null,
        array $metadata = []
    ): AiMessage {
        $message = new AiMessage();
        $message->conversation_id = $conversationId;
        $message->role = $role;
        $message->content = $content;
        $message->provider = $provider;
        $message->tool_name = $toolName;
        $message->tool_call_id = $toolCallId;
        $message->metadata_json = $metadata === [] ? null : json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $message->token_count = $metadata['token_count'] ?? null;
        $message->save(false);
        AiConversation::updateAll(['updated_at' => date('Y-m-d H:i:s')], ['id' => $conversationId]);

        return $message;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function providerMessages(string $conversationId, int $limit = 20): array
    {
        $messages = AiMessage::find()
            ->where(['conversation_id' => $conversationId, 'role' => [AiMessage::ROLE_USER, AiMessage::ROLE_ASSISTANT]])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();

        $messages = array_reverse($messages);
        return array_map(static fn (AiMessage $message): array => [
            'role' => $message->role,
            'content' => (string) $message->content,
        ], $messages);
    }

    /**
     * @return array<int, AiConversation>
     */
    public function listForUser(int $userId, int $limit = 30): array
    {
        return AiConversation::find()
            ->where(['user_id' => $userId])
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * @return array<int, AiMessage>
     */
    public function messagesForUser(string $conversationId, int $userId): array
    {
        $conversation = $this->findForUser($conversationId, $userId);
        return $conversation->messages;
    }

    private function makeTitle(string $message): string
    {
        $message = trim(preg_replace('/\s+/u', ' ', $message) ?: '');
        if ($message === '') {
            return 'New AI Conversation';
        }

        return function_exists('mb_substr') ? mb_substr($message, 0, 80) : substr($message, 0, 80);
    }
}
