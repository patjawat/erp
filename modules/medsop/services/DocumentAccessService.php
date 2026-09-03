<?php

namespace app\modules\medsop\services;

use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\medsop\models\Document;
use app\modules\medsop\models\DocumentAudience;
use app\modules\medsop\models\OrganizationAccess;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * ศูนย์รวมการตรวจสิทธิ์ของคลังเอกสาร SOP/WI แบ่งเป็น 4 ระดับ
 *
 * 1. ผู้ดูแลระบบ (medsop.admin) จัดการได้ทุกหน่วยงาน เผยแพร่ และตั้งค่าระบบ
 * 2. ผู้จัดทำหน่วยงาน (medsop.author / role medsop) สร้างและแก้ไขเฉพาะหน่วยงานที่ตนสังกัด
 *    ส่งอนุมัติได้ แต่เผยแพร่เองไม่ได้ ต้องให้ผู้ดูแลระบบตรวจก่อน
 * 3. ผู้ดูทั้งองค์กร (medsop.viewAll / director / hr) เห็นทุกหน่วยงาน แต่แก้ไม่ได้
 * 4. ผู้ใช้ทั่วไป เห็นเอกสารที่เผยแพร่แล้วของหน่วยงานตน เอกสารที่ตนเป็นผู้รับ
 *    และเอกสารของหน่วยงานอื่นที่หัวหน้าหน่วยงานนั้นเปิดสิทธิ์ให้หน่วยงานตนเข้าดู
 */
class DocumentAccessService
{
    public const PERMISSION_ADMIN = 'medsop.admin';
    public const PERMISSION_AUTHOR = 'medsop.author';
    public const PERMISSION_REVIEW = 'medsop.review';
    public const PERMISSION_VIEW_ALL = 'medsop.viewAll';
    public const PERMISSION_VIEW_PUBLISHED = 'medsop.viewPublished';

    /**
     * RBAC item ของแต่ละระดับ รับทั้ง permission ชุด medsop.* และ role เดิมของระบบ
     * เพื่อให้มอบสิทธิ์จากหน้าจัดการผู้ใช้ (role) ได้โดยไม่ต้องผูก permission ทีละรายการ
     */
    private const ADMIN_ITEMS = [self::PERMISSION_ADMIN, 'medsopAdmin', 'admin'];
    private const AUTHOR_ITEMS = [self::PERMISSION_AUTHOR, 'medsop'];
    private const REVIEW_ITEMS = [self::PERMISSION_REVIEW, 'director', 'hr'];

    private $employee;
    private $audienceDocumentIds;
    private $authorOrganizationIds;
    private $grantedOrganizationIds;
    private $leaderOrganizationIds;
    private $itemCache = [];

    public function currentEmployee(): ?Employees
    {
        if ($this->employee === null && !Yii::$app->user->isGuest) {
            $this->employee = Employees::find()->where(['user_id' => Yii::$app->user->id])->one() ?: false;
        }
        return $this->employee ?: null;
    }

    /** หน่วยงานที่ผู้ใช้สังกัด (0 เมื่อยังไม่ผูกกับทะเบียนบุคลากร) */
    public function currentOrganizationId(): int
    {
        $employee = $this->currentEmployee();
        return $employee === null ? 0 : (int) $employee->department;
    }

