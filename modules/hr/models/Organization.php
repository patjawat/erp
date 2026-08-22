<?php

namespace app\modules\hr\models;

use Yii;
use creocoder\nestedsets\NestedSetsBehavior;
use yii\helpers\Json;

/**
 * This is the model class for table "organization_diagram".
 *
 * @property int $id
 * @property int|null $root
 * @property int $lft
 * @property int $rgt
 * @property int $lvl
 * @property string $name
 * @property string|null $icon
 * @property int $icon_type
 * @property int $active
 * @property int $selected
 * @property int $disabled
 * @property int $readonly
 * @property int $visible
 * @property int $collapsed
 * @property int $movable_u
 * @property int $movable_d
 * @property int $movable_l
 * @property int $movable_r
 * @property int $removable
 * @property int $removable_all
 * @property int $child_allowed
 */
class Organization extends \yii\db\ActiveRecord
{


    use \kartik\tree\models\TreeTrait {
        isDisabled as parentIsDisabled; // note the alias
    }

    /**
     * @var string the classname for the TreeQuery that implements the NestedSetQueryBehavior.
     * If not set this will default to `kartik	ree\models\TreeQuery`.
     */
    public static $treeQueryClass; // change if you need to set your own TreeQuery

    /**
     * @var bool whether to HTML encode the tree node names. Defaults to `true`.
     */
    public $encodeNodeNames = true;

    /**
     * @var bool whether to HTML purify the tree node icon content before saving.
     * Defaults to `true`.
     */
    public $purifyNodeIcons = true;

    /**
     * @var array activation errors for the node
     */
    public $nodeActivationErrors = [];

    /**
     * @var array node removal errors
     */
    public $nodeRemovalErrors = [];

