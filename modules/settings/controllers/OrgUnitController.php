<?php

namespace app\modules\settings\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Query;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use app\models\Categorise;
use app\modules\settings\models\OrgUnit;
use app\modules\plan\components\PlanHelper;

/**
 * ทะเบียนหน่วยงานกลาง — กำหนดอักษรย่อ/ประเภท/หัวหน้า/เปิด-ปิดใช้
 * ดึงหน่วยจากผังโครงสร้าง (tree) + เพิ่มหน่วยภายในเอง (ทีมประสาน/สสจ./CUP)
 */
class OrgUnitController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'save' => ['post'],
                    'add' => ['post'],
                    'sync' => ['post'],
                    'delete' => ['post'],
                    'type-add' => ['post'],
                    'type-save' => ['post'],
                    'type-delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $year = (int) ($this->request->get('thai_year') ?: PlanHelper::currentPlanYear());
        $type = (string) $this->request->get('unit_type', '');
        $source = (string) $this->request->get('source', '');
        $q = trim((string) $this->request->get('q', ''));

        $query = OrgUnit::find()->where(['thai_year' => $year]);
        if ($type !== '') {
            $query->andWhere(['unit_type' => $type]);
        }
        if ($source !== '') {
            $query->andWhere(['source' => $source]);
        }
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'name', $q], ['like', 'code', $q]]);
        }
        // structure ก่อน (source DESC: structure > manual) แล้วเรียงตามลำดับผัง
        $rows = $query->orderBy(['source' => SORT_DESC, 'sort' => SORT_ASC, 'name' => SORT_ASC])->all();

        return $this->render('index', [
            'year' => $year,
            'type' => $type,
            'source' => $source,
            'q' => $q,
            'rows' => $rows,
            'years' => $this->years($year),
            'types' => $this->typeMap(),
            'employees' => $this->employeeMap(),
            'typeCounts' => $this->typeCounts($year),
            'srcCounts' => $this->srcCounts($year),
            'levels' => $this->levelMap(),
            'manageTypes' => Categorise::find()->where(['name' => 'org_unit_type'])->orderBy(['sort' => SORT_ASC])->all(),
        ]);
    }

    /** เพิ่มประเภทหน่วยงาน (categorise org_unit_type) — code สร้างอัตโนมัติ, ผู้ใช้กรอกแค่ชื่อ */
    public function actionTypeAdd()
    {
        $year = (int) ($this->request->post('thai_year') ?: PlanHelper::currentPlanYear());
        $title = trim((string) $this->request->post('title', ''));
        if ($title !== '') {
            $max = (int) (new Query())->from('categorise')
                ->where(['name' => 'org_unit_type'])->max('CAST(sort AS UNSIGNED)');
            Yii::$app->db->createCommand()->insert('categorise', [
                'name' => 'org_unit_type',
                'code' => $this->genTypeCode(),
                'title' => $title,
                'sort' => (string) ($max + 1),
                'active' => 1,
            ])->execute();
            Yii::$app->session->setFlash('success', 'เพิ่มประเภท "' . $title . '" แล้ว');
        }
        return $this->redirect(['index', 'thai_year' => $year]);
    }

    /** บันทึกชื่อ/สถานะ/ลำดับ ประเภท (bulk) */
    public function actionTypeSave()
    {
        $year = (int) ($this->request->post('thai_year') ?: PlanHelper::currentPlanYear());
        foreach ((array) $this->request->post('types', []) as $id => $d) {
            $c = Categorise::findOne(['id' => (int) $id, 'name' => 'org_unit_type']);
            if ($c === null) {
                continue;
            }
            $title = trim((string) ($d['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $c->title = $title;
            $c->active = !empty($d['active']) ? 1 : 0;
            if (isset($d['sort'])) {
                $c->sort = (string) (int) $d['sort'];
            }
            $c->save(false);
        }
        Yii::$app->session->setFlash('success', 'บันทึกประเภทหน่วยงานเรียบร้อย');
        return $this->redirect(['index', 'thai_year' => $year]);
    }

    /** ลบประเภท — กันลบถ้ามีหน่วยงานใช้อยู่ (ทุกปี) */
    public function actionTypeDelete($id)
    {
        $c = Categorise::findOne(['id' => (int) $id, 'name' => 'org_unit_type']);
        if ($c !== null) {
            if (OrgUnit::find()->where(['unit_type' => $c->code])->exists()) {
                Yii::$app->session->setFlash('error', 'ลบไม่ได้ — ประเภท "' . $c->title . '" มีหน่วยงานใช้อยู่ (ปิดใช้แทนได้)');
            } else {
                $c->delete();
                Yii::$app->session->setFlash('success', 'ลบประเภทแล้ว');
            }
        }
        return $this->redirect(['index']);
    }

    private function genTypeCode(): string
    {
        do {
            $code = 'OU_' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $exists = (new Query())->from('categorise')->where(['name' => 'org_unit_type', 'code' => $code])->exists();
        } while ($exists);
        return $code;
    }

    /** บันทึกแบบกลุ่ม — อักษรย่อ/ประเภท/เปิดใช้ (+ ชื่อ/หัวหน้า สำหรับหน่วย manual) */
    public function actionSave()
    {
        $year = (int) ($this->request->post('thai_year') ?: PlanHelper::currentPlanYear());
        $errors = [];
        foreach ((array) $this->request->post('rows', []) as $id => $data) {
            $model = OrgUnit::findOne((int) $id);
            if ($model === null || (int) $model->thai_year !== $year) {
                continue;
            }
            $code = strtoupper(trim((string) ($data['code'] ?? '')));
            $model->code = $code !== '' ? $code : null;
            $model->unit_type = ($data['unit_type'] ?? '') !== '' ? (string) $data['unit_type'] : null;
            $model->active = !empty($data['active']);
            if ($model->source === OrgUnit::SOURCE_MANUAL) {
                if (isset($data['name']) && trim((string) $data['name']) !== '') {
                    $model->name = trim((string) $data['name']);
                }
                $model->leader_emp_id = !empty($data['leader_emp_id']) ? (int) $data['leader_emp_id'] : null;
            }
            if (!$model->save()) {
                $errors[] = $model->name . ': ' . implode(' ', $model->getFirstErrors());
            }
        }
        if ($errors) {
            Yii::$app->session->setFlash('warning', 'บันทึกบางส่วนไม่สำเร็จ: ' . implode(' | ', $errors));
        } else {
            Yii::$app->session->setFlash('success', 'บันทึกทะเบียนหน่วยงานเรียบร้อย');
        }
        return $this->redirect(['index', 'thai_year' => $year]);
    }

    /** เพิ่มหน่วยงานภายใน (manual) */
    public function actionAdd()
    {
        $year = (int) ($this->request->post('thai_year') ?: PlanHelper::currentPlanYear());
        $code = strtoupper(trim((string) $this->request->post('code', '')));
        $model = new OrgUnit([
            'thai_year' => $year,
            'source' => OrgUnit::SOURCE_MANUAL,
            'unit_type' => (string) $this->request->post('unit_type', '') ?: null,
            'name' => trim((string) $this->request->post('name', '')),
            'code' => $code !== '' ? $code : null,
            'leader_emp_id' => (int) $this->request->post('leader_emp_id', 0) ?: null,
            'active' => 1,
            'sort' => 999,
        ]);
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'เพิ่มหน่วยงาน "' . $model->name . '" แล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'เพิ่มไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['index', 'thai_year' => $year]);
    }

    /** ซิงก์หน่วยงานจากผังโครงสร้าง (tree) เข้าปีที่เลือก */
    public function actionSync()
    {
        $year = (int) ($this->request->post('thai_year') ?: PlanHelper::currentPlanYear());
        $r = OrgUnit::syncStructure($year);
        Yii::$app->session->setFlash('success', "ซิงก์จากผังโครงสร้างแล้ว — เพิ่ม {$r['added']}, อัปเดต {$r['updated']} หน่วย");
        return $this->redirect(['index', 'thai_year' => $year]);
    }

    /** ลบหน่วย manual (หน่วยในโครงสร้างลบไม่ได้ ให้ปิดใช้แทน) */
    public function actionDelete($id)
    {
        $model = OrgUnit::findOne((int) $id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบหน่วยงาน');
        }
        $year = (int) $model->thai_year;
        if ($model->source !== OrgUnit::SOURCE_MANUAL) {
            Yii::$app->session->setFlash('error', 'ลบได้เฉพาะหน่วยที่เพิ่มเอง — หน่วยในโครงสร้างให้ปิดใช้แทน');
            return $this->redirect(['index', 'thai_year' => $year]);
        }
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบหน่วยงานแล้ว');
        return $this->redirect(['index', 'thai_year' => $year]);
    }

    private function years(int $current): array
    {
        $years = array_map('intval', (new Query())->select('thai_year')->from('org_unit')->distinct()->column());
        if (!in_array($current, $years, true)) {
            $years[] = $current;
        }
        rsort($years);
        return $years;
    }

    /** ระดับชั้นในผัง (tree.id => lvl) สำหรับเยื้องหน่วยงานโครงสร้าง */
    private function levelMap(): array
    {
        return ArrayHelper::map(
            (new Query())->select(['id', 'lvl'])->from('tree')->all(),
            'id',
            'lvl'
        );
    }

    private function typeMap(): array
    {
        return ArrayHelper::map(
            Categorise::find()->where(['name' => 'org_unit_type', 'active' => 1])->orderBy(['sort' => SORT_ASC])->all(),
            'code',
            'title'
        );
    }

    private function employeeMap(): array
    {
        $rows = (new Query())
            ->select(['id', 'nm' => new Expression("TRIM(CONCAT(COALESCE(fname,''),' ',COALESCE(lname,'')))")])
            ->from('employees')
            ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])
            ->all();
        return ArrayHelper::map($rows, 'id', 'nm');
    }

    private function typeCounts(int $year): array
    {
        $rows = (new Query())
            ->select(['unit_type', 'n' => 'COUNT(*)'])
            ->from('org_unit')->where(['thai_year' => $year])
            ->groupBy('unit_type')->all();
        return ArrayHelper::map($rows, 'unit_type', 'n');
    }

    private function srcCounts(int $year): array
    {
        $rows = (new Query())
            ->select(['source', 'n' => 'COUNT(*)'])
            ->from('org_unit')->where(['thai_year' => $year])
            ->groupBy('source')->all();
        return ArrayHelper::map($rows, 'source', 'n');
    }
}
