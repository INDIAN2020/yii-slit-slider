<?php

// auto-loading
Yii::setPathOfAlias('Slit', dirname(__FILE__));
Yii::import('Slit.*');
Yii::import('vendor.phundament.p3media.models.*');

class Slit extends BaseSlit
{

    // Add your model-specific methods here. This file will not be overriden by gtc except you force it.
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function init()
    {
        return parent::init();
    }

    public function get_label()
    {
        return (string) $this->status;
    }

    public function behaviors()
    {
        return array_merge(
                parent::behaviors(), array(
            'CTimestampBehavior' => array(
                'class' => 'zii.behaviors.CTimestampBehavior',
                'createAttribute' => 'created_at',
                'updateAttribute' => 'updated_at',
            ),
            'OwnerBehavior' => array(
                'class' => 'OwnerBehavior',
                'ownerColumn' => 'created_by',
            ),
        ));
    }

    /**
     * 
     * @param type $media_id
     * @param type $link
     * @param type $title
     * @param type $preset
     * @return type
     */
    public function createImageLink($media_id, $title, $link = array(), $preset = null)
    {
        $createUrl = Yii::app()->controller->createUrl('/p3media/file/image', array(
            'id' => $media_id, 
            'preset' => (isset($preset)) ? $preset : SlitSliderWidget::imagePreset_view));

        $image = CHtml::image($createUrl, $title, array('class' => 'pull-left'));
        $link = CHtml::link($image, is_array($link) ? $link : '', array('class' => 'pull-left btn-info'));
        return $link;
    }
    
    public function createLink($url)
    {
        if (strpos($url,'http') === 0) {
            $link = CHtml::link ($url, $url , array('class' => 'pull-left'));
        } else {
            $link = CHtml::link ($url, '/' . Yii::app()->getLanguage() . '/' . $url , array('class' => 'pull-left'));
        }
        return $link;
    }
    
    /**
     * 
     * @param type $page_id
     * @return type string
     */
    public function getPageName($page_id)
    {
        $thisPage = P3Page::model()->findByAttributes(array('id' => $page_id));
        return "{$thisPage->nameId}";
    }

    public function rules()
    {
        return array_merge(
                parent::rules()
                /* , array(
                  array('column1, column2', 'rule1'),
                  array('column3', 'rule2'),
                  ) */
        );
    }

}
