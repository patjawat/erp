<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hit_counter".
 *
 * @property int $id
 * @property string $counter_id
 * @property string|null $cookie_mark
 * @property int $js_cookei_enabled
 * @property int $js_java_enabled
 * @property int|null $js_timezone_offset
 * @property string|null $js_timezone
 * @property string|null $js_connection
 * @property string|null $js_current_url
 * @property string|null $js_referer_url
 * @property int|null $js_screen_width
 * @property int|null $js_screen_height
 * @property int|null $js_color_depth
 * @property string|null $js_browser_language
 * @property int|null $js_history_length
 * @property int $js_is_toutch_device
 * @property int|null $js_processor_ram
 * @property string|null $serv_ip
 * @property string|null $serv_user_agent
 * @property string|null $serv_referer_url
 * @property string|null $serv_server_name
 * @property int|null $serv_auth_user_id
 * @property int|null $serv_port
 * @property string|null $serv_cookies
 * @property string|null $serv_os
 * @property string|null $serv_client
 * @property string|null $serv_device
 * @property string|null $serv_brand
 * @property string|null $serv_model
 * @property string|null $serv_bot
 * @property string|null $serv_host_by_ip
 * @property int $serv_is_proxy_or_vpn
 * @property string $created_at
 */
class HitCounter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hit_counter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['counter_id', 'created_at'], 'required'],
            [['js_cookei_enabled', 'js_java_enabled', 'js_timezone_offset', 'js_screen_width', 'js_screen_height', 'js_color_depth', 'js_history_length', 'js_is_toutch_device', 'js_processor_ram', 'serv_auth_user_id', 'serv_port', 'serv_is_proxy_or_vpn'], 'integer'],
            [['js_current_url', 'js_referer_url', 'serv_user_agent', 'serv_referer_url'], 'string'],
            [['serv_cookies', 'created_at'], 'safe'],
            [['counter_id', 'js_timezone', 'js_connection', 'js_browser_language', 'serv_server_name', 'serv_os', 'serv_client', 'serv_device', 'serv_brand', 'serv_model', 'serv_bot', 'serv_host_by_ip'], 'string', 'max' => 255],
            [['cookie_mark'], 'string', 'max' => 32],
            [['serv_ip'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'counter_id' => 'Counter ID',
            'cookie_mark' => 'Cookie Mark',
            'js_cookei_enabled' => 'Js Cookei Enabled',
            'js_java_enabled' => 'Js Java Enabled',
            'js_timezone_offset' => 'Js Timezone Offset',
            'js_timezone' => 'Js Timezone',
            'js_connection' => 'Js Connection',
            'js_current_url' => 'Js Current Url',
            'js_referer_url' => 'Js Referer Url',
            'js_screen_width' => 'Js Screen Width',
            'js_screen_height' => 'Js Screen Height',
            'js_color_depth' => 'Js Color Depth',
            'js_browser_language' => 'Js Browser Language',
            'js_history_length' => 'Js History Length',
            'js_is_toutch_device' => 'Js Is Toutch Device',
            'js_processor_ram' => 'Js Processor Ram',
            'serv_ip' => 'Serv Ip',
            'serv_user_agent' => 'Serv User Agent',
            'serv_referer_url' => 'Serv Referer Url',
            'serv_server_name' => 'Serv Server Name',
            'serv_auth_user_id' => 'Serv Auth User ID',
            'serv_port' => 'Serv Port',
            'serv_cookies' => 'Serv Cookies',
            'serv_os' => 'Serv Os',
            'serv_client' => 'Serv Client',
            'serv_device' => 'Serv Device',
            'serv_brand' => 'Serv Brand',
            'serv_model' => 'Serv Model',
            'serv_bot' => 'Serv Bot',
            'serv_host_by_ip' => 'Serv Host By Ip',
            'serv_is_proxy_or_vpn' => 'Serv Is Proxy Or Vpn',
            'created_at' => 'Created At',
        ];
    }
}
