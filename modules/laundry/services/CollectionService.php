<?php

namespace app\modules\laundry\services;

use app\modules\hr\models\Organization;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Connection;
use yii\db\Query;

/** All writes go through this service; confirmed rounds cannot be silently edited. */
class CollectionService
{
    private $db;

    public function __construct(?Connection $db = null)
    {
        $this->db = $db ?: Yii::$app->db;
    }

    public static function netKg($gross, $tare): string
    {
        foreach ([$gross, $tare] as $value) {
            if (!is_scalar($value) || !preg_match('/^\d{1,9}(?:\.\d{1,3})?$/', (string) $value)) {
                throw new InvalidArgumentException('น้ำหนักต้องเป็นตัวเลขไม่เกิน 3 ตำแหน่งทศนิยม');
            }
        }
        if ((float) $gross > 999999999.999 || (float) $tare > 999999999.999 || (float) $gross <= (float) $tare) {
            throw new InvalidArgumentException('น้ำหนักรวมต้องมากกว่าน้ำหนักภาชนะ');
        }
        // Round at the same 0.001 kg precision as the database columns.
        $net = round((float) $gross - (float) $tare, 3);
        if ($net <= 0) {
            throw new InvalidArgumentException('น้ำหนักสุทธิต้องมากกว่า 0');
        }
        return number_format($net, 3, '.', '');
    }

    public function createRound(string $date, ?int $collectorId, ?string $note, ?int $userId): int
    {
        $this->assertDate($date);
        $now = date('Y-m-d H:i:s');
        // UUID-backed document number prevents collisions during concurrent creation.
        $number = 'LC-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $this->db->createCommand()->insert('laundry_collection_round', [
            'round_no' => $number, 'collection_date' => $date, 'departed_at' => $now,
            'collector_id' => $collectorId, 'status' => 'OPEN', 'note' => $note,
            'created_at' => $now, 'created_by' => $userId,
        ])->execute();
        return (int) $this->db->getLastInsertID();
    }

    public function addStop(int $roundId, int $departmentId, int $soiledBags, int $infectiousBags, string $collectedAt): int
    {
        if ($soiledBags < 0 || $infectiousBags < 0) {
            throw new InvalidArgumentException('จำนวนถุงต้องไม่ติดลบ');
        }
        if (!Organization::find()->where(['id' => $departmentId])->exists()) {
            throw new InvalidArgumentException('ไม่พบหน่วยงาน');
        }
        $time = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $collectedAt);
        if (!$time || $time->format('Y-m-d\TH:i') !== $collectedAt) {
            throw new InvalidArgumentException('วันเวลาที่เก็บไม่ถูกต้อง');
        }
        $tx = $this->db->beginTransaction();
        try {
            $round = $this->lockedRound($roundId);
            $this->assertEditable($round);
            $this->db->createCommand()->insert('laundry_collection_stop', [
                'round_id' => $roundId, 'department_id' => $departmentId,
                'collected_at' => $time->format('Y-m-d H:i:s'),
                'soiled_bag_count' => $soiledBags, 'infectious_bag_count' => $infectiousBags,
            ])->execute();
            $stopId = (int) $this->db->getLastInsertID();
            $tx->commit();
            return $stopId;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function weigh(int $roundId, int $stopId, string $class, $gross, $tare, ?string $scaleRef, ?int $userId): void
    {
        if (!in_array($class, ['SOILED', 'INFECTIOUS'], true)) {
            throw new InvalidArgumentException('ประเภทผ้าไม่ถูกต้อง');
        }
        $net = self::netKg($gross, $tare);
        $tx = $this->db->beginTransaction();
        try {
            $round = $this->lockedRound($roundId);
            $this->assertEditable($round);
            $stop = (new Query())->from('laundry_collection_stop')->where(['id' => $stopId, 'round_id' => $roundId])->one($this->db);
            if (!$stop) {
                throw new InvalidArgumentException('ไม่พบรายการเก็บผ้าประเภทนี้ของหน่วยงานในรอบ');
            }
            $values = [
                'gross_kg' => $gross, 'tare_kg' => $tare, 'net_kg' => $net,
                'weighed_at' => date('Y-m-d H:i:s'), 'weighed_by' => $userId,
                'scale_ref' => $scaleRef,
            ];
            $existing = (new Query())->from('laundry_collection_weight')->where(['stop_id' => $stopId, 'linen_class' => $class])->one($this->db);
            if ($existing) {
                $this->db->createCommand()->update('laundry_collection_weight', $values, ['id' => $existing['id']])->execute();
            } else {
                $this->db->createCommand()->insert('laundry_collection_weight', $values + ['stop_id' => $stopId, 'linen_class' => $class])->execute();
            }
            if (!$round['returned_at']) {
                $this->db->createCommand()->update('laundry_collection_round', ['returned_at' => date('Y-m-d H:i:s')], ['id' => $roundId])->execute();
            }
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function confirm(int $roundId, ?int $userId): void
    {
        $tx = $this->db->beginTransaction();
        try {
            $round = $this->lockedRound($roundId);
            $this->assertEditable($round);
            $stops = (new Query())->from('laundry_collection_stop')->where(['round_id' => $roundId])->all($this->db);
            if (!$stops) {
                throw new InvalidArgumentException('ยังไม่มีหน่วยงานในรอบเก็บ');
            }
            $weights = (new Query())->select(['w.stop_id', 'w.linen_class'])
                ->from(['w' => 'laundry_collection_weight'])
                ->innerJoin(['s' => 'laundry_collection_stop'], 's.id = w.stop_id')
                ->where(['s.round_id' => $roundId])->all($this->db);
            $seen = [];
            foreach ($weights as $weight) {
                $seen[$weight['stop_id'] . ':' . $weight['linen_class']] = true;
            }
            foreach ($stops as $stop) {
                if (!isset($seen[$stop['id'] . ':SOILED']) && !isset($seen[$stop['id'] . ':INFECTIOUS'])) {
                    throw new InvalidArgumentException('ยังไม่ได้ชั่งผ้าของบางหน่วยงาน');
                }
                foreach (['SOILED' => 'soiled_bag_count', 'INFECTIOUS' => 'infectious_bag_count'] as $class => $field) {
                    if ((int) $stop[$field] > 0 && !isset($seen[$stop['id'] . ':' . $class])) {
                        throw new InvalidArgumentException('ยังชั่งผ้าไม่ครบทุกหน่วยงานและประเภท');
                    }
                }
            }
            $now = date('Y-m-d H:i:s');
            $this->db->createCommand()->update('laundry_collection_round', [
                'status' => 'CONFIRMED', 'returned_at' => $round['returned_at'] ?: $now,
                'confirmed_at' => $now, 'confirmed_by' => $userId,
            ], ['id' => $roundId, 'status' => 'OPEN'])->execute();
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    private function lockedRound(int $id): array
    {
        $round = $this->db->createCommand('SELECT * FROM {{%laundry_collection_round}} WHERE id = :id FOR UPDATE', [':id' => $id])->queryOne();
        if (!$round) {
            throw new InvalidArgumentException('ไม่พบรอบเก็บผ้า');
        }
        return $round;
    }

    private function assertEditable(array $round): void
    {
        if ($round['status'] !== 'OPEN') {
            throw new InvalidArgumentException('รอบเก็บนี้ยืนยันแล้ว ไม่สามารถแก้ไขโดยตรง');
        }
    }

    private function assertDate(string $date): void
    {
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException('วันที่เก็บไม่ถูกต้อง');
        }
    }
}
