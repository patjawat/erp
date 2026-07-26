<?php

use yii\db\Migration;

/**
 * Adds the user-provided six-month anesthesia nurse roadmap as an editable draft.
 */
class m260725_000002_seed_anesthesia_training_roadmap extends Migration
{
    public function safeUp()
    {
        if ((new \yii\db\Query())->from('{{%training_roadmap}}')->where(['code' => 'NUR-ANE-TRM-001', 'version_no' => 1])->exists()) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->insert('{{%training_roadmap}}', [
            'code' => 'NUR-ANE-TRM-001',
            'title' => 'Training Roadmap พยาบาลวิสัญญีใหม่',
            'roadmap_type' => 'professional',
            'version_no' => 1,
            'duration_value' => 6,
            'duration_unit' => 'month',
            'description' => 'แนวทางการพัฒนาและประเมินสมรรถนะพยาบาลวิสัญญีใหม่ในระยะ 6 เดือน เพื่อให้ประเมินและเตรียมผู้ป่วย เตรียมเครื่องมือและยา เฝ้าระวังระหว่างระงับความรู้สึก และตอบสนองภาวะฉุกเฉินได้อย่างปลอดภัย',
            'target_json' => json_encode([
                'professions' => ['พยาบาลวิชาชีพ'],
                'positions' => ['พยาบาลวิสัญญี'],
                'audience' => 'บุคลากรใหม่',
            ], JSON_UNESCAPED_UNICODE),
            'status' => 'draft',
            'ref' => 'seed-nur-ane-trm-001-v1',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $roadmapId = $this->db->getLastInsertID();

        $phases = [
            ['เตรียมพร้อม / ปฐมนิเทศ', 'ก่อนเริ่มงาน', 0, 0, 'รู้จักหน่วยวิสัญญี ระบบงาน บทบาทหน้าที่ และทบทวน Patient Safety', [
                ['รู้จักหน่วยวิสัญญี ระบบงาน และบทบาทหน้าที่', 'orientation', 'ORG-ORIENTATION', 2, 'Preceptor / พี่เลี้ยงประจำ', 'complete', null],
                ['ทบทวนพื้นฐานการระงับความรู้สึกและ Patient Safety', 'self_learning', 'ANE-PATIENT-SAFETY', 2, 'Case-based Learning', 'pass_fail', null],
                ['มาตรฐานการป้องกันการติดเชื้อและความปลอดภัยในห้องผ่าตัด', 'checklist', 'IPC-OR-SAFETY', 3, 'Competency Checklist', 'checklist', null],
            ]],
            ['OR Orientation & Safety', 'สัปดาห์ที่ 1–2', 1, 2, 'ปฏิบัติตามมาตรฐานความปลอดภัยและทำงานร่วมกับทีมผ่าตัดได้', [
                ['Patient identification และ Surgical Safety Checklist', 'checklist', 'OR-SAFETY', 3, 'Bedside Coaching', 'checklist', null],
                ['Hand hygiene, PPE และ Aseptic technique', 'demonstration', 'IPC-ASEPTIC', 3, 'Return Demonstration', 'pass_fail', null],
                ['เรียนรู้การทำงานร่วมกับทีมผ่าตัดและทีมวิสัญญี', 'coaching', 'TEAMWORK', 2, 'Preceptor / พี่เลี้ยงประจำ', 'complete', null],
            ]],
            ['Machine & Airway Preparation', 'สัปดาห์ที่ 3–4', 3, 4, 'เตรียมเครื่องดมยาสลบ เครื่องเฝ้าระวัง และอุปกรณ์ทางเดินหายใจได้ถูกต้อง', [
                ['ตรวจเครื่องดมยาสลบ เครื่องเฝ้าระวัง และ suction', 'checklist', 'ANE-EQUIPMENT', 3, 'Competency Checklist', 'checklist', null],
                ['เตรียม airway devices: mask, ETT, LMA และ laryngoscope', 'demonstration', 'ANE-AIRWAY', 3, 'Return Demonstration', 'pass_fail', null],
                ['เตรียม IV line ยา และอุปกรณ์สำหรับผู้ป่วยก่อนผ่าตัด', 'practice', 'ANE-DRUGS', 3, 'Bedside Coaching', 'pass_fail', null],
            ]],
            ['Basic Anesthesia Care', 'เดือนที่ 2', 2, 2, 'ช่วยเตรียมและส่งยาระงับความรู้สึก พร้อมเฝ้าระวังผู้ป่วยภายใต้การกำกับ', [
                ['ประเมินผู้ป่วยก่อนระงับความรู้สึก: History, Risk, Consent และ Pre-op check', 'case', 'ANE-PRE-ASSESS', 3, 'Case-based Learning', 'count', 10],
                ['Medication safety, double check และ documentation', 'checklist', 'MED-SAFETY', 3, 'Bedside Coaching', 'checklist', null],
                ['เฝ้าระวัง vital signs, ECG, SpO2, BP และ ETCO2', 'practice', 'ANE-MONITORING', 3, 'Bedside Coaching', 'count', 10],
            ]],
            ['Complication & Emergency Care', 'เดือนที่ 3–4', 3, 4, 'เฝ้าระวังและตอบสนองต่อภาวะแทรกซ้อนหรือเหตุฉุกเฉินได้อย่างเหมาะสม', [
                ['Difficult airway และการเรียกทีมช่วยเหลือ', 'simulation', 'ANE-DIFFICULT-AIRWAY', 3, 'Simulation', 'pass_fail', null],
                ['Hypotension, Bradycardia และ Anaphylaxis', 'simulation', 'ANE-COMPLICATION', 3, 'Simulation', 'pass_fail', null],
                ['CPR, emergency drugs และ rapid response in OR', 'simulation', 'EMERGENCY-RESPONSE', 3, 'Simulation', 'pass_fail', null],
                ['รายงานแพทย์และช่วยจัดการเหตุฉุกเฉินอย่างเหมาะสม', 'case', 'CLINICAL-COMMUNICATION', 3, 'Case-based Learning', 'count', 5],
            ]],
            ['Advanced Practice & Recovery', 'เดือนที่ 5–6', 5, 6, 'ดูแลผู้ป่วยต่อเนื่องใน PACU และผ่านการประเมินสมรรถนะขั้นสุดท้าย', [
                ['ช่วยดูแลผู้ป่วยหลังระงับความรู้สึกและการส่งต่อ PACU', 'practice', 'ANE-PACU', 4, 'Bedside Coaching', 'count', 10],
                ['Pain management, recovery observation และ handoff', 'case', 'ANE-RECOVERY', 4, 'Case-based Learning', 'count', 10],
                ['Final competency assessment และ sign-off', 'checklist', 'ANE-FINAL', 4, 'Competency Checklist', 'checklist', null],
            ]],
        ];

        $phaseIds = [];
        foreach ($phases as $phaseIndex => $phase) {
            $this->insert('{{%training_roadmap_phase}}', [
                'roadmap_id' => $roadmapId, 'sequence' => $phaseIndex + 1,
                'title' => $phase[0], 'period_label' => $phase[1],
                'start_offset' => $phase[2], 'end_offset' => $phase[3],
                'offset_unit' => $phaseIndex < 3 ? 'week' : 'month',
                'description' => $phase[4], 'color_role' => 'primary',
                'ref' => "seed-nur-ane-phase-" . ($phaseIndex + 1),
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $phaseId = $this->db->getLastInsertID();
            $phaseIds[] = $phaseId;
            foreach ($phase[5] as $activityIndex => $activity) {
                $this->insert('{{%training_roadmap_activity}}', [
                    'phase_id' => $phaseId, 'sequence' => $activityIndex + 1,
                    'title' => $activity[0], 'activity_type' => $activity[1],
                    'competency_code' => $activity[2], 'competency_level' => $activity[3],
                    'development_method' => $activity[4], 'requirement_type' => $activity[5],
                    'target_value' => $activity[6], 'is_required' => 1,
                    'evidence_required' => in_array($activity[5], ['checklist', 'pass_fail'], true) ? 1 : 0,
                    'ref' => "seed-nur-ane-activity-" . ($phaseIndex + 1) . '-' . ($activityIndex + 1),
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        $milestones = [
            [1, 'ผ่าน Basic OR Safety และ Infection Control Checklist', 1, 'month', 'ผ่านรายการความปลอดภัยและการควบคุมการติดเชื้อที่เป็นข้อบังคับ'],
            [2, 'เตรียมเครื่องมือและยาภายใต้การกำกับได้', 2, 'month', 'ผ่าน Checklist การเตรียมเครื่องดมยา อุปกรณ์ทางเดินหายใจ และยา'],
            [4, 'เฝ้าระวังและจัดการภาวะแทรกซ้อนเบื้องต้นได้', 4, 'month', 'ผ่าน Simulation ภาวะฉุกเฉินและมีผู้ประเมินรับรอง'],
            [6, 'ผ่าน Competency Checklist และปฏิบัติงานได้', 6, 'month', 'กิจกรรมบังคับครบทั้งหมดและผ่าน Final competency assessment'],
        ];
        foreach ($milestones as $index => $milestone) {
            $this->insert('{{%training_roadmap_milestone}}', [
                'roadmap_id' => $roadmapId,
                'phase_id' => $phaseIds[$milestone[0] - 1] ?? null,
                'sequence' => $index + 1, 'title' => $milestone[1],
                'due_offset' => $milestone[2], 'offset_unit' => $milestone[3],
                'criteria_text' => $milestone[4], 'requires_signoff' => 1,
                'ref' => 'seed-nur-ane-milestone-' . ($index + 1),
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function safeDown()
    {
        $id = (new \yii\db\Query())->select('id')->from('{{%training_roadmap}}')
            ->where(['code' => 'NUR-ANE-TRM-001', 'version_no' => 1])->scalar();
        if ($id) {
            $this->delete('{{%training_roadmap}}', ['id' => $id]);
        }
    }
}
