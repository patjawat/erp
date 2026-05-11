<?php

namespace app\modules\dms\models;

use Yii;
use Exception;
use yii\helpers\Url;
use yii\helpers\Json;
use Imagine\Image\Box;
use yii\db\Expression;
use yii\imagine\Image;
use yii\base\Component;
use yii\bootstrap5\Html;
use yii\web\UploadedFile;
use app\models\Categorise;
use kartik\file\FileInput;
use yii\httpclient\Client;
use app\components\LineMsg;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use yii\helpers\BaseFileHelper;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\dms\models\Documents;
use app\modules\hr\models\Organization;

use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;


/**
 * This is the model class for table "documents_detail".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $name ชื่อการ tags เอกสาร เช่น  employee,department
 * @property string|null $document_id เอกสาร
 * @property string|null $to_id ถึงบุคลากรหรือหน่วยงาน ระบุเป็นเลข id ของบุคลากรหรือหน่วยงาน
 * @property string|null $to_name ถึงบุคลากรหรือหน่วยงาน ระบุเป็นชื่อของบุคลากรหรือหน่วยงาน
 * @property string|null $to_type ถึงบุคลากรหรือหน่วยงาน ระบุเป็นประเภทของบุคลากรหรือหน่วยงาน
 * @property string|null $from_id จากบุคลากรหรือหน่วยงาน ระบุเป็นเลข id ของบุคลากรหรือหน่วยงาน
 * @property string|null $from_name จากบุคลากรหรือหน่วยงาน ระบุเป็นชื่อของบุคลากรหรือหน่วยงาน
 * @property string|null $from_type จากบุคลากรหรือหน่วยงาน ระบุเป็นประเภทของบุคลากรหรือหน่วยงาน
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class DocumentsDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $q;
    public $thai_year;
    public $date_start;
    public $date_end;
    public $show_reading;
    public $comment;
    public $status;
    public $date_filter;
    public $tags_employee;
    public $tags_department;
    public $tag_id;
    public static function tableName()
    {
        return 'documents_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'deleted_at', 'thai_year', 'q', 'show_reading', 'tags_employee', 'tags_department', 'tag_id', 'data_json', 'status','date_filter','date_start','date_start'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by', 'doc_read'], 'integer'],
            [['ref', 'name', 'document_id', 'to_id', 'to_name', 'to_type', 'from_id', 'from_name', 'from_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'Ref',
            'name' => 'ชื่อการ tags เอกสาร เช่น  employee,department',
            'document_id' => 'เอกสาร',
            'to_id' => 'ถึงบุคลากรหรือหน่วยงาน ระบุเป็นเลข id ของบุคลากรหรือหน่วยงาน',
            'to_name' => 'ถึงบุคลากรหรือหน่วยงาน ระบุเป็นชื่อของบุคลากรหรือหน่วยงาน',
            'to_type' => 'ถึงบุคลากรหรือหน่วยงาน ระบุเป็นประเภทของบุคลากรหรือหน่วยงาน',
            'from_id' => 'จากบุคลากรหรือหน่วยงาน ระบุเป็นเลข id ของบุคลากรหรือหน่วยงาน',
            'from_name' => 'จากบุคลากรหรือหน่วยงาน ระบุเป็นชื่อของบุคลากรหรือหน่วยงาน',
            'from_type' => 'จากบุคลากรหรือหน่วยงาน ระบุเป็นประเภทของบุคลากรหรือหน่วยงาน',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => ['updated_at'],
                'value' => new Expression('NOW()'),
            ],
        ];
    }
    public function getDocument()
    {
        return $this->hasOne(Documents::class, ['id' => 'document_id']);
    }

    public function getDocumentStatus()
    {
        $document = $this->document;
        return $document ? $document->documentStatus : null;
    }

    // บุคลากร
    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'to_id']);
    }
    //หน่วยงาน
    public function getDepartment()
    {
        return $this->hasOne(Organization::class, ['id' => 'to_id']);
    }

    public function afterFind()
    {
        try {
            $this->data_json = $this->normalizeJsonData($this->data_json);
            $this->comment = isset($this->data_json['comment']) ? $this->data_json['comment'] : '';
        } catch (\Throwable $th) {
        }

        parent::afterFind();
    }

    public function beforeSave($insert)
    {
        if (is_array($this->data_json)) {
            $this->data_json = json_encode($this->data_json, JSON_UNESCAPED_UNICODE);
        }

        return parent::beforeSave($insert);
    }


    public function ListThaiYear()
    {
        $model = Documents::find()
            ->select('thai_year')
            ->groupBy('thai_year')
            ->orderBy(['thai_year' => SORT_DESC])
            ->asArray()
            ->all();

        $year = AppHelper::YearBudget();
        $isYear = [['thai_year' => $year]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
        // รวมข้อมูล
        $model = ArrayHelper::merge($isYear, $model);
        return ArrayHelper::map($model, 'thai_year', 'thai_year');
    }

    // แสดงรายการสถานะ
    public function ListStatus()
    {
        $model = Categorise::find()
            ->where(['name' => 'document_status'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }


    /**
     * รายการ id ของหน่วยงานที่เอกสารนี้ส่งไปแล้ว (รวม forwarding card + comment_dept)
     * ใช้กรอง dept dropdown ใน composer
     */
    public function listForwardedDepartmentIds()
    {
        try {
            return self::find()
                ->select('to_id')
                ->where(['document_id' => $this->document_id])
                ->andWhere(['in', 'name', ['department', 'comment_dept']])
                ->andWhere(['not', ['to_id' => null]])
                ->andWhere(['<>', 'to_id', ''])
                ->distinct()
                ->column();
        } catch (\Throwable $th) {
            return [];
        }
    }

    /**
     * รายการหน่วยงานที่ "ยังไม่ได้" ส่ง — ใช้ใน Select2 ของ composer
     */
    public function listAvailableDepartments()
    {
        $forwarded = $this->listForwardedDepartmentIds();
        // ถ้ากำลังแก้ comment เดิม ให้แสดง dept ที่เคยเลือกของ comment นี้ด้วย
        $myCurrent = is_array($this->tags_department) ? $this->tags_department : [];
        $exclude = array_diff($forwarded, $myCurrent);
        $query = Organization::find()->where(['active' => 1])->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC]);
        if (!empty($exclude)) {
            $query->andWhere(['not in', 'id', $exclude]);
        }
        return ArrayHelper::map($query->all(), 'id', 'name');
    }

    // ดึงค่าไปแสดงตอนที่เรา update
    public function listEmployeeSelectTag()
    {
        try {
            // หาบุคคลที่เคย tag ไปแล้ว
            $tags = self::find()->select('to_id')

                ->where(['name' => 'employee', 'document_id' => $this->document_id])
                ->andWhere(['<>', 'to_id', $this->created_by])
                ->All();
            $allTag = ArrayHelper::getColumn($tags, 'to_id');
            // หาบุคคลที่ยังไม่ได้ tag ไป  และ ไม่ใช่ admin
            $employees = Employees::find()
                ->select(['id', 'concat(fname, " ", lname) as fullname'])
                ->andWhere(['status' => '1'])
                // ->andWhere(['<>', 'id', '1'])
                ->andWhere(['not in', 'id', $allTag])
                ->asArray()
                ->all();

            return ArrayHelper::map($employees, 'id', function ($model) {
                return $model['fullname'];
            });
        } catch (\Throwable $th) {
            return [];
        }
    }

    //ส่งline
    public function sendMessage()
    {
        $models = self::find()->where(['name' => 'comment', 'document_id' => $this->document_id])->all();
        foreach ($models as $model) {
            try {
                $line_id = $model->employee->user->line_id;
                $topic = $this->comment;
                // ส่ง msg ให้ Approve
                LineMsg::sendDocument($model, $line_id);
            } catch (\Throwable $th) {
            }
        }
    }

    protected function normalizeJsonData($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    protected function commentDataJson(): array
    {
        return $this->normalizeJsonData($this->data_json);
    }

    protected function departmentLeaderIds(Organization $department): array
    {
        $dataJson = $this->normalizeJsonData($department->data_json);
        $leaderIds = [];

        foreach (['leader1', 'leader_1', 'leader2', 'leader_2'] as $key) {
            $value = $dataJson[$key] ?? null;
            if (is_numeric($value) && (int) $value > 0) {
                $leaderIds[] = (int) $value;
            }
        }

        return array_values(array_unique($leaderIds));
    }

    /**
     * รายชื่อพนักงานที่ควรได้รับแจ้งเมื่อมีการ comment ในเอกสารนี้
     */
    public function commentNotificationRecipients(): array
    {
        $detailRows = self::find()
            ->select(['name', 'to_id'])
            ->where(['document_id' => $this->document_id])
            ->andWhere(['in', 'name', ['department', 'employee', 'employee_tag', 'tags', 'req_approve']])
            ->all();

        if (!empty($this->id)) {
            $commentRows = self::find()
                ->select(['name', 'to_id'])
                ->where(['document_id' => $this->document_id, 'from_id' => $this->id])
                ->andWhere(['in', 'name', ['comment_emp', 'comment_dept']])
                ->all();
            $detailRows = array_merge($detailRows, $commentRows);
        }

        $legacyTagIds = DocumentTags::find()
            ->select('tag_id')
            ->where(['document_id' => $this->document_id, 'name' => 'employee'])
            ->column();
        if (!empty($legacyTagIds)) {
            foreach ($legacyTagIds as $legacyTagId) {
                $detailRows[] = (object) [
                    'name' => 'employee',
                    'to_id' => $legacyTagId,
                ];
            }
        }

        $employeeIds = [];
        $departmentIds = [];

        foreach ($detailRows as $row) {
            $toId = (int) $row->to_id;
            if ($toId <= 0) {
                continue;
            }

            if (in_array($row->name, ['department', 'comment_dept'], true)) {
                $departmentIds[] = $toId;
            } else {
                $employeeIds[] = $toId;
            }
        }

        if (!empty($departmentIds)) {
            $departments = Organization::find()
                ->where(['id' => array_values(array_unique($departmentIds))])
                ->all();

            foreach ($departments as $department) {
                $employeeIds = array_merge($employeeIds, $this->departmentLeaderIds($department));
            }
        }

        $employeeIds = array_values(array_unique(array_filter(array_map('intval', $employeeIds))));
        if (empty($employeeIds)) {
            return [];
        }

        return Employees::find()
            ->where(['id' => $employeeIds])
            ->with('user')
            ->all();
    }

    public function notifyCommentRecipients(): void
    {
        $document = $this->document;
        if (!$document) {
            return;
        }

        $recipients = $this->commentNotificationRecipients();
        if (empty($recipients)) {
            Yii::warning('No comment notification recipients found for document #' . (int) $this->document_id, __METHOD__);
            return;
        }

        $commenter = Employees::find()->where(['user_id' => $this->created_by])->with('user')->one();
        $commenterName = $commenter ? $commenter->fullname : ('ผู้ใช้ #' . $this->created_by);
        $commentData = $this->commentDataJson();
        $commentText = trim((string) ($commentData['comment'] ?? ''));
        $commentText = $commentText !== '' ? $commentText : '-';
        $documentUrl = Url::to(['/dms/documents/view', 'id' => $document->id], true);

        $lineMessage = $this->buildCommentLineMessage($document, $commenterName, $commentText, $documentUrl);
        $telegramMessage = $this->buildCommentTelegramMessage($document, $commenterName, $commentText, $documentUrl);

        foreach ($recipients as $employee) {
            if (!$employee || !$employee->user) {
                continue;
            }
            if ((int) $employee->user_id === (int) $this->created_by) {
                continue;
            }

            $lineId = trim((string) ($employee->user->line_id ?? ''));
            if ($lineId !== '') {
                try {
                    $lineResult = LineMsg::PushMsg([
                        'to' => $lineId,
                        'messages' => [
                            [
                                'type' => 'text',
                                'text' => $lineMessage,
                            ],
                        ],
                    ]);
                    if (!$lineResult) {
                        Yii::warning('LINE notification returned empty result for employee #' . $employee->id, __METHOD__);
                    }
                } catch (\Throwable $th) {
                    Yii::warning('Failed to send comment LINE notification: ' . $th->getMessage(), __METHOD__);
                }
            }

            $telegramId = trim((string) ($employee->user->telegram_id ?? ''));
            if ($telegramId !== '') {
                try {
                    $telegramResult = Yii::$app->telegram->sendDirectMessage($telegramId, $telegramMessage);
                    if ($telegramResult === false) {
                        Yii::warning('Telegram notification returned false for employee #' . $employee->id, __METHOD__);
                    }
                } catch (\Throwable $th) {
                    Yii::warning('Failed to send comment Telegram notification: ' . $th->getMessage(), __METHOD__);
                }
            }
        }
    }

    protected function buildCommentLineMessage(Documents $document, string $commenterName, string $commentText, string $documentUrl, string $heading = 'มีความเห็นใหม่ในเอกสาร'): string
    {
        $lines = [
            $heading,
        ];

        if (!empty($document->doc_regis_number)) {
            $lines[] = 'เลขรับ: ' . $document->doc_regis_number;
        }
        if (!empty($document->doc_number)) {
            $lines[] = 'เลขที่: ' . $document->doc_number;
        }

        $lines[] = 'เรื่อง: ' . $document->topic;
        $lines[] = 'โดย: ' . $commenterName;
        $lines[] = 'ความเห็น: ' . $commentText;
        $lines[] = 'เปิดดูเอกสาร: ' . $documentUrl;

        return implode("\n", $lines);
    }

    protected function buildCommentTelegramMessage(Documents $document, string $commenterName, string $commentText, string $documentUrl, string $heading = 'มีความเห็นใหม่ในเอกสาร'): string
    {
        $lines = [
            '<b>' . Html::encode($heading) . '</b>',
        ];

        if (!empty($document->doc_regis_number)) {
            $lines[] = '<b>เลขรับ:</b> ' . Html::encode((string) $document->doc_regis_number);
        }
        if (!empty($document->doc_number)) {
            $lines[] = '<b>เลขที่:</b> ' . Html::encode((string) $document->doc_number);
        }

        $lines[] = '<b>เรื่อง:</b> ' . Html::encode((string) $document->topic);
        $lines[] = '<b>โดย:</b> ' . Html::encode($commenterName);
        $lines[] = '<b>ความเห็น:</b> ' . Html::encode($commentText);
        $lines[] = '<a href="' . Html::encode($documentUrl) . '">เปิดดูเอกสาร</a>';

        return implode("\n", $lines);
    }

    protected function normalizeIdList($value): array
    {
        if (is_array($value)) {
            $items = $value;
        } elseif (is_string($value) && trim($value) !== '') {
            $items = preg_split('/[\s,]+/', trim($value));
        } elseif (is_numeric($value)) {
            $items = [$value];
        } else {
            $items = [];
        }

        $items = array_map('intval', $items);
        $items = array_filter($items, static function ($id) {
            return $id > 0;
        });

        return array_values(array_unique($items));
    }

    protected function loadEmployeesByIds(array $employeeIds): array
    {
        $employeeIds = $this->normalizeIdList($employeeIds);
        if (empty($employeeIds)) {
            return [];
        }

        return Employees::find()
            ->where(['id' => $employeeIds])
            ->with('user')
            ->all();
    }

    protected function resolveNotificationRecipientsFromSelection(array $employeeIds = [], array $departmentIds = [], bool $fallbackToSelf = false): array
    {
        $recipientsById = [];

        foreach ($this->loadEmployeesByIds($employeeIds) as $employee) {
            $recipientsById[(int) $employee->id] = $employee;
        }

        $departmentIds = $this->normalizeIdList($departmentIds);
        if (!empty($departmentIds)) {
            $departments = Organization::find()
                ->where(['id' => $departmentIds])
                ->all();

            $leaderIds = [];
            foreach ($departments as $department) {
                $leaderIds = array_merge($leaderIds, $this->departmentLeaderIds($department));
            }

            foreach ($this->loadEmployeesByIds($leaderIds) as $employee) {
                $recipientsById[(int) $employee->id] = $employee;
            }
        }

        if (empty($recipientsById) && $fallbackToSelf) {
            $me = UserHelper::GetEmployee();
            if ($me && (int) $me->id > 0) {
                $self = Employees::find()
                    ->where(['id' => (int) $me->id])
                    ->with('user')
                    ->one();
                if ($self) {
                    $recipientsById[(int) $self->id] = $self;
                }
            }
        }

        return array_values($recipientsById);
    }

    protected function collectTestNotificationRecipients(): array
    {
        $recipientsById = [];

        foreach ($this->commentNotificationRecipients() as $employee) {
            if ($employee && (int) $employee->id > 0) {
                $recipientsById[(int) $employee->id] = $employee;
            }
        }

        $selectedEmployees = $this->normalizeIdList($this->tags_employee);
        $selectedDepartments = $this->normalizeIdList($this->tags_department);
        foreach ($this->resolveNotificationRecipientsFromSelection($selectedEmployees, $selectedDepartments, false) as $employee) {
            if ($employee && (int) $employee->id > 0) {
                $recipientsById[(int) $employee->id] = $employee;
            }
        }

        $currentUserId = (int) Yii::$app->user->id;
        if ($currentUserId > 0 && count($recipientsById) > 1) {
            unset($recipientsById[$currentUserId]);
        }

        if (empty($recipientsById)) {
            $fallbackRecipients = $this->resolveNotificationRecipientsFromSelection([], [], true);
            foreach ($fallbackRecipients as $employee) {
                if ($employee && (int) $employee->id > 0) {
                    $recipientsById[(int) $employee->id] = $employee;
                }
            }
        }

        return array_values($recipientsById);
    }

    public function sendTestNotification(?string $commentText = null): array
    {
        $document = $this->document;
        if (!$document) {
            return [
                'status' => 'error',
                'message' => 'ไม่พบข้อมูลเอกสาร',
            ];
        }

        $recipients = $this->collectTestNotificationRecipients();
        if (empty($recipients)) {
            Yii::warning('No test notification recipients found for document #' . (int) $this->document_id, __METHOD__);
            return [
                'status' => 'error',
                'message' => 'ไม่พบผู้รับสำหรับทดสอบการแจ้งเตือน',
            ];
        }

        $commenter = UserHelper::GetEmployee();
        $commenterName = $commenter ? $commenter->fullname : ('ผู้ใช้ #' . Yii::$app->user->id);
        $commentText = trim((string) ($commentText ?? ''));
        if ($commentText === '') {
            $commentData = $this->commentDataJson();
            $commentText = trim((string) ($commentData['comment'] ?? ''));
        }
        if ($commentText === '') {
            $commentText = 'ทดสอบการส่งการแจ้งเตือน';
        }
        $documentUrl = Url::to(['/dms/documents/view', 'id' => $document->id], true);

        $lineMessage = $this->buildCommentLineMessage($document, $commenterName, $commentText, $documentUrl, 'ทดสอบการส่งการแจ้งเตือน');
        $telegramMessage = $this->buildCommentTelegramMessage($document, $commenterName, $commentText, $documentUrl, 'ทดสอบการส่งการแจ้งเตือน');

        $lineSent = 0;
        $telegramSent = 0;
        $noChannelCount = 0;
        $failedCount = 0;
        $missingChannelRecipients = [];

        foreach ($recipients as $employee) {
            if (!$employee || !$employee->user) {
                $failedCount++;
                $missingChannelRecipients[] = [
                    'emp_id' => (int) ($employee->id ?? 0),
                    'user_id' => (int) ($employee->user_id ?? 0),
                    'name' => trim((string) ($employee->fullname ?? (($employee->prefix ?? '') . ($employee->fname ?? '') . ' ' . ($employee->lname ?? '')))),
                ];
                continue;
            }

            $sentAny = false;

            $lineId = trim((string) ($employee->user->line_id ?? ''));
            if ($lineId !== '') {
                $sentAny = true;
                try {
                    $lineResult = LineMsg::PushMsg([
                        'to' => $lineId,
                        'messages' => [
                            [
                                'type' => 'text',
                                'text' => $lineMessage,
                            ],
                        ],
                    ]);
                    if ($lineResult) {
                        $lineSent++;
                    } else {
                        $failedCount++;
                    }
                } catch (\Throwable $th) {
                    $failedCount++;
                    Yii::warning('Failed to send test LINE notification: ' . $th->getMessage(), __METHOD__);
                }
            }

            $telegramId = trim((string) ($employee->user->telegram_id ?? ''));
            if ($telegramId !== '') {
                $sentAny = true;
                try {
                    $telegramResult = Yii::$app->telegram->sendDirectMessage($telegramId, $telegramMessage);
                    if ($telegramResult !== false) {
                        $telegramSent++;
                    } else {
                        $failedCount++;
                    }
                } catch (\Throwable $th) {
                    $failedCount++;
                    Yii::warning('Failed to send test Telegram notification: ' . $th->getMessage(), __METHOD__);
                }
            }

            if (!$sentAny) {
                $noChannelCount++;
                $missingChannelRecipients[] = [
                    'emp_id' => (int) ($employee->id ?? 0),
                    'user_id' => (int) ($employee->user_id ?? 0),
                    'name' => trim((string) ($employee->fullname ?? (($employee->prefix ?? '') . ($employee->fname ?? '') . ' ' . ($employee->lname ?? '')))),
                ];
            }
        }

        if ($lineSent === 0 && $telegramSent === 0) {
            $detailLines = [];
            foreach ($missingChannelRecipients as $recipient) {
                $detailLines[] = sprintf(
                    'emp_id: %d, user_id: %d, name: %s',
                    (int) ($recipient['emp_id'] ?? 0),
                    (int) ($recipient['user_id'] ?? 0),
                    trim((string) ($recipient['name'] ?? '-')) ?: '-'
                );
            }

            $detailsHtml = '';
            if (!empty($detailLines)) {
                $detailsHtml = '<div class="text-start mt-2">'
                    . '<div class="fw-semibold mb-1">รายละเอียดผู้รับ:</div>'
                    . '<div style="white-space:pre-line;">'
                    . Html::encode(implode("\n", $detailLines))
                    . '</div></div>';
            }

            return [
                'status' => 'warning',
                'message' => 'พบผู้รับแล้ว แต่ไม่พบ line_id หรือ telegram_id สำหรับส่งการแจ้งเตือน',
                'details_html' => $detailsHtml,
                'details' => $detailLines,
                'recipients' => count($recipients),
                'line_sent' => $lineSent,
                'telegram_sent' => $telegramSent,
                'no_channel_count' => $noChannelCount,
                'failed_count' => $failedCount,
            ];
        }

        $messageParts = ['ส่งการแจ้งเตือนทดสอบแล้ว'];
        if ($lineSent > 0) {
            $messageParts[] = 'LINE ' . $lineSent;
        }
        if ($telegramSent > 0) {
            $messageParts[] = 'Telegram ' . $telegramSent;
        }
        if ($noChannelCount > 0) {
            $messageParts[] = 'ไม่มีช่องทาง ' . $noChannelCount;
        }
        if ($failedCount > 0) {
            $messageParts[] = 'ล้มเหลว ' . $failedCount;
        }

        return [
            'status' => 'success',
            'message' => implode(' · ', $messageParts),
            'recipients' => count($recipients),
            'line_sent' => $lineSent,
            'telegram_sent' => $telegramSent,
            'no_channel_count' => $noChannelCount,
            'failed_count' => $failedCount,
        ];
    }


    // บันทึก tag ไปยัง document
    public function UpdateDocumentsDetail()
    {
        try {
            $userId = (int) Yii::$app->user->id;
            $empIds = is_array($this->tags_employee) ? array_values(array_filter(array_map('intval', $this->tags_employee))) : [];
            $deptIds = is_array($this->tags_department) ? array_values(array_filter(array_map('intval', $this->tags_department))) : [];

            $deleteEmpCondition = [
                'from_id' => $this->id,
                'name' => 'comment_emp',
                'created_by' => $userId,
            ];
            if (!empty($empIds)) {
                $deleteEmpCondition[] = ['not in', 'to_id', $empIds];
            }
            self::deleteAll($deleteEmpCondition);

            $deleteDeptCondition = [
                'from_id' => $this->id,
                'name' => 'comment_dept',
                'created_by' => $userId,
            ];
            if (!empty($deptIds)) {
                $deleteDeptCondition[] = ['not in', 'to_id', $deptIds];
            }
            self::deleteAll($deleteDeptCondition);

            foreach ($empIds as $value) {
                $check = DocumentsDetail::find()
                    ->where(['name' => 'comment_emp', 'document_id' => $this->document_id, 'from_id' => $this->id, 'to_id' => (string) $value])
                    ->one();
                $model = $check ? $check : new DocumentsDetail();
                $model->name = 'comment_emp';
                $model->document_id = $this->document_id;
                $model->from_id = (string) $this->id;
                $model->from_type = 'comment';
                $model->to_id = (string) $value;
                $model->to_type = 'employee';
                $model->save(false);
            }

            foreach ($deptIds as $value) {
                $check = DocumentsDetail::find()
                    ->where(['name' => 'comment_dept', 'document_id' => $this->document_id, 'from_id' => $this->id, 'to_id' => (string) $value])
                    ->one();
                $model = $check ? $check : new DocumentsDetail();
                $model->name = 'comment_dept';
                $model->document_id = $this->document_id;
                $model->from_id = (string) $this->id;
                $model->from_type = 'comment';
                $model->to_id = (string) $value;
                $model->to_type = 'department';
                $model->save(false);
            }
        } catch (\Throwable $th) {
        }
        // return $dicrector;
    }

    public function updateTags()
    {
        try {
            $dicrector = 0;
            $clearDEmployeeTag = DocumentsDetail::deleteAll([
                'and',
                ['not in', 'to_id', $this->tags_employee],
                ['document_id' => $this->id, 'name' => 'comment']
            ]);

            foreach ($this->tags_employee as $key => $value):
                $check = DocumentsDetail::find()->where(['name' => 'comment', 'document_id' => $this->id, 'to_id' => $value])->one();
                $new = $check ? $check : new DocumentsDetail();
                $new->name = 'employee';
                $new->document_id = $this->id;
                $new->to_id = $value;
                $new->save(false);
            endforeach;
        } catch (\Throwable $th) {
        }
    }

    public function getAvatar($empid, $msg = '')
    {
        try {
            $employee = Employees::find()->where(['user_id' => $this->created_by])->one();
            $createdAt = Yii::$app->thaiFormatter->asDate($this->created_at, 'medium');
            $commentData = $this->commentDataJson();
            $msg = '<i class="fa-solid fa-quote-left"></i><span="fst-italic mb-0 ps-4">' . ($commentData['comment'] ?? '') . '</span><i class="fa-solid fa-quote-right"></i>';
            // $msg = $employee->departmentName();
            return [
                'avatar' => $employee->getAvatar(false, $msg),
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
                'position_name' => $employee->positionName(),
                // 'product_type_name' => $this->data_json['product_type_name']
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'department' => '',
                'fullname' => '',
                'position_name' => '',
                'product_type_name' => ''
            ];
        }
    }



    // แสดงการcommentและส่งต่อรายบุคคล
    public function StackSendTags()
    {
        try {
            $querys = $this->tags_employee;
            $count = count($querys) - 2;
            $data = '';
            $data .= '<div class="avatar-stack">';
            $count > 0 ? $data .= Html::a('+' . $count, ['/dms/documents/list-comment', 'id' => $this->id, 'title' => '<i class="fa-regular fa-comments fs-2"></i> การลงความเห็น'], ['class' => 'open-modal avatar-sm rounded-circle shadow bg-secondary text-white text-center p-2 fs-13', 'data' => [
                'size' => 'modal-md',
            ]]) : '';
            foreach ($querys as $key => $emp_id) {
                $emp = Employees::findOne(['id' => $emp_id]);
                if ($key <= 1) {
                    // $data .= Html::a(
                    $data .= Html::img('@web/img/placeholder-img.jpg', [
                        'class' => 'avatar-sm rounded-circle shadow lazyload blur-up',
                        'data' => [
                            'expand' => '-20',
                            'sizes' => 'auto',
                            'src' => $emp->showAvatar()
                        ]
                    ]);
                    // ['/dms/documents/list-comment', 'id' => $emp_id, 'title' => '<i class="fa-regular fa-comments fs-2"></i> การลงความเห็น'],
                    // [
                    //     'class' => 'open-modal',
                    //     'data' => [
                    //         'size' => 'modal-md',
                    //         'bs-trigger' => 'hover focus',
                    //         'bs-toggle' => 'popover',
                    //         'bs-placement' => 'top',
                    //         'bs-title' => '<i class="fa-regular fa-comment"></i> ความคิดเห็น',
                    //         'bs-html' => 'true',
                    //         'bs-content' => $emp->fullname
                    //     ]
                    // ]
                    // );
                } else {
                }
            }

            $data .= '</div>';
            return $data;
        } catch (\Throwable $th) {
            // return 'เกิดข้อผิดพลาด';
        }
    }

    /**
     * วันเวลาอ่านของพนักงานต่อแถว routing (department/tags) ตรงกับ actionView: name=read, from_id=id แถวถึงหน่วยงาน/tags, to_id=พนักงาน
     *
     * @param int[] $routingDetailIds
     * @return array<int,string> routing detail id => doc_read (datetime)
     */
    public static function readRecordTimesByRoutingFromIds(array $routingDetailIds, int $employeeId): array
    {
        $routingDetailIds = array_values(array_unique(array_filter(array_map('intval', $routingDetailIds))));
        if ($routingDetailIds === []) {
            return [];
        }

        $toOr = [
            'or',
            ['to_id' => (string) $employeeId],
            ['to_id' => $employeeId],
        ];

        $rows = static::find()
            ->select(['from_id', 'doc_read'])
            ->where(['name' => 'read'])
            ->andWhere($toOr)
            ->andWhere(['from_id' => $routingDetailIds])
            ->andWhere(['not', ['doc_read' => null]])
            ->orderBy(['doc_read' => SORT_DESC])
            ->asArray()
            ->all();

        $map = [];
        foreach ($rows as $row) {
            $fromId = (int) $row['from_id'];
            if (isset($map[$fromId])) {
                continue;
            }
            $dr = $row['doc_read'] ?? null;
            if ($dr === null || $dr === '' || trim((string) $dr) === '') {
                continue;
            }
            if (strpos((string) $dr, '0000-00-00') === 0) {
                continue;
            }
            $map[$fromId] = is_string($dr) ? $dr : (string) $dr;
        }

        return $map;
    }

    //ปักหมุดเอกสาร
    public function docRead()
    {

        $emp = UserHelper::GetEmployee();
        $model = self::find()->where(['name' => 'read', 'document_id' => $this->document_id, 'to_id' => $emp->id])->one();
        if ($model && $model->bookmark == 'Y') {
            return [
                'status' => $model->bookmark,
                'view' => $model->bookmark == 'Y' ? '<i class="fa-solid fa-bookmark text-warning"></i>' : '<i class="fa-regular fa-star"></i>',
                'read_date' => $model->doc_read ? (ThaiDateHelper::formatThaiDate($model->doc_read) . ' ' . (explode(' ',$model->doc_read)[1]) ?? '') : ''
            ];
        }else{
            return [
                'status' => 'N',
                'view' => '<i class="fa-regular fa-bookmark"></i>',
                'read_date' => ''
            ];
        }
    }
}
