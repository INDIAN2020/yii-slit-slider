<?php

/**
 * Class File
 * @author    Christopher Stebe <chris@stebe.eu>
 * @link      https://github.com/Quexer69
 * @copyright Copyright &copy; 2005-2010 diemeisterei GmbH
 * @license   http://www.phundament.com/license/
 */

Yii::setPathOfAlias('Slit', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'models');
Yii::import('Slit.*');

class SlitSliderWidget extends CWidget
{

    const SLIT_ACTIVE           = 'published';
    const IMAGE                 = 'image';
    const HTML                  = 'html';

    public $image_preset        = 'slitslider';
    public $order               = 'rank ASC';

    /**
     *  Call this Widget on which page and position
     *  you what the slit-slider appear
     * 
     * <pre>
     * <?php
     *   $this->widget(
     *      'vendor.quexer69.yii-slit-slider.SlitSliderWidget', 
     *          array(
     *              'image_preset'  => 'slitslider',
     *              'order'         => 'rank DESC',
     *          )
     *   );
     * ?>
     * </pre>
     * {@link SlitController}
     * @author  Christopher Stebe <chris@stebe.eu>
     * @version 0.1.0
     * @package quexer69/yii-slit-slider
     */

    public function run()
    {
        // @var pageID: Get active P3Page->id
        $pageID = $this->getActivePageId();

        // get Slit models for this P3Page and status
        $thisSlits = $this->querySlits($pageID);

        // Check if slits are availible for this P3age
        if ($this->hasSlits($thisSlits)) {

            // Just if there are slits for this P3Page, publish Assets (css, js)
            $this->registerAssets();

            // Output HTML Template (for IMAGE and HTML slits)
            $this->openSliderWrapper();

                foreach ($thisSlits as $slit) {

                    // if slit type -> image
                    if ($slit->type === $this::IMAGE) {

                        $this->showImage($slit->media_id, $this->image_preset, $slit->headline, $slit->subline, $slit->link, $slit->custom_attributes);
                    }
                    // if slit type -> html
                    elseif ($slit->type === $this::HTML) {

                        $this->showHtml($slit->bodyHtml, $slit->custom_attributes);
                    }
                }
                // put needed dots to navigate, first hast class 'nav-dot-current'
                $this->showDots($thisSlits);

            $this->closeSliderWrapper();
        }
    }

    public function getP3MediaNames()
    {
        // TODO: checkAccess for media Files!!
        return P3Media::model()->findAll();
    }

    public function getP3Pages()
    {
        $nameIds = array();

        //FindAll P3Page's
        $p3pages = P3Page::model()->findAll();
        foreach ($p3pages AS $p3page) {

            // If page hast nameId
            if ($p3page->nameId)
                $nameIds[$p3page->id] = $p3page->nameId;
        }
        return $nameIds;
    }

    public function getActivePage()
    {
        if (!P3Page::getActivePage()) {
            return false;
        }else{
            return array(P3Page::getActivePage()->id => P3Page::getActivePage()->nameId);
        }
    }

    public function getActivePageId()
    {
        $activePage = $this->getActivePage();
        foreach ($activePage as $key => $nameId) {

            return $key;
        }
    }

    public function getActivePageNameId()
    {
        $activePage = $this->getActivePage();
        foreach ($activePage as $key => $nameId) {

            return $nameId;
        }
    }

    public function registerAssets()
    {
        $registerScripts = Yii::app()->getClientScript();

        // JS files
        $js = Yii::app()->assetManager->publish(Yii::getPathOfAlias('SlitAssets') . '/js', true, -1, true); // set last param to `true` for development
        $registerScripts->registerScriptFile($js . "/jquery.slitslider.js", CClientScript::POS_END);
        $registerScripts->registerScriptFile($js . "/jquery.slitslider.init.js", CClientScript::POS_END);

        // CSS files
        $css = Yii::app()->assetManager->publish(Yii::getPathOfAlias('SlitAssets') . '/css', true, -1, true); // set last param to `true` for development
        $registerScripts->registerCssFile($css . '/slitslider.css');
    }

    public function querySlits($pageID)
    {
        $criteria = new CDbCriteria();
        $criteria->order = $this->order;
        $criteria->addSearchCondition('page_name', $pageID);
        $criteria->addSearchCondition('status', $this::SLIT_ACTIVE);
        $criteria->addSearchCondition('language', Yii::app()->getLanguage());

        // findAll with this $creteria
        return Slit::model()->findAll($criteria);
    }

    public function hasSlits($allSlits)
    {
        if (sizeof($allSlits) > 0) {
            return true;
        }
        return false;
    }

    public function hasDots($allSlits)
    {
        if (sizeof($allSlits) > 1) {
            return true;
        }
        return false;
    }

    public function showImage($id, $preset, $headline, $subline, $link, $custom_attributes)
    {
        $imgSrc = Yii::app()->controller->createUrl('/p3media/file/image', array('id' => $id, 'preset' => $preset));

        echo "      <div class=\"sl-slide\" {$custom_attributes}>\n";
        echo "          <div class=\"sl-slide-inner\">\n";
        echo "              <div class=\"bg-img centerHtml\">\n";
        echo "                  <img src=\"{$imgSrc}\" alt=\"\" />";
        echo "              </div>\n";
        echo "                    <h2>{$headline}</h2>\n";
        echo "                    <blockquote>\n";
        echo "                        <p>{$subline}</p>\n";
        echo "                        <cite>{$link}</cite></blockquote>\n";
        echo "          </div>\n";
        echo "      </div>\n";
    }

    public function showHtml($code, $custom_attributes)
    {
        echo "      <div class=\"sl-slide\" {$custom_attributes}>\n";
        echo "          <div class=\"sl-slide-inner\">\n";
        echo "              <div class=\"centerHtml\">\n";
        echo $code;
        echo "              </div>\n";
        echo "          </div>\n";
        echo "      </div>\n";
    }

    public function showDots($allSlits)
    {
        if ($this->hasDots($allSlits)) {
            $_size = sizeof($allSlits);

            if ($_size > 1) {

                echo "      <nav class=\"nav-dots\" id=\"nav-dots\">\n";
                echo "          <span class=\"nav-dot-current\"></span>\n";

                for ($i = 0; $i < $_size - 1; $i++) {
                    echo "              <span></span>";
                }
                echo "      </nav>";
            }
        }
    }

    public function openSliderWrapper()
    {
        echo "<div class=\"sl-slider-wrapper\" id=\"slider\">\n";
        echo "   <div class=\"sl-slider\">\n";
    }

    public function closeSliderWrapper()
    {
        echo "   </div>\n";
        echo "</div>\n";
    }

}

?>