<?php

namespace app\modules\telegrambot\services;

use app\models\Categorise;
use app\modules\usermanager\models\User;
use Yii;

/**
 * เชื่อมบัญชี Telegram เข้ากับบัญชีผู้ใช้ ERP
 *
 * วิธีเดิมผูกได้ทางเดียวคือเปิด Mini App จากในแอป Telegram
 * ซึ่งคนส่วนใหญ่ไม่รู้ว่าต้องทำอย่างไร ผูกได้จริงแค่ 26 จาก 332 บัญชี
 *
 * วิธีนี้ใช้ลิงก์เชิญที่มีโทเคนติดไปด้วย ผู้ใช้กดจากมือถือหรือสแกน QR จากจอคอมได้
 * โทเคนเก็บใน cache อายุสั้น จึงไม่ต้องเพิ่มตารางใหม่และไม่ต้องรัน migration
 */
class TelegramLinkService
{
    /** อายุโทเคน — สั้นพอที่หลุดไปแล้วใช้ไม่ได้ แต่นานพอให้เปิดแอปทัน */
    public const TOKEN_TTL = 600;

    private const CACHE_PREFIX = 'tg_link_';

    /** ออกโทเคนใหม่สำหรับผู้ใช้ที่กำลังล็อกอิน */
    public static function issueToken(int $userId): ?string
    {
        if ($userId <= 0) {
            return null;
        }

        try {
            $token = Yii::$app->security->generateRandomString(24);
            Yii::$app->cache->set(self::CACHE_PREFIX . $token, $userId, self::TOKEN_TTL);
            return $token;
        } catch (\Throwable $e) {
            Yii::warning('ออกโทเคนเชื่อม Telegram ไม่สำเร็จ: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /** ใช้โทเคนแล้วทิ้ง คืน user id ถ้าโทเคนยังไม่หมดอายุ */
    public static function consumeToken(string $token): ?int
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        try {
            $key = self::CACHE_PREFIX . $token;
            $userId = Yii::$app->cache->get($key);
            Yii::$app->cache->delete($key);
            return $userId ? (int) $userId : null;
        } catch (\Throwable $e) {
            Yii::warning('อ่านโทเคนเชื่อม Telegram ไม่สำเร็จ: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * ผูก chat id เข้ากับบัญชีผู้ใช้
     *
     * @return array{status: string, message: string}
     */
    public static function bind(int $userId, string $chatId): array
    {
        $chatId = trim($chatId);
        if ($userId <= 0 || $chatId === '') {
            return ['status' => 'error', 'message' => 'ข้อมูลไม่ครบ เชื่อมต่อไม่สำเร็จ'];
        }

        $user = User::findOne($userId);
        if (!$user) {
            return ['status' => 'error', 'message' => 'ไม่พบบัญชีผู้ใช้'];
        }

        // กัน Telegram บัญชีเดียวไปผูกกับผู้ใช้หลายคน ไม่งั้นแจ้งเตือนจะส่งผิดคน
        $owner = User::findOne(['telegram_id' => $chatId]);
        if ($owner && (int) $owner->id !== $userId) {
            return [
                'status' => 'error',
                'message' => 'บัญชี Telegram นี้ถูกผูกกับผู้ใช้อื่นแล้ว กรุณายกเลิกการเชื่อมต่อจากบัญชีนั้นก่อน',
            ];
        }

        $user->telegram_id = $chatId;
        if (!$user->save(false, ['telegram_id'])) {
            Yii::warning('บันทึก telegram_id ไม่สำเร็จ user ' . $userId, __METHOD__);
            return ['status' => 'error', 'message' => 'บันทึกไม่สำเร็จ กรุณาลองใหม่'];
        }

        return ['status' => 'success', 'message' => 'เชื่อมต่อสำเร็จ'];
    }

    public static function unbind(int $userId): bool
    {
        $user = User::findOne($userId);
        if (!$user) {
            return false;
        }
        $user->telegram_id = null;
        return (bool) $user->save(false, ['telegram_id']);
    }

    /** ลิงก์เชิญที่พาไปเปิดแชทกับบอทพร้อมส่งโทเคนให้อัตโนมัติ */
    public static function deepLink(string $token): ?string
    {
        $username = self::botUsername();
        if (!$username || $token === '') {
            return null;
        }
        return 'https://t.me/' . $username . '?start=' . $token;
    }

    public static function botUsername(): ?string
    {
        $settings = self::settings();
        $username = trim((string) ($settings['bot_username'] ?? ''));
        return $username !== '' ? ltrim($username, '@') : null;
    }

    /** อ่านค่าตั้งค่าบอทจากทะเบียนกลาง (categorise) */
    public static function settings(): array
    {
        try {
            $row = Categorise::findOne(['name' => 'telegram_setting']);
            if (!$row) {
                return [];
            }
            $data = $row->data_json;
            if (is_string($data) && $data !== '') {
                $data = json_decode($data, true);
            }
            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** ระบบแจ้งเตือนถูกเปิดใช้งานอยู่หรือไม่ */
    public static function isEnabled(): bool
    {
        $settings = self::settings();
        return (string) ($settings['enable_notification'] ?? '0') === '1';
    }
}
