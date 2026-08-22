<?php

namespace app\modules\settings\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\db\Expression;
use app\models\Categorise;

/**
 * ทะเบียนหน่วยงานกลาง (org_unit) — เวอร์ชันตามปีงบประมาณ
 *
 * รวมหน่วยจัดทำแผน 2 แหล่ง:
 *  - source=structure : ดึงจากผังโครงสร้างบุคลากร (tree) — แก้ไม่ได้ อัปเดตชื่อ/หัวหน้าจากผัง
 *  - source=manual    : เพิ่มเอง (ทีมประสาน/สสจ./CUP ฯลฯ)
 *
 * @property int $id
 * @property int $thai_year
 * @property string $source
 * @property int|null $ref_id
 * @property string|null $unit_type
 * @property string|null $code
 * @property string $name
 * @property int|null $leader_emp_id
 * @property int $active
 * @property int $sort
 * @property array|string|null $data_json
 */
class OrgUnit extends ActiveRecord
{
    public const SOURCE_STRUCTURE = 'structure';
    public const SOURCE_MANUAL = 'manual';

    /** ประเภทเริ่มต้น (categorise code name=org_unit_type) */
    public const TYPE_ORG = 'OU_ORG';
    public const TYPE_TEAM = 'OU_TEAM';

    public static function tableName()
    {
        return 'org_unit';
    }

