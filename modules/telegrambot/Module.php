<?php

namespace app\modules\telegrambot;

/**
 * telegrambot module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\telegrambot\controllers';

    public $botToken; // ตั้งค่า Token ที่นี่
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        //  if (!$this->botToken) {
        //     throw new \Exception('Telegram bot token must be configured');
        // }
        // custom initialization code goes here
    }
}
