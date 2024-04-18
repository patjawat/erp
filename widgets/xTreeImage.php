<?php

namespace app\widgets;

use Yii;
use yii\helpers\Html;
use yii\web\View;

/**
 *
 * 
 * @author Muh Samsul Huda <muh.samsulhuda714@gmail.com>
 * @since 1.0
 */
class TreeImage extends \yii\bootstrap\Widget
{
      
    

    public $root = 'Root';

    public $icon = 'user';

    public $iconRoot = 'tree-conifer';
    
    public $query;


    public function init()
    {
        Asset::register($this->getView());
        $this->initTreeView();
    }

    protected function initTreeView()
    {   
        $icon1 = '<span class="glyphicon glyphicon-'.$this->icon.'"></span>';
        $iconRoot = '<span class="glyphicon glyphicon-'.$this->iconRoot.'"></span>';

        $dataArray = $this->query->asArray()->all();

        $nodeDepth = $currDepth = $counter = 0;

        echo Html::beginTag('div', ['class' => 'tree']);
                echo Html::beginTag('ul') . "\n" .Html::beginTag('li') . "\n" ;
                echo '<a href="#">'.$iconRoot.'  '.$this->root.'</a>' . "\n" ;

        foreach ($dataArray as $key) {
            if ($key['lvl'] == 0 && $currDepth == 0) 
            {
                echo Html::beginTag('ul') . "\n" .Html::beginTag('li') . "\n" ;
                echo '<a href="#">'.$icon1.'  '.$key['name'].'</a>' . "\n" ;
            }  else
            {
                $as = $currDepth-1;
                $sa = ${'x'.$as}+1;
                if ($key['lvl'] == ${'x'.$as}) {
                    echo Html::beginTag('li') . "\n";
                    echo '<a href="#">'.$icon1.'  '.$key['name'].'</a>' . "\n" ;
                    echo  Html::endTag('/li') . "\n";
                } else if ($key['lvl'] == $sa){
                    echo Html::beginTag('ul') . "\n" .Html::beginTag('li') . "\n" ;
                    echo '<a href="#">'.$icon1.'  '.$key['name'].'</a>' . "\n" ;
                } else
                {
                    $da = ${'x'.$as}-1;
                    if ($key['lvl'] == $da) {
                        echo Html::endTag('li') . "\n" ;
                        echo Html::endTag('ul') . "\n" ;
                        echo Html::beginTag('li') . "\n" ;
                        echo '<a href="#">'.$icon1.'  '.$key['name'].'</a>' . "\n" ;
                    }else
                    {
                        $hasil = ${'x'.$as} - $key['lvl'];
                        for ($i=0; $i < $hasil ; $i++) { 
                            echo Html::endTag('li') . "\n" ;
                            echo Html::endTag('ul') . "\n" ;
                        }
                        echo Html::beginTag('li') . "\n" ;
                        echo '<a href="#">'.$icon1.'  '.$key['name'].'</a>' . "\n" ;
                    }
                }
            }      

            ${'x'.$currDepth} = $key['lvl'];    
            ++$currDepth;
            ++$nodeDepth;
        }

        echo Html::endTag('div');
    }
}