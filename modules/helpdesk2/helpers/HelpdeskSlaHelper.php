<?php

namespace app\modules\helpdesk2\helpers;

use DateInterval;
use DateTime;
use Yii;
use yii\helpers\Html;
use app\modules\helpdesk2\models\Helpdesk;

class HelpdeskSlaHelper
{
    /**
     * Calculate SLA information for a ticket.
     *
     * @return array{
     *   deadline: string|null,
     *   secondsRemaining: int|null,
     *   status: string, // within|near|breached|no_sla
     *   priority: string|null
     * }
     */
    public static function calculate(Helpdesk $ticket): array
    {
        $createdAt = $ticket->created_at ? new DateTime($ticket->created_at) : null;
        if ($createdAt === null) {
            return [
                'deadline' => null,
                'secondsRemaining' => null,
                'status' => 'no_sla',
                'priority' => null,
            ];
        }

        [$priorityKey, $hours] = self::resolvePriorityAndHours($ticket);
        if ($hours === null) {
            return [
                'deadline' => null,
                'secondsRemaining' => null,
                'status' => 'no_sla',
                'priority' => $priorityKey,
            ];
        }

        $deadline = clone $createdAt;
        $deadline->add(new DateInterval('PT' . $hours . 'H'));

        $now = new DateTime('now');
        $secondsRemaining = (int) ($deadline->getTimestamp() - $now->getTimestamp());

        if ($secondsRemaining <= 0) {
            $status = 'breached';
        } elseif ($secondsRemaining <= 3600) {
            // ภายใน 1 ชั่วโมงสุดท้าย
            $status = 'near';
        } else {
            $status = 'within';
        }

        return [
            'deadline' => $deadline->format('Y-m-d H:i:s'),
            'secondsRemaining' => $secondsRemaining,
            'status' => $status,
            'priority' => $priorityKey,
        ];
    }

    /**
     * Map urgency in data_json to SLA hours.
     *
     * Low → 72h, Medium → 24h, High → 4h, Critical → 1h
     *
     * @return array{0: string|null, 1: int|null}
     */
    private static function resolvePriorityAndHours(Helpdesk $ticket): array
    {
        $urgency = $ticket->data_json['urgency'] ?? null;
        if ($urgency === null) {
            return [null, null];
        }

        // รองรับทั้งตัวเลข (1-4) และข้อความ (low, medium, high, critical)
        $map = [
            '1' => ['low', 72],
            '2' => ['medium', 24],
            '3' => ['high', 4],
            '4' => ['critical', 1],
            'low' => ['low', 72],
            'medium' => ['medium', 24],
            'high' => ['high', 4],
            'critical' => ['critical', 1],
        ];

        $key = (string) $urgency;
        if (!isset($map[$key])) {
            return [null, null];
        }

        return $map[$key];
    }

    /**
     * Return HTML badge for SLA status.
     */
    public static function renderBadge(Helpdesk $ticket): string
    {
        $info = self::calculate($ticket);

        if ($info['status'] === 'no_sla') {
            return '';
        }

        switch ($info['status']) {
            case 'within':
                $class = 'badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1';
                $label = 'ภายใน SLA';
                $icon = 'fa-regular fa-circle-check';
                break;
            case 'near':
                $class = 'badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1';
                $label = 'ใกล้ครบกำหนด SLA';
                $icon = 'fa-solid fa-triangle-exclamation';
                break;
            default:
                $class = 'badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1';
                $label = 'เกิน SLA';
                $icon = 'fa-solid fa-circle-exclamation';
                break;
        }

        return Html::tag(
            'span',
            '<i class="' . $icon . ' me-1"></i>' . $label,
            ['class' => $class]
        );
    }
}

