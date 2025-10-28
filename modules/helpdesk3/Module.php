<?php

namespace app\modules\helpdesk3;

/**
 * helpdesk3 module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\helpdesk3\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}

// https://www.canva.com/ai/code/thread/ecd31fe1-5187-4c26-ade5-abb44e09341f
// https://www.canva.com/ai/code/thread/16fa8321-682d-45fb-9b8f-b7499b2273ee

// update database

// ALTER TABLE `categorise` ADD `sort` VARCHAR(255) NULL AFTER `id`;

// UPDATE `helpdesk` SET `status` = 'pending'     WHERE status = '1';
// UPDATE `helpdesk` SET `status` = 'receive'     WHERE status = '2';
// UPDATE `helpdesk` SET `status` = 'in_progress' WHERE status = '3';
// UPDATE `helpdesk` SET `status` = 'success'     WHERE status = '4';

// UPDATE `helpdesk`
// SET data_json = JSON_SET(data_json, '$.urgency', 'low')
// WHERE name = 'repair' 
//   AND JSON_UNQUOTE(data_json->'$.urgency') = '1';
  
//   UPDATE `helpdesk`
// SET data_json = JSON_SET(data_json, '$.urgency', 'medium')
// WHERE name = 'repair' 
//   AND JSON_UNQUOTE(data_json->'$.urgency') = '2';
  
//   UPDATE `helpdesk`
// SET data_json = JSON_SET(data_json, '$.urgency', 'high')
// WHERE name = 'repair' 
//   AND JSON_UNQUOTE(data_json->'$.urgency') = '3';
  
//   UPDATE `helpdesk`
// SET data_json = JSON_SET(data_json, '$.urgency', 'critical')
// WHERE name = 'repair' 
//   AND JSON_UNQUOTE(data_json->'$.urgency') = '4';