    public function rules()
    {
        return [
            [['thai_year', 'name'], 'required'],
            [['thai_year', 'ref_id', 'leader_emp_id', 'sort', 'created_by', 'updated_by'], 'integer'],
            [['active'], 'boolean'],
            [['source'], 'in', 'range' => [self::SOURCE_STRUCTURE, self::SOURCE_MANUAL]],
            [['source', 'unit_type'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 20],
            [['code'], 'match', 'pattern' => '/^[A-Z0-9_-]+$/', 'message' => 'ใช้อักษรอังกฤษตัวพิมพ์ใหญ่ ตัวเลข ขีดกลาง หรือขีดล่างเท่านั้น'],
            [['code'], 'validateUniqueCode'],
            [['data_json', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'thai_year' => 'ปีงบประมาณ',
            'source' => 'แหล่งที่มา',
            'unit_type' => 'ประเภท',
            'code' => 'อักษรย่อ',
            'name' => 'ชื่อหน่วยงาน',
            'leader_emp_id' => 'หัวหน้า/ผู้รับผิดชอบ',
            'active' => 'เปิดใช้',
        ];
    }

    /** อักษรย่อห้ามซ้ำในปีเดียวกัน */
    public function validateUniqueCode($attribute): void
    {
        if ($this->hasErrors($attribute) || $this->$attribute === null || $this->$attribute === '') {
            return;
        }
        $exists = static::find()
            ->where(['thai_year' => (int) $this->thai_year, 'code' => $this->$attribute])
            ->andWhere(['<>', 'id', (int) $this->id])
            ->exists();
        if ($exists) {
            $this->addError($attribute, sprintf('อักษรย่อ "%s" ถูกใช้แล้วในปี %d', $this->$attribute, $this->thai_year));
        }
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // คอลัมน์ชนิด json — Yii เข้ารหัสให้เองตอนบันทึก ถ้าเข้ารหัสซ้ำจะได้ค่าเป็น JSON string
        // ทำให้ JSON_EXTRACT ใน SQL หาไม่เจอ จึงต้องส่งเป็น array เสมอ
        if (is_string($this->data_json)) {
            $decoded = json_decode($this->data_json, true);
            $this->data_json = is_array($decoded) ? $decoded : null;
        }
        $now = date('Y-m-d H:i:s');
        $uid = (!Yii::$app->has('user') || Yii::$app->user->isGuest) ? null : Yii::$app->user->id;
        if ($insert) {
            $this->created_at = $now;
            $this->created_by = $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;
        return true;
    }

    public function afterFind()
    {
        parent::afterFind();
        if (is_string($this->data_json)) {
            $this->data_json = json_decode($this->data_json, true) ?: null;
        }
    }

    /**
     * ซิงก์หน่วยงานในโครงสร้าง (tree) เข้าทะเบียนของปีที่ระบุ
     * - เพิ่ม node ใหม่ที่ยังไม่มีในปีนั้น
     * - อัปเดตชื่อ/หัวหน้าจากผังเสมอ (ผัง = แหล่งข้อมูลจริง)
     * - carry-forward อักษรย่อ/ประเภท/active จากปีก่อนหน้า (ถ้ามี)
     *
     * @return array{added:int, updated:int}
     */
    public static function syncStructure(int $thaiYear): array
    {
        $added = 0;
        $updated = 0;

        // เรียงตามลำดับผังจริง: (root, lft) — tree มีได้หลาย root, lft ซ้ำข้าม root ได้
        $nodes = (new Query())
            ->from('tree')
            ->where(['active' => 1])
            ->andWhere(['>', 'lvl', 0])
            ->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])
            ->all();

        $seq = 0;
        foreach ($nodes as $node) {
            $seq += 10; // ลำดับต่อเนื่องทั้งผัง (เว้นช่องไว้แทรก)
            $refId = (int) $node['id'];
            $data = $node['data_json'];
            if (is_string($data)) {
                $data = json_decode($data, true) ?: [];
            }
            $leader = (int) ($data['leader_1'] ?? $data['leader1'] ?? $node['leader'] ?? 0);
            $leader = $leader > 0 ? $leader : null;

            $row = static::findOne([
                'thai_year' => $thaiYear,
                'source' => self::SOURCE_STRUCTURE,
                'ref_id' => $refId,
            ]);

            if ($row === null) {
                $row = new static([
                    'thai_year' => $thaiYear,
                    'source' => self::SOURCE_STRUCTURE,
                    'ref_id' => $refId,
                    'unit_type' => self::TYPE_ORG,
                    'active' => 1,
                ]);
                // carry-forward อักษรย่อ/ประเภท/สถานะ จากปีล่าสุดก่อนหน้า
                $prev = static::find()
                    ->where(['source' => self::SOURCE_STRUCTURE, 'ref_id' => $refId])
                    ->andWhere(['<', 'thai_year', $thaiYear])
                    ->orderBy(['thai_year' => SORT_DESC])
                    ->one();
                if ($prev) {
                    $row->code = $prev->code;
                    $row->unit_type = $prev->unit_type;
                    $row->active = $prev->active;
                }
                $added++;
            } else {
                $updated++;
            }

            // อัปเดตจากผังเสมอ (ชื่อ/หัวหน้า/ลำดับตามผัง)
            $row->name = (string) $node['name'];
            $row->leader_emp_id = $leader;
            $row->sort = $seq;
            $row->save(false);
        }

        return ['added' => $added, 'updated' => $updated];
    }

    /** คีย์เทียบซ้ำของหน่วยที่เพิ่มเอง — ผูก team_group เดิมถ้ามี ไม่มีก็เทียบด้วยชื่อ */
    private function manualKey(): string
    {
        $tgId = is_array($this->data_json) ? (int) ($this->data_json['team_group_id'] ?? 0) : 0;
        return $tgId > 0 ? 'tg:' . $tgId : 'name:' . trim((string) $this->name);
    }

    /**
     * คัดลอกทะเบียนจากปีหนึ่งไปอีกปี — ใช้กับปีที่ยังไม่ได้จัดชุด (มีแต่ผลของ syncStructure)
     *  - หน่วยในผัง : เติมอักษรย่อ/ประเภท ให้แถวปลายทางที่ยังว่าง (จับคู่ด้วย ref_id)
     *  - หน่วยเพิ่มเอง : เพิ่มทีมประสาน/หน่วยนอกผังที่ปลายทางยังไม่มี
     *
     * ไม่ลบแถว ไม่แก้ชื่อ/สถานะเปิดใช้ และไม่แตะ id เดิม เพราะแผนและโครงการอ้าง org_unit.id ของปีนั้นอยู่
     *
     * @return array{filled:int, added:int, skipped:string[]}
     */
    public static function copyFromYear(int $fromYear, int $toYear): array
    {
        $filled = 0;
        $added = 0;
        $skipped = [];
        if ($fromYear === $toYear) {
            return ['filled' => 0, 'added' => 0, 'skipped' => []];
        }

        // 1) หน่วยในผัง — เติมเฉพาะช่องที่ยังว่าง ของเดิมที่กรอกไว้แล้วไม่ทับ
        $targets = [];
        foreach (static::find()->where(['thai_year' => $toYear, 'source' => self::SOURCE_STRUCTURE])->all() as $t) {
            $targets[(int) $t->ref_id] = $t;
        }
        foreach (static::find()->where(['thai_year' => $fromYear, 'source' => self::SOURCE_STRUCTURE])->all() as $src) {
            $target = $targets[(int) $src->ref_id] ?? null;
            if ($target === null) {
                continue; // ผังปีปลายทางไม่มีหน่วยนี้ — ปล่อยไว้ ให้ผู้ดูแลตัดสินใจเอง
            }
            $dirty = false;
            if (($target->code === null || $target->code === '') && $src->code) {
                if (static::codeTaken($toYear, (string) $src->code, (int) $target->id)) {
                    $skipped[] = $target->name . ': อักษรย่อ ' . $src->code . ' ถูกใช้แล้วในปี ' . $toYear;
                } else {
                    $target->code = $src->code;
                    $dirty = true;
                }
            }
            if (($target->unit_type === null || $target->unit_type === '') && $src->unit_type) {
                $target->unit_type = $src->unit_type;
                $dirty = true;
            }
            if ($dirty) {
                $target->save(false);
                $filled++;
            }
        }

        // 2) หน่วยที่เพิ่มเอง (ทีมประสาน/นอกผัง) — เพิ่มเฉพาะที่ปลายทางยังไม่มี
        $existing = [];
        foreach (static::find()->where(['thai_year' => $toYear, 'source' => self::SOURCE_MANUAL])->all() as $t) {
            $existing[$t->manualKey()] = true;
        }
        foreach (static::find()->where(['thai_year' => $fromYear, 'source' => self::SOURCE_MANUAL])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all() as $src) {
            if (isset($existing[$src->manualKey()])) {
                continue;
            }
            $code = (string) $src->code;
            if ($code !== '' && static::codeTaken($toYear, $code, 0)) {
                $skipped[] = $src->name . ': อักษรย่อ ' . $code . ' ถูกใช้แล้วในปี ' . $toYear;
                $code = '';
            }
            $row = new static([
                'thai_year' => $toYear,
                'source' => self::SOURCE_MANUAL,
                'ref_id' => null,
                'unit_type' => $src->unit_type,
                'code' => $code !== '' ? $code : null,
                'name' => $src->name,
                'leader_emp_id' => $src->leader_emp_id,
                'active' => (int) $src->active,
                'sort' => (int) $src->sort,
                'data_json' => is_array($src->data_json) ? $src->data_json : null,
            ]);
            $row->save(false);
            $existing[$row->manualKey()] = true;
            $added++;
        }

        return ['filled' => $filled, 'added' => $added, 'skipped' => $skipped];
    }

    /**
     * นำเข้าข้อมูลเดิมจาก medsop (ครั้งแรก) — อักษรย่อหน่วยงาน + ทีมประสาน
     * เรียกหลัง syncStructure() ของปีเดียวกัน
     *
     * @return array{orgCodes:int, teams:int}
     */
    public static function importLegacyMedsop(int $thaiYear): array
    {
        $orgCodes = 0;
        $teams = 0;

        // 1) อักษรย่อหน่วยงาน: medsop_organization_setting -> structure rows ที่ยังไม่มี code
        if (static::tableExists('medsop_organization_setting')) {
            foreach ((new Query())->from('medsop_organization_setting')->all() as $s) {
                $row = static::findOne([
                    'thai_year' => $thaiYear,
                    'source' => self::SOURCE_STRUCTURE,
                    'ref_id' => (int) $s['organization_id'],
                ]);
                if ($row === null || ($row->code !== null && $row->code !== '')) {
                    continue;
                }
                $code = strtoupper(trim((string) ($s['code'] ?? '')));
                if ($code !== '' && !static::codeTaken($thaiYear, $code, (int) $row->id)) {
                    $row->code = $code;
                    $orgCodes++;
                }
                if (isset($s['active'])) {
                    $row->active = (int) $s['active'];
                }
                $row->save(false);
            }
        }

        // 2) ทีมประสาน: team_group -> manual rows (unit_type=OU_TEAM)
        if (static::tableExists('team_group')) {
            $teamSettings = static::tableExists('medsop_team_setting')
                ? (new Query())->from('medsop_team_setting')->indexBy('team_group_id')->all()
                : [];
            foreach ((new Query())->from('team_group')->where(['deleted_at' => null])->all() as $tg) {
                $tgId = (int) $tg['id'];
                $exists = static::find()
                    ->where(['thai_year' => $thaiYear, 'source' => self::SOURCE_MANUAL])
                    ->andWhere(new Expression("JSON_EXTRACT(data_json, '$.team_group_id') = :tg", [':tg' => $tgId]))
                    ->exists();
                if ($exists) {
                    continue;
                }
                $ts = $teamSettings[$tgId] ?? null;
                $code = $ts ? strtoupper(trim((string) ($ts['code'] ?? ''))) : '';
                if ($code !== '' && static::codeTaken($thaiYear, $code, 0)) {
                    $code = '';
                }
                $row = new static([
                    'thai_year' => $thaiYear,
                    'source' => self::SOURCE_MANUAL,
                    'ref_id' => null,
                    'unit_type' => self::TYPE_TEAM,
                    'code' => $code !== '' ? $code : null,
                    'name' => (string) $tg['title'],
                    'leader_emp_id' => $ts && !empty($ts['leader_employee_id']) ? (int) $ts['leader_employee_id'] : null,
                    'active' => $ts && isset($ts['active']) ? (int) $ts['active'] : 1,
                    'data_json' => ['team_group_id' => $tgId],
                ]);
                $row->save(false);
                $teams++;
            }
        }

        return ['orgCodes' => $orgCodes, 'teams' => $teams];
    }

    /**
     * mirror อักษรย่อ/สถานะ จากทะเบียน -> ตาราง medsop (เฉพาะปีที่เปิดใช้ปัจจุบัน)
     * ให้ระบบออกเลขเอกสาร medsop อ่านค่าเดิมของตัวเองต่อได้ โดยแก้จุดเดียวที่ทะเบียน
     */
    public static function syncToMedsop(int $thaiYear): void
    {
        if ($thaiYear !== (int) \app\modules\plan\components\PlanHelper::currentPlanYear()) {
            return; // sync เฉพาะปีปัจจุบัน — แก้ปีเก่าไม่กระทบ medsop
        }
        $now = date('Y-m-d H:i:s');
        $db = Yii::$app->db;
        $hasOrg = static::tableExists('medsop_organization_setting');
        $hasTeam = static::tableExists('medsop_team_setting');

        foreach (static::find()->where(['thai_year' => $thaiYear])->all() as $u) {
            if ($u->source === self::SOURCE_STRUCTURE && $u->ref_id && $hasOrg) {
                $db->createCommand()->upsert('medsop_organization_setting',
                    ['organization_id' => (int) $u->ref_id, 'code' => $u->code, 'active' => (int) $u->active, 'created_at' => $now, 'updated_at' => $now],
                    ['code' => $u->code, 'active' => (int) $u->active, 'updated_at' => $now]
                )->execute();
            } elseif ($u->source === self::SOURCE_MANUAL && $hasTeam) {
                $tgId = is_array($u->data_json) ? (int) ($u->data_json['team_group_id'] ?? 0) : 0;
                if ($tgId > 0) {
                    $db->createCommand()->upsert('medsop_team_setting',
                        ['team_group_id' => $tgId, 'code' => $u->code, 'active' => (int) $u->active, 'created_at' => $now, 'updated_at' => $now],
                        ['code' => $u->code, 'active' => (int) $u->active, 'updated_at' => $now]
                    )->execute();
                }
            }
        }
    }

    /**
     * ปีที่มีข้อมูลทะเบียนให้เลือกจริง
     * ถ้าปีที่ขอยังไม่ได้ตั้งค่าไว้ ให้ถอยไปใช้ปีที่ใกล้ที่สุดที่มีข้อมูล (เท่ากันเลือกปีที่ใหม่กว่า)
     * เพื่อไม่ให้ฟอร์มขึ้นตัวเลือกว่างเปล่าจนบันทึกไม่ได้
     */
    public static function yearWithData(int $thaiYear): int
    {
        if (static::find()->where(['thai_year' => $thaiYear, 'active' => 1])->exists()) {
            return $thaiYear;
        }
        $years = array_map('intval', static::find()->select('thai_year')->distinct()->where(['active' => 1])->column());
        if (!$years) {
            return $thaiYear;
        }
        usort($years, fn ($a, $b) => (abs($a - $thaiYear) <=> abs($b - $thaiYear)) ?: ($b <=> $a));
        return $years[0];
    }

    /**
     * ข้อมูลสำหรับ Select2 ของแผน — จัดกลุ่มตามประเภท + เยื้องตามลำดับชั้นผัง (เหมือนหน้าตั้งค่า)
     * คืน: [ชื่อประเภท => [org_unit_id => ชื่อ(เยื้อง)]]
     *
     * @param int      $thaiYear ปีงบของเอกสาร
     * @param int|null $keepId   ค่าที่เอกสารเลือกไว้อยู่ ให้คงอยู่ในรายการเสมอแม้จะถูกปิดใช้หรือเป็นของปีอื่น
     *                           มิฉะนั้นเปิดฟอร์มแก้ข้อมูลเก่าแล้วค่าเดิมจะหายไปเงียบ ๆ
     */
    public static function groupedForSelect(int $thaiYear, ?int $keepId = null): array
    {
        $year = static::yearWithData($thaiYear);

        $rows = (new Query())
            ->select(['o.id', 'o.name', 'o.source', 'type_title' => 'c.title', 'lvl' => 't.lvl'])
            ->from(['o' => 'org_unit'])
            ->leftJoin(['c' => 'categorise'], "c.name='org_unit_type' AND c.code=o.unit_type")
            ->leftJoin(['t' => 'tree'], 't.id = o.ref_id')
            ->where(['o.thai_year' => $year, 'o.active' => 1])
            ->orderBy(['o.source' => SORT_DESC, 'o.sort' => SORT_ASC, 'o.name' => SORT_ASC])
            ->all();

        $groups = [];
        $seen = [];
        foreach ($rows as $r) {
            $indent = '';
            if ($r['source'] === self::SOURCE_STRUCTURE && (int) $r['lvl'] > 1) {
                $indent = str_repeat("\u{00A0}\u{00A0}\u{00A0}", (int) $r['lvl'] - 1);
            }
            $groups[$r['type_title'] ?: 'อื่น ๆ'][$r['id']] = $indent . $r['name'];
            $seen[(int) $r['id']] = true;
        }

        // ค่าที่เลือกไว้เดิมต้องเลือกค้างได้เสมอ พร้อมบอกเหตุผลว่าทำไมไม่อยู่ในรายการปกติ
        if ($keepId && empty($seen[$keepId]) && ($kept = static::findOne($keepId))) {
            $notes = [];
            if (!$kept->active) {
                $notes[] = 'ปิดใช้แล้ว';
            }
            if ((int) $kept->thai_year !== $year) {
                $notes[] = 'ปี ' . (int) $kept->thai_year;
            }
            $label = $kept->name . ($notes ? ' (' . implode(' · ', $notes) . ')' : '');
            $typeTitle = (string) Categorise::find()->select('title')
                ->where(['name' => 'org_unit_type', 'code' => $kept->unit_type])->scalar();
            $groups[$typeTitle ?: 'อื่น ๆ'][$kept->id] = $label;
        }

        return $groups;
    }

    /** map org_unit_id => ref_id (tree.id) ของปี — ใช้ให้ pull การเบิกแปลงกลับเป็น tree.id */
    public static function refMap(int $thaiYear): array
    {
        $map = [];
        foreach (static::find()->select(['id', 'ref_id'])->where(['thai_year' => $thaiYear, 'active' => 1])->asArray()->all() as $r) {
            $map[$r['id']] = $r['ref_id'] !== null ? (int) $r['ref_id'] : null;
        }
        return $map;
    }

    private static function codeTaken(int $thaiYear, string $code, int $exceptId): bool
    {
        return static::find()
            ->where(['thai_year' => $thaiYear, 'code' => $code])
            ->andWhere(['<>', 'id', $exceptId])
            ->exists();
    }

    private static function tableExists(string $table): bool
    {
        return in_array($table, Yii::$app->db->schema->getTableNames(), true);
    }
}