    /** ตรวจ RBAC item ชุดหนึ่ง ผ่านอย่างน้อยหนึ่งรายการถือว่าผ่าน */
    private function canAny(array $items): bool
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        foreach ($items as $item) {
            if (!array_key_exists($item, $this->itemCache)) {
                $this->itemCache[$item] = Yii::$app->user->can($item);
            }
            if ($this->itemCache[$item]) {
                return true;
            }
        }
        return false;
    }

    public function isAdmin(): bool
    {
        return $this->canAny(self::ADMIN_ITEMS);
    }

    /** ได้รับสิทธิ์จัดทำเอกสาร แต่จำกัดเฉพาะหน่วยงานที่สังกัด */
    public function isAuthor(): bool
    {
        return $this->canAny(self::AUTHOR_ITEMS);
    }

    public function canReview(): bool
    {
        return $this->canAny(self::REVIEW_ITEMS);
    }

    public function canViewAll(): bool
    {
        return $this->isAdmin() || $this->canReview() || $this->canAny([self::PERMISSION_VIEW_ALL]);
    }

    /**
     * หน่วยงานที่ผู้ใช้จัดทำเอกสารได้ในฐานะผู้จัดทำหน่วยงาน
     * ผู้ดูแลระบบไม่ผ่านทางนี้ เพราะทำได้ทุกหน่วยงานอยู่แล้ว (ดู manageableOrganizationIds)
     */
    public function authorOrganizationIds(): array
    {
        if ($this->authorOrganizationIds !== null) {
            return $this->authorOrganizationIds;
        }
        $organizationId = $this->currentOrganizationId();
        return $this->authorOrganizationIds = ($this->isAuthor() && $organizationId > 0) ? [$organizationId] : [];
    }

    /**
     * หน่วยงานที่จัดทำเอกสารได้ทั้งหมด
     * คืน null เมื่อไม่จำกัด (ผู้ดูแลระบบ) เพื่อให้ผู้เรียกแยก "ทุกหน่วยงาน" ออกจาก "ไม่มีสิทธิ์เลย"
     */
    public function manageableOrganizationIds(): ?array
    {
        return $this->isAdmin() ? null : $this->authorOrganizationIds();
    }

    /** หน่วยงานอื่นที่เปิดสิทธิ์ให้หน่วยงานของผู้ใช้เข้าดูเอกสารที่เผยแพร่แล้ว */
    public function grantedOrganizationIds(): array
    {
        if ($this->grantedOrganizationIds !== null) {
            return $this->grantedOrganizationIds;
        }
        return $this->grantedOrganizationIds = OrganizationAccess::ownerIdsFor($this->currentOrganizationId());
    }

    /**
     * หน่วยงานที่ผู้ใช้เป็นหัวหน้า (tree.data_json.leader1)
     * ใช้ตัดสินว่าใครเปิดสิทธิ์ให้หน่วยงานอื่นเข้าดูเอกสารของหน่วยงานนี้ได้
     */
    public function leaderOrganizationIds(): array
    {
        if ($this->leaderOrganizationIds !== null) {
            return $this->leaderOrganizationIds;
        }
        $employee = $this->currentEmployee();
        if ($employee === null) {
            return $this->leaderOrganizationIds = [];
        }
        $employeeId = (string) $employee->id;
        $ids = Organization::find()
            ->select('id')
            ->where(['active' => 1])
            ->andWhere(['or',
                new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.leader1')) = :leaderEmp", [':leaderEmp' => $employeeId]),
                new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.leader_1')) = :leaderEmpAlt", [':leaderEmpAlt' => $employeeId]),
            ])
            ->column();
        return $this->leaderOrganizationIds = array_map('intval', $ids);
    }

    /**
     * หน่วยงานที่ผู้ใช้เปิดสิทธิ์เข้าถึงข้ามหน่วยงานได้
     * คืน null เมื่อไม่จำกัด (ผู้ดูแลระบบ)
     */
    public function grantableOrganizationIds(): ?array
    {
        return $this->isAdmin() ? null : $this->leaderOrganizationIds();
    }

    public function canGrantAccess(): bool
    {
        return $this->isAdmin() || $this->leaderOrganizationIds() !== [];
    }

    public function canGrantAccessFor(int $organizationId): bool
    {
        return $this->isAdmin() || in_array($organizationId, $this->leaderOrganizationIds(), true);
    }

    public function canManageSetting(): bool
    {
        return $this->isAdmin();
    }

    /**
     * มีบทบาทใดบทบาทหนึ่งในโมดูลนี้หรือไม่ ใช้ตัดสินการแสดงเมนูคลัง SOP/WI
     * เรียงจากการตรวจที่ถูกที่สุด (RBAC cache) ไปหาที่ต้องคิวรีฐานข้อมูล
     */
    public function canEnterModule(): bool
    {
        return $this->isAdmin() || $this->isAuthor() || $this->canViewAll() || $this->canGrantAccess();
    }

    public function canCreate(): bool
    {
        return $this->isAdmin() || $this->authorOrganizationIds() !== [];
    }

    /** จัดการเอกสารฉบับนี้ได้หรือไม่ โดยยังไม่คิดเรื่องสถานะ */
    public function ownsDocument(Document $document): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return in_array((int) $document->organization_id, $this->authorOrganizationIds(), true);
    }

    public function canUpdate(Document $document): bool
    {
        return $this->ownsDocument($document) && $document->isEditable();
    }

    /** ส่งอนุมัติ: ผู้จัดทำส่งฉบับร่างหรือฉบับที่ถูกส่งกลับ ให้ผู้ดูแลระบบตรวจ */
    public function canSubmit(Document $document): bool
    {
        return $this->ownsDocument($document)
            && in_array($document->status, [Document::STATUS_DRAFT, Document::STATUS_REJECTED], true);
    }

    /** เผยแพร่: เฉพาะผู้ดูแลระบบ เพื่อคุมคุณภาพเอกสารก่อนออกสู่หน่วยงาน */
    public function canPublish(Document $document): bool
    {
        return $this->isAdmin()
            && in_array($document->status, [Document::STATUS_DRAFT, Document::STATUS_PENDING, Document::STATUS_REJECTED], true);
    }

    /** ส่งกลับแก้ไข: ผู้ดูแลระบบตีกลับเอกสารที่รออนุมัติ */
    public function canReject(Document $document): bool
    {
        return $this->isAdmin() && $document->status === Document::STATUS_PENDING;
    }

    public function canDelete(Document $document): bool
    {
        return $this->ownsDocument($document) && $document->status === Document::STATUS_DRAFT;
    }

    public function canView(Document $document): bool
    {
        if ($this->canViewAll()) {
            return true;
        }
        $employee = $this->currentEmployee();
        if ($employee === null) {
            return false;
        }

        $organizationId = (int) $document->organization_id;
        // ผู้จัดทำเห็นเอกสารของหน่วยงานตนได้ทุกสถานะ รวมฉบับร่างที่ยังไม่เผยแพร่
        if (in_array($organizationId, $this->authorOrganizationIds(), true)) {
            return true;
        }
        if ($document->status !== Document::STATUS_PUBLISHED) {
            return false;
        }
        // หน่วยงานที่เจ้าของเอกสารเปิดสิทธิ์ให้เข้าดู
        if (in_array($organizationId, $this->grantedOrganizationIds(), true)) {
            return true;
        }

        $audiences = DocumentAudience::find()
            ->where(['document_id' => (int) $document->id])
            ->orderBy(['id' => SORT_ASC])
            ->all();
        if ($audiences !== []) {
            return isset((new AudienceResolverService())->resolve($audiences)[(int) $employee->id]);
        }

        return $organizationId === (int) $employee->department;
    }

    public function applyVisibleScope(ActiveQuery $query): void
    {
        if ($this->canViewAll()) {
            return;
        }
        $employee = $this->currentEmployee();
        if ($employee === null) {
            $query->andWhere(['d.status' => Document::STATUS_PUBLISHED, 'd.id' => -1]);
            return;
        }

        $department = (int) $employee->department;
        $conditions = ['or'];

        // เอกสารของหน่วยงานที่ตนจัดทำ เห็นได้ทุกสถานะ
        $authorOrganizationIds = $this->authorOrganizationIds();
        if ($authorOrganizationIds !== []) {
            $conditions[] = ['d.organization_id' => $authorOrganizationIds];
        }

        // เอกสารเผยแพร่แล้วของหน่วยงานอื่นที่เปิดสิทธิ์ให้หน่วยงานตนเข้าดู
        $grantedOrganizationIds = $this->grantedOrganizationIds();
        if ($grantedOrganizationIds !== []) {
            $conditions[] = ['and',
                ['d.status' => Document::STATUS_PUBLISHED],
                ['d.organization_id' => $grantedOrganizationIds],
            ];
        }

        // เอกสารเผยแพร่แล้วที่ตนเป็นผู้รับ
        $conditions[] = ['and',
            ['d.status' => Document::STATUS_PUBLISHED],
            ['d.id' => $this->audienceDocumentIds((int) $employee->id)],
        ];

        // เอกสารเผยแพร่แล้วของหน่วยงานตน เฉพาะฉบับที่ไม่ได้จำกัดรายชื่อผู้รับไว้
        if ($department > 0) {
            $conditions[] = ['and',
                ['d.status' => Document::STATUS_PUBLISHED],
                ['d.organization_id' => $department],
                new Expression('NOT EXISTS (SELECT 1 FROM {{%medsop_document_audience}} audience WHERE audience.document_id = d.id)'),
            ];
        }

        $query->andWhere($conditions);
    }

    private function audienceDocumentIds(int $employeeId): array
    {
        if ($this->audienceDocumentIds !== null) {
            return $this->audienceDocumentIds;
        }

        $grouped = [];
        foreach (DocumentAudience::find()->orderBy(['document_id' => SORT_ASC, 'id' => SORT_ASC])->all() as $audience) {
            $grouped[(int) $audience->document_id][] = $audience;
        }

        $resolver = new AudienceResolverService();
        $documentIds = [];
        foreach ($grouped as $documentId => $audiences) {
            if (isset($resolver->resolve($audiences)[$employeeId])) {
                $documentIds[] = (int) $documentId;
            }
        }

        return $this->audienceDocumentIds = $documentIds ?: [-1];
    }
}
