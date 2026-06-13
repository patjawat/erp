<?php

namespace app\modules\mobile\services;

/**
 * Status taxonomy ที่ใช้ร่วมกันใน mobile booking surfaces (vehicle, meeting, my-requests, ...).
 *
 * ก่อนคลาสนี้ logic การแมพ status → label/tone/bucket ถูก inline ไว้ใน view แต่ละไฟล์
 * (closure `$statusInfo` ใน booking-vehicle.php, booking-meeting.php) ซึ่งใช้ key ต่างกัน
 * ('lbl' vs 'label'). คลาสนี้ standardize ให้ใช้ 'label' เป็น canonical.
 */
class MobileBookingStatus
{
    /**
     * แมพ status code (ทั้ง english และไทย) เป็น label, tone, bucket.
     *
     * @return array{label:string,tone:string,bucket:string}
     */
    public static function info(string $status): array
    {
        static $map = [
            'Pending'    => ['label' => 'รออนุมัติ',  'tone' => 'warning', 'bucket' => 'pending'],
            'รออนุมัติ'   => ['label' => 'รออนุมัติ',  'tone' => 'warning', 'bucket' => 'pending'],
            'Approve'    => ['label' => 'อนุมัติแล้ว', 'tone' => 'success', 'bucket' => 'approved'],
            'Pass'       => ['label' => 'อนุมัติแล้ว', 'tone' => 'success', 'bucket' => 'approved'],
            'อนุมัติแล้ว' => ['label' => 'อนุมัติแล้ว', 'tone' => 'success', 'bucket' => 'approved'],
            'Cancel'     => ['label' => 'ยกเลิก',     'tone' => 'danger',  'bucket' => 'cancelled'],
            'Reject'     => ['label' => 'ปฏิเสธ',     'tone' => 'danger',  'bucket' => 'cancelled'],
            'ยกเลิก'      => ['label' => 'ยกเลิก',     'tone' => 'danger',  'bucket' => 'cancelled'],
            'ปฏิเสธ'      => ['label' => 'ปฏิเสธ',     'tone' => 'danger',  'bucket' => 'cancelled'],
        ];

        return $map[$status] ?? [
            'label'  => $status !== '' ? $status : '-',
            'tone'   => 'secondary',
            'bucket' => 'other',
        ];
    }

    /**
     * นับจำนวนรายการแยกตาม bucket จากชุดของ object/array ที่มี attribute `status`.
     *
     * @param iterable<object|array> $items
     * @return array{all:int,pending:int,approved:int,cancelled:int,other:int}
     */
    public static function bucketCounts(iterable $items, string $statusAttr = 'status'): array
    {
        $counts = ['all' => 0, 'pending' => 0, 'approved' => 0, 'cancelled' => 0, 'other' => 0];
        foreach ($items as $item) {
            $status = is_array($item)
                ? (string) ($item[$statusAttr] ?? '')
                : (string) ($item->$statusAttr ?? '');
            $bucket = self::info($status)['bucket'];
            $counts['all']++;
            $counts[$bucket] = ($counts[$bucket] ?? 0) + 1;
        }
        return $counts;
    }
}
