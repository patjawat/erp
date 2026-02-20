<?php

namespace app\components;

use yii\helpers\Html;

/**
 * Status Badge Helper – มาตรฐาน Soft Color + Subtle Border, รองรับ Dark/Light
 * ใช้ร่วมกับ Bootstrap 5, Bootstrap Icons, rounded-pill เท่านั้น, bg-opacity-10
 */
class StatusBadgeHelper
{
    /** ค่าคงที่สถานะ (ใช้เป็น key ใน config) */
    const PENDING = 'PENDING';
    const APPROVED = 'APPROVED';
    const REJECTED = 'REJECTED';
    const DRAFT = 'DRAFT';
    /** สำหรับโมดูลคลัง/รับเข้า */
    const CONFIRMED = 'CONFIRMED';
    const CANCELLED = 'CANCELLED';

    /**
     * Config แต่ละ status: theme (Bootstrap), icon, label ภาษาไทย
     * เพิ่ม status ใหม่ที่นี้เท่านั้น
     */
    private static function config(): array
    {
        return [
            self::PENDING => [
                'theme' => 'warning',
                'icon' => 'bi-hourglass-split',
                'label' => 'รอดำเนินการ',
            ],
            self::APPROVED => [
                'theme' => 'success',
                'icon' => 'bi-check-circle',
                'label' => 'อนุมัติแล้ว',
            ],
            self::REJECTED => [
                'theme' => 'danger',
                'icon' => 'bi-x-circle',
                'label' => 'ไม่อนุมัติ',
            ],
            self::DRAFT => [
                'theme' => 'secondary',
                'icon' => 'bi-file-earmark',
                'label' => 'ร่าง',
            ],
            self::CONFIRMED => [
                'theme' => 'success',
                'icon' => 'bi-check-circle',
                'label' => 'บันทึกแล้ว',
            ],
            self::CANCELLED => [
                'theme' => 'danger',
                'icon' => 'bi-x-circle',
                'label' => 'ยกเลิก',
            ],
        ];
    }

    /**
     * ดึง config ของ status (รองรับทั้ง UPPER และค่าจาก DB)
     */
    public static function getStatusConfig(?string $status): ?array
    {
        if ($status === null || $status === '') {
            return null;
        }
        $key = strtoupper(trim($status));
        $config = self::config();
        return $config[$key] ?? null;
    }

    /**
     * สร้าง HTML สำหรับ status badge ตามมาตรฐานระบบ
     *
     * @param string|null $status ค่า status เช่น PENDING, APPROVED, DRAFT
     * @param array $options [
     *   'label' => string|null,   // override ข้อความ (null = ใช้จาก config)
     *   'showIcon' => bool,      // default true
     *   'tooltip' => string|null, // title สำหรับ tooltip (null = ใช้ label)
     *   'tag' => string,         // default 'span'
     *   'class' => string,       // class เพิ่มเติม
     * ]
     * @return string HTML
     */
    public static function renderStatusBadge(?string $status, array $options = []): string
    {
        $cfg = self::getStatusConfig($status);
        $label = $options['label'] ?? $cfg['label'] ?? $status;
        $showIcon = $options['showIcon'] ?? true;
        $tooltip = $options['tooltip'] ?? $label;
        $tag = $options['tag'] ?? 'span';
        $extraClass = $options['class'] ?? '';

        if ($cfg === null) {
            $theme = 'secondary';
            $icon = 'bi-question-circle';
        } else {
            $theme = $cfg['theme'];
            $icon = $cfg['icon'];
        }

        $class = implode(' ', array_filter([
            'badge',
            "bg-{$theme}",
            'bg-opacity-10',
            "text-{$theme}",
            "border",
            "border-{$theme}-subtle",
            'rounded-pill',
            'fw-medium',
            'px-2',
            'py-1',
            $extraClass,
        ]));

        $content = '';
        if ($showIcon) {
            $content .= '<i class="bi ' . $icon . ' me-1"></i>';
        }
        $content .= Html::encode($label);

        $attrs = [
            'class' => $class,
        ];
        if ($tooltip !== null && $tooltip !== '') {
            $attrs['title'] = $tooltip;
            $attrs['data-bs-toggle'] = 'tooltip';
            $attrs['data-bs-placement'] = 'top';
        }

        return Html::tag($tag, $content, $attrs);
    }

    /**
     * คืน label ภาษาไทยของ status (ใช้ใน dropdown / export)
     */
    public static function getLabel(?string $status): string
    {
        $cfg = self::getStatusConfig($status);
        return $cfg['label'] ?? (string) $status;
    }

    /**
     * คืน theme (success/warning/danger/secondary) สำหรับใช้ใน logic
     */
    public static function getTheme(?string $status): string
    {
        $cfg = self::getStatusConfig($status);
        return $cfg['theme'] ?? 'secondary';
    }

    /**
     * Badge สถานะแบบ loading (skeleton) สำหรับ AJAX / กำลังโหลด
     */
    public static function renderStatusBadgeLoading(array $options = []): string
    {
        $tag = $options['tag'] ?? 'span';
        $class = 'badge rounded-pill fw-medium px-2 py-1 bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 ' . ($options['class'] ?? '');
        $content = '<span class="placeholder col-4"></span>';
        return Html::tag($tag, $content, [
            'class' => $class,
            'aria-hidden' => 'true',
        ]);
    }
}
