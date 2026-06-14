<?php

namespace app\modules\mobile\services;

use app\modules\am\models\Asset;
use app\modules\am\models\AssetDetail;
use app\modules\filemanager\models\Uploads;
use app\modules\helpdesk2\models\Helpdesk;

/**
 * รวบรวมประวัติของทรัพย์สิน (Asset) จากตารางจริงในระบบ:
 *  - asset_detail (name = maintenance/borrow/calibration/asset-move/asset_document/pm)
 *  - helpdesk (งานแจ้งซ่อมที่ผูกกับ asset_number)
 *  - uploads (เอกสารแนบที่ผูกกับ asset.ref)
 *
 * ออกแบบให้ "graceful degrade" — ถ้า query ใดล้ม (table หรือ column ขาด)
 * จะคืน [] เพื่อให้ view แสดง empty state ไม่ฟัง stack trace
 */
class MobileAssetHistoryService
{
    /**
     * ดึงประวัติของ asset ครบทุกหมวด แต่ละหมวดมี items ล่าสุด + total
     *
     * @return array<string,array{
     *     items:array<int,array{title:string,desc:string,datetime:string,tone:string,meta:string}>,
     *     total:int
     * }>
     */
    public function gather(Asset $asset, int $perCategoryLimit = 5): array
    {
        return [
            'maintenance' => $this->maintenance($asset, $perCategoryLimit),
            'pm'          => $this->byName($asset, 'pm', $perCategoryLimit),
            'calibration' => $this->byName($asset, 'calibration', $perCategoryLimit),
            'borrow'      => $this->borrow($asset, $perCategoryLimit),
            'move'        => $this->move($asset, $perCategoryLimit),
            'document'    => $this->documents($asset, $perCategoryLimit),
        ];
    }

    /**
     * รวมประวัติซ่อมจาก 2 แหล่ง: asset_detail.name='maintenance' + helpdesk (mobile)
     */
    private function maintenance(Asset $asset, int $limit): array
    {
        $items = [];
        try {
            $rows = AssetDetail::find()
                ->where(['asset_id' => (int) $asset->id, 'name' => 'maintenance'])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                ->limit($limit * 2)
                ->all();
            foreach ($rows as $row) {
                $data = $this->safeJson($row->data_json);
                $title = (string) ($data['title'] ?? $data['ma_topic'] ?? $data['topic'] ?? 'แจ้งซ่อม');
                $desc  = (string) ($data['description'] ?? $data['detail'] ?? $data['result'] ?? '');
                $items[] = [
                    'title'    => $title !== '' ? $title : 'แจ้งซ่อม',
                    'desc'     => $desc,
                    'datetime' => $this->formatDt((string) $row->created_at),
                    'tone'     => 'success',
                    'meta'     => trim((string) ($data['vendor'] ?? '')),
                ];
            }
        } catch (\Throwable $e) {}

        // เพิ่มเติม: ดึงจาก helpdesk (จากระบบแจ้งซ่อม mobile) ที่ผูกกับรหัสครุภัณฑ์
        try {
            $code = trim((string) ($asset->code ?? ''));
            if ($code !== '') {
                $hd = Helpdesk::find()
                    ->where(['asset_number' => $code])
                    ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                    ->limit($limit * 2)
                    ->all();
                foreach ($hd as $row) {
                    $data = $this->safeJson($row->data_json);
                    $items[] = [
                        'title'    => (string) ($row->title ?: ($data['problem_type_label'] ?? 'แจ้งซ่อม')),
                        'desc'     => (string) ($data['description'] ?? ''),
                        'datetime' => $this->formatDt((string) $row->created_at),
                        'tone'     => 'success',
                        'meta'     => (string) ($row->repair_number ?: ''),
                    ];
                }
            }
        } catch (\Throwable $e) {}

        usort($items, function ($a, $b) { return strcmp($b['datetime'], $a['datetime']); });
        $total = count($items);
        return ['items' => array_slice($items, 0, $limit), 'total' => $total];
    }

    /** Query generic AssetDetail by name */
    private function byName(Asset $asset, string $name, int $limit): array
    {
        $items = [];
        try {
            $rows = AssetDetail::find()
                ->where(['asset_id' => (int) $asset->id, 'name' => $name])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                ->limit($limit)
                ->all();
            foreach ($rows as $row) {
                $data = $this->safeJson($row->data_json);
                $items[] = [
                    'title'    => (string) ($data['title'] ?? $data['ma_topic'] ?? $data['topic'] ?? 'รายการ'),
                    'desc'     => (string) ($data['description'] ?? $data['detail'] ?? $data['result'] ?? ''),
                    'datetime' => $this->formatDt((string) $row->created_at),
                    'tone'     => 'success',
                    'meta'     => (string) ($data['vendor'] ?? $data['by'] ?? ''),
                ];
            }
            $total = (int) AssetDetail::find()
                ->where(['asset_id' => (int) $asset->id, 'name' => $name])
                ->count();
            return ['items' => $items, 'total' => $total];
        } catch (\Throwable $e) {
            return ['items' => [], 'total' => 0];
        }
    }