    /**
     * @var bool attribute to cache the `active` state before a model update. Defaults to `true`.
     */
    public $activeOrig = true;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tree';
    }


    public function behaviors()
    {
        return [
            'tree' => [
                'class' => NestedSetsBehavior::class,
                'treeAttribute' => 'root',   // ใช้ root column
                'leftAttribute' => 'lft',
                'rightAttribute' => 'rgt',
                'depthAttribute' => 'lvl',   // ใช้ lvl เป็น depth
            ],
        ];
    }

    public function getParent()
    {
        return $this->parents(1)->one(); // ดึง parent ตัวเดียว
    }

    /**
     * ชื่อหน่วยงานตามลำดับชั้นในผังองค์กร (ราก → โหนดนี้)
     * @return string[]
     */
    public function pathNames(): array
    {
        $names = [];
        try {
            foreach ($this->parents()->all() as $ancestor) {
                $names[] = $ancestor->name;
            }
        } catch (\Throwable $e) {
            // ไม่มี behavior/ข้อมูลผิดปกติ — คืนเฉพาะชื่อตัวเอง
        }
        $names[] = $this->name;
        return $names;
    }

    /**
     * ชื่อเต็มตามผังองค์กร เช่น "กลุ่มอำนวยการ › กลุ่มงานบริหารทั่วไป › งานพัสดุ"
     */
    public function pathLabel(string $sep = ' › '): string
    {
        return implode($sep, $this->pathNames());
    }

    /**
     * ข้อมูลสำหรับ Select2 แบบจัดกลุ่ม (optgroup ตามหน่วยงานบนสุด/ราก)
     * คืน [ 'ชื่อราก' => [ id => 'กลุ่มงาน › งาน', ... ], ... ] — query เดียว ไม่ N+1
     *
     * @param bool $activeOnly เอาเฉพาะ active=1
     * @param string $sep
     * @return array
     */
    public static function groupedByRoot(bool $activeOnly = true, string $sep = ' › '): array
    {
        $query = static::find()->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC]);
        if ($activeOnly) {
            $query->andWhere(['active' => 1]);
        }
        $all = $query->all();

        $grouped = [];
        foreach ($all as $node) {
            if ((int) $node->lvl === 0) {
                continue; // รากเป็นหัวข้อกลุ่ม ไม่ให้เลือกเอง
            }
            // หาบรรพบุรุษภายในรากเดียวกัน (in-memory, n เล็ก)
            $chain = [];
            foreach ($all as $a) {
                if ($a->root === $node->root && $a->lft < $node->lft && $a->rgt > $node->rgt) {
                    $chain[(int) $a->lft] = $a->name;
                }
            }
            ksort($chain);
            $rootName = reset($chain) ?: $node->name; // บรรพบุรุษตัวแรก = ราก
            array_shift($chain); // ตัดรากออก (เป็นชื่อกลุ่มแล้ว)
            $chain[] = $node->name;
            $grouped[$rootName][$node->id] = implode($sep, $chain);
        }

        return $grouped;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['root', 'lft', 'rgt', 'lvl', 'icon_type', 'active', 'selected', 'disabled', 'readonly', 'visible', 'collapsed', 'movable_u', 'movable_d', 'movable_l', 'movable_r', 'removable', 'removable_all', 'child_allowed'], 'integer'],
            [['name'], 'required'],
            [['leader', 'tb_name', 'code', 'data_json'], 'safe'],
            [['name'], 'string', 'max' => 60],
            [['leader'], 'string', 'max' => 60],
            [['icon'], 'string', 'max' => 255],
        ];
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->normalizeDataJson();
    }

    public function beforeValidate()
    {
        $this->normalizeDataJson();
        return parent::beforeValidate();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->normalizeDataJson();
        if (is_array($this->data_json) && !$this->isNativeJsonColumn()) {
            $this->data_json = Json::encode($this->data_json);
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->normalizeDataJson();
    }

    /**
     * คืนค่า data_json เป็น array เสมอ รองรับทั้งคอลัมน์ JSON และ LONGTEXT
     */
    public function getDataJsonArray(): array
    {
        $this->normalizeDataJson();
        return $this->data_json;
    }

    private function normalizeDataJson(): void
    {
        if (is_array($this->data_json)) {
            return;
        }

        if (!is_string($this->data_json) || trim($this->data_json) === '') {
            $this->data_json = [];
            return;
        }

        try {
            $decoded = Json::decode($this->data_json, true);
            $this->data_json = is_array($decoded) ? $decoded : [];
        } catch (\InvalidArgumentException $e) {
            $this->data_json = [];
        }
    }

    private function isNativeJsonColumn(): bool
    {
        $column = static::getTableSchema()->getColumn('data_json');

        return $column !== null
            && ($column->type === 'json' || stripos((string) $column->dbType, 'json') !== false);
    }

//     public function rules()
// {
//     return [
//         [['root', 'lft', 'rgt', 'lvl', 'icon_type', 'active', 'selected', 'disabled', 'readonly', 'visible', 'collapsed', 'movable_u', 'movable_d', 'movable_l', 'movable_r', 'removable', 'removable_all'], 'safe'],
//         [['name'], 'required'],
//         [['name'], 'string', 'max' => 60],
//         [['icon'], 'string', 'max' => 255],
//     ];
// }

    // public function rules()
    // {
    //     $rules = parent::rules();
    //     $rules[] = ['description', 'safe'];
    //     return $rules;
    // }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'root' => 'Root',
            'lft' => 'Lft',
            'rgt' => 'Rgt',
            'lvl' => 'Lvl',
            'name' => 'Name',
            'icon' => 'Icon',
            'icon_type' => 'Icon Type',
            'active' => 'Active',
            'selected' => 'Selected',
            'disabled' => 'Disabled',
            'readonly' => 'Readonly',
            'visible' => 'Visible',
            'collapsed' => 'Collapsed',
            'movable_u' => 'Movable U',
            'movable_d' => 'Movable D',
            'movable_l' => 'Movable L',
            'movable_r' => 'Movable R',
            'removable' => 'Removable',
            'removable_all' => 'Removable All',
            'child_allowed' => 'Child Allowed',
        ];
    }

    public function getLeader()
    {
        $json = $this->getDataJsonArray();
        return Employees::findOne($json['leader_1'] ?? $json['leader1'] ?? null);
    }

    public function getTreeLabel()
    {
        $leader = $this->leader;

        if ($leader) {
            $avatar = $leader->ShowAvatar();
            return "<img src='{$avatar}' width='24' height='24' class='rounded-circle me-1'> {$this->name}";
        }

        return $this->name;
    }
}
