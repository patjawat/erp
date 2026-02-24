<?php

namespace app\widgets;

use app\assets\TomSelectAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\InputWidget;

/**
 * Tom-Select widget สำหรับ dropdown แบบค้นหาได้ (และโหลดจาก AJAX ได้)
 *
 * ตัวอย่าง (ไม่มี model):
 * ```php
 * echo TomSelectWidget::widget([
 *     'name' => 'item_code',
 *     'id' => 'item_code',
 *     'options' => ['class' => 'form-select', 'required' => true],
 *     'items' => ['' => 'ค้นหา...'],
 *     'clientOptions' => [
 *         'valueField' => 'item_code',
 *         'labelField' => 'item_name',
 *         'searchField' => ['item_name', 'item_code'],
 *         'placeholder' => 'ค้นหาชื่อหรือรหัส...',
 *     ],
 *     'loadUrl' => Url::to(['/inventory-v2/stock-item/item-list']),
 *     'loadUrlParamKeys' => ['warehouse_id'], // optional: ส่งพารามจาก DOM
 * ]);
 * ```
 *
 * ตัวอย่าง (มี model):
 * ```php
 * echo $form->field($model, 'item_code')->widget(TomSelectWidget::class, [
 *     'clientOptions' => [...],
 *     'loadUrl' => [...],
 * ]);
 * ```
 */
class TomSelectWidget extends InputWidget
{
    /** @var array รายการ option เริ่มต้น [value => label] (ใช้เมื่อไม่มี loadUrl หรือเป็น static) */
    public $items = [];

    /** @var array options ส่งไป TomSelect (valueField, labelField, searchField, placeholder, onChange, ...) */
    public $clientOptions = [];

    /** @var string|null URL สำหรับโหลดรายการแบบ AJAX (GET ?q=...) ถ้าใส่จะสร้าง load() ให้ */
    public $loadUrl;

    /** @var string[] ชื่อพารามที่เพิ่มใน query (ค่าอ่านจาก DOM เช่น ['warehouse_id'] => อ่านจาก #warehouse_id) */
    public $loadUrlParamKeys = [];

    /** @var string ชื่อ key ใน response ที่เป็น array (เช่น 'results') ถ้าไม่ใส่จะใช้ทั้ง response เป็น array */
    public $loadResponseKey = 'results';

    /** @var string|null ถ้ากำหนด จะเก็บ instance TomSelect ไว้ที่ window[name] เพื่อให้ JS อื่นเรียกใช้ (เช่น getValue()) */
    public $instanceName;

    public function init()
    {
        parent::init();
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        if (empty($this->items)) {
            $this->items = ['' => ''];
        }
    }

    public function run()
    {
        TomSelectAsset::register($this->view);

        if ($this->hasModel()) {
            $select = Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options);
        } else {
            $select = Html::dropDownList($this->name, $this->value, $this->items, $this->options);
        }

        echo $select;

        $id = $this->options['id'];
        $opts = $this->clientOptions;

        if ($this->loadUrl !== null && $this->loadUrl !== '') {
            $loadUrl = Json::encode($this->loadUrl);
            $paramKeys = Json::encode($this->loadUrlParamKeys);
            $resKey = Json::encode($this->loadResponseKey);
            unset($opts['load']);
            $loadFn = "function(q,callback){var u=$loadUrl;var sep=u.indexOf('?')>=0?'&':'?';u+=sep+'q='+encodeURIComponent(typeof q==='string'?q:'');var keys=$paramKeys;if(Array.isArray(keys)){keys.forEach(function(k){var el=document.getElementById(k);if(el)u+='&'+k+'='+encodeURIComponent(el.value||'');});}fetch(u).then(function(r){return r.json();}).then(function(j){var key=$resKey;var list=key&&j[key]!==undefined?j[key]:j;callback(Array.isArray(list)?list:[]);}).catch(function(){callback([]);});}";
            $optsJson = Json::encode($opts);
            $inst = $this->instanceName
                ? 'var _inst=new TomSelect(\'#' . $id . '\',_opts);if(typeof window!==\'undefined\'){window[' . Json::encode($this->instanceName) . ']=_inst;}'
                : 'new TomSelect(\'#' . $id . '\',_opts)';
            $js = 'if(typeof TomSelect!==\'undefined\'){var _opts=' . $optsJson . ';_opts.load=' . $loadFn . ';' . $inst . ';}';
        } else {
            $optsJson = Json::encode($opts);
            $inst = $this->instanceName
                ? 'var _inst=new TomSelect(\'#' . $id . '\',' . $optsJson . ');if(typeof window!==\'undefined\'){window[' . Json::encode($this->instanceName) . ']=_inst;}'
                : 'new TomSelect(\'#' . $id . '\',' . $optsJson . ')';
            $js = 'if(typeof TomSelect!==\'undefined\'){' . $inst . ';}';
        }
        $this->view->registerJs($js, View::POS_END);
    }
}