    private function borrow(Asset $asset, int $limit): array
    {
        $items = [];
        $total = 0;
        try {
            $rows = AssetDetail::find()
                ->where(['asset_id' => (int) $asset->id, 'name' => 'borrow'])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                ->limit($limit)
                ->all();
            foreach ($rows as $row) {
                $data = $this->safeJson($row->data_json);
                $borrower = (string) ($data['borrower_name'] ?? $data['borrow_name'] ?? $data['user'] ?? '');
                $purpose  = (string) ($data['purpose'] ?? $data['reason'] ?? '');
                $returnDt = (string) ($data['return_date'] ?? '');
                $isReturned = trim($returnDt) !== '' && $returnDt !== '0000-00-00';
                $items[] = [
                    'title'    => $borrower !== '' ? ('ยืมโดย ' . $borrower) : 'รายการยืม',
                    'desc'     => $purpose,
                    'datetime' => $this->formatDt((string) $row->created_at),
                    'tone'     => $isReturned ? 'success' : 'warning',
                    'meta'     => $isReturned ? ('คืนเมื่อ ' . $this->formatDt($returnDt)) : 'ยังไม่คืน',
                ];
            }
            $total = (int) AssetDetail::find()
                ->where(['asset_id' => (int) $asset->id, 'name' => 'borrow'])
                ->count();
        } catch (\Throwable $e) {}
        return ['items' => $items, 'total' => $total];
    }

    private function move(Asset $asset, int $limit): array
    {
        $items = [];
        $total = 0;
        try {
            $rows = AssetDetail::find()
                ->where(['asset_id' => (int) $asset->id, 'name' => 'asset-move'])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                ->limit($limit)
                ->all();
            foreach ($rows as $row) {
                $data = $this->safeJson($row->data_json);
                $from = (string) ($data['from_location'] ?? $data['from'] ?? $data['from_department'] ?? '');
                $to   = (string) ($data['to_location']   ?? $data['to']   ?? $data['to_department']   ?? '');
                $reason = (string) ($data['reason'] ?? $data['note'] ?? '');
                $titleParts = [];
                if ($from !== '') $titleParts[] = $from;
                if ($to !== '')   $titleParts[] = $to;
                $titleStr = $titleParts ? implode(' → ', $titleParts) : 'การเคลื่อนย้าย';
                $items[] = [
                    'title'    => $titleStr,
                    'desc'     => $reason,
                    'datetime' => $this->formatDt((string) $row->created_at),
                    'tone'     => 'document',
                    'meta'     => '',
                ];
            }
            $total = (int) AssetDetail::find()
                ->where(['asset_id' => (int) $asset->id, 'name' => 'asset-move'])
                ->count();
        } catch (\Throwable $e) {}
        return ['items' => $items, 'total' => $total];
    }

    private function documents(Asset $asset, int $limit): array
    {
        $items = [];
        $total = 0;
        try {
            $rows = AssetDetail::find()
                ->where(['asset_id' => (int) $asset->id, 'name' => 'asset_document'])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                ->limit($limit)
                ->all();
            foreach ($rows as $row) {
                $data = $this->safeJson($row->data_json);
                $fileTitle = (string) ($data['title'] ?? $data['document_name'] ?? 'เอกสาร');
                $items[] = [
                    'title'    => $fileTitle,
                    'desc'     => (string) ($data['description'] ?? ''),
                    'datetime' => $this->formatDt((string) $row->created_at),
                    'tone'     => 'document',
                    'meta'     => (string) ($data['document_type'] ?? ''),
                ];
            }
            $total = (int) AssetDetail::find()
                ->where(['asset_id' => (int) $asset->id, 'name' => 'asset_document'])
                ->count();
        } catch (\Throwable $e) {}

        // เพิ่มเติม: นับ uploads ที่ผูกกับ asset->ref (รูปและไฟล์)
        try {
            $ref = (string) ($asset->ref ?? '');
            if ($ref !== '') {
                $upRows = Uploads::find()->where(['ref' => $ref])->limit($limit)->all();
                foreach ($upRows as $up) {
                    $items[] = [
                        'title'    => (string) ($up->file_name ?? 'ไฟล์แนบ'),
                        'desc'     => (string) ($up->name ?? ''),
                        'datetime' => $this->formatDt((string) ($up->created_at ?? '')),
                        'tone'     => 'document',
                        'meta'     => strtoupper(pathinfo((string) $up->file_name, PATHINFO_EXTENSION) ?: ''),
                    ];
                }
                $total += (int) Uploads::find()->where(['ref' => $ref])->count();
            }
        } catch (\Throwable $e) {}

        usort($items, function ($a, $b) { return strcmp($b['datetime'], $a['datetime']); });
        return ['items' => array_slice($items, 0, $limit), 'total' => $total];
    }

    private function safeJson($value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && $value !== '') {
            try {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            } catch (\Throwable $e) { return []; }
        }
        return [];
    }

    private function formatDt(string $value): string
    {
        $value = trim($value);
        if ($value === '' || $value === '0000-00-00 00:00:00' || $value === '0000-00-00') return '';
        $ts = strtotime($value);
        if ($ts === false) return $value;
        try {
            $date = \app\components\ThaiDateHelper::formatThaiDate(date('Y-m-d', $ts), 'medium');
            return $date . ' ' . date('H:i', $ts) . ' น.';
        } catch (\Throwable $e) {
            return date('Y-m-d H:i', $ts);
        }
    }
}
