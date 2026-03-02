<?php

namespace app\modules\appreciation;

/**
 * พลังแห่งคำขอบคุณ (Appreciation Wall)
 * ให้คำขอบคุณต่อกันในหน่วยงาน สะสมคะแนน และร่วม Challenge รับของรางวัล
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\appreciation\controllers';

    /** คะแนนที่ผู้รับได้ต่อ 1 คำขอบคุณ */
    public $pointsPerThank = 50;

    public function init()
    {
        parent::init();
    }
}
