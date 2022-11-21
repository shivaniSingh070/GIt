<?php

/**
 * @author: AA
 * @date: 21.08.2019
 * @description: Swatch value for configurable product
 */

namespace Pixelmechanics\ConfigurableSwatcher\Plugin\Swatches\Block\Product\Renderer;
 
class Configurable
{
    protected $helperData;
    public function __construct(
   
        \Pixelmechanics\Engelsrufer\Helper\Catalog $helperData
 
    ) {
        $this->helperData = $helperData;

    }
    public function afterGetJsonConfig(\Magento\Swatches\Block\Product\Renderer\Configurable $subject, $result) {
 
        $jsonResult = json_decode($result, true);
        $jsonResult['skus'] = [];
        $jsonResult['details'] = [];
        $jsonResult['bulletPoint1'] = [];
        $jsonResult['bulletPoint2'] = [];
        $jsonResult['bulletPoint3'] = [];
        $jsonResult['bulletPoint4'] = [];
        $jsonResult['bulletPoint5'] = [];
        $jsonResult['productType'] = [];
        $jsonResult['stoneType'] = [];
        $jsonResult['goldlegierung'] = [];
        $jsonResult['materialLegierung'] = [];
        $jsonResult['size'] = [];
        $jsonResult['color'] = [];
        $jsonResult['qualityDetailsLabel'] = [];
        $jsonResult['qualityDetails'] = [];
        $jsonResult['klangfarbeLabel'] = [];
        $jsonResult['klangfarbe'] = [];
        $jsonResult['verpackungLabel'] = [];
        $jsonResult['verpackung'] = [];
        $jsonResult['engelsruferKonzeptLabel'] = [];
        $jsonResult['engelsruferKonzeptValue'] = [];
        $jsonResult['verpackungImage'] = [];
        $jsonResult['engelsruferKonzeptImage'] = [];
/** added extra 5 more attribute function to show more extra attributes on the PDP by NA 30.09.19
* trello:https://trello.com/c/FUegOmvv/143-convert-csv-file-and-import-products-from-csv-file-into-relaunch-shop#comment-5d8c7cc8bf20101bda04929c
*/ 
        $jsonResult['funktion'] = [];
        $jsonResult['durchmesser_hoehe'] = [];
        $jsonResult['thickness'] = [];
        $jsonResult['verschlusstypen'] = [];
        $jsonResult['lange_cm'] = [];
        foreach ($subject->getAllowProducts() as $simpleProduct) {
           $product =  $this->helperData->getProductById($simpleProduct->getId());
           $jsonResult['skus'][$simpleProduct->getId()] = $simpleProduct->getSku();
           $jsonResult['details'][$simpleProduct->getId()] = $product->getDescription();
           $jsonResult['bulletPoint1'][$simpleProduct->getId()] = $product->getBulletPoint1();
           $jsonResult['bulletPoint2'][$simpleProduct->getId()] = $product->getBulletPoint2();
           $jsonResult['bulletPoint3'][$simpleProduct->getId()] = $product->getBulletPoint3();
           $jsonResult['bulletPoint4'][$simpleProduct->getId()] = $product->getBulletPoint4();
           $jsonResult['bulletPoint5'][$simpleProduct->getId()] = $product->getBulletPoint5();
           $jsonResult['productType'][$simpleProduct->getId()] = $product->getAttributeText('produktuntertyp');
           $jsonResult['stoneType'][$simpleProduct->getId()] = $product->getSteinart();
           $jsonResult['funktion'][$simpleProduct->getId()] = $product->getFunktion();
           $jsonResult['durchmesser_hoehe'][$simpleProduct->getId()] = $product->getDurchmesserHoehe();
           $jsonResult['thickness'][$simpleProduct->getId()] = $product->getThickness();
           $jsonResult['verschlusstypen'][$simpleProduct->getId()] = $product->getVerschlusstypen();
           $jsonResult['lange_cm'][$simpleProduct->getId()] = $product->getLangeCm();
           $jsonResult['goldlegierung'][$simpleProduct->getId()] = $product->getAttributeText('gold_legierung');
           $jsonResult['materialLegierung'][$simpleProduct->getId()] = $product->getMaterialLegierung();
           $jsonResult['size'][$simpleProduct->getId()] = $product->getAttributeText('size');
           $jsonResult['color'][$simpleProduct->getId()] = $product->getAttributeText('color');
           $jsonResult['qualityDetailsLabel'][$simpleProduct->getId()] = $product->getQualityDetailsLabel();
              $attributeQualityValue = $product->getQualityDetails();
           $jsonResult['qualityDetails'][$simpleProduct->getId()] = $this->helperData->getWysiwygAttributeValue($attributeQualityValue);
           $jsonResult['klangfarbeLabel'][$simpleProduct->getId()] = $product->getKlangfarbeLabel();
               $attributeklangfarbeValue = $product->getKlangfarbe();
            $jsonResult['klangfarbe'][$simpleProduct->getId()] = $this->helperData->getWysiwygAttributeValue($attributeklangfarbeValue);
            $jsonResult['verpackungLabel'][$simpleProduct->getId()] = $product->getVerpackungLabel();
               $attributeVerpackungValue = $product->getVerpackung();
            $jsonResult['verpackung'][$simpleProduct->getId()] = $this->helperData->getWysiwygAttributeValue($attributeVerpackungValue);
            $jsonResult['engelsruferKonzeptLabel'][$simpleProduct->getId()] = $product->getEngelsruferKonzeptLabel();
               $attributeEngelsruferKonzeptValue = $product->getEngelsruferKonzept();
            $jsonResult['engelsruferKonzeptValue'][$simpleProduct->getId()] = $this->helperData->getWysiwygAttributeValue($attributeEngelsruferKonzeptValue);
            $jsonResult['verpackungImage'][$simpleProduct->getId()] = $this->helperData->getPubMediaUrl().'catalog/product'.$product->getVerpackungImage();
            $jsonResult['engelsruferKonzeptImage'][$simpleProduct->getId()] = $this->helperData->getPubMediaUrl().'catalog/product'.$product->getEngelsruferKonzeptImage();
        }
        $result = json_encode($jsonResult);
        return $result;
	}
}