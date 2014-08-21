<?php
class Etailers_Popup_Block_Popup extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getPopup()     
     { 
        if (!$this->hasData('popup')) {
            $this->setData('popup', Mage::registry('popup'));
        }
        return $this->getData('popup');
        
    }
    
    public function getPopupActive()
	{
		
            $preview = $this->getRequest()->getParam('popup');
            $popupID = $this->getRequest()->getParam('popupid');
            if($preview == "preview" && !empty($popupID)){
                 $arrPopup = Mage::getModel('popup/popup')->getPopupPreview($popupID);
            }else{
                $arrPopup = Mage::getModel('popup/popup')->getPopupActive();
            }
            
		
		if(!empty($arrPopup)){
			
			// If there is a link, process template tag
			if(!empty($arrPopup["popup_url"])){
				$helper = Mage::helper('cms');
				$processor = $helper->getPageTemplateProcessor();
				$arrPopup["popup_url"] = $processor->filter($arrPopup["popup_url"]);
			}
			
			// If there is a content, process template tag
			if(!empty($arrPopup["popup_content_html"])){
				$helper = Mage::helper('cms');
				$processor = $helper->getPageTemplateProcessor();
				$arrPopup["popup_content_html"] = $processor->filter($arrPopup["popup_content_html"]);
			}

			$popupID = $arrPopup["popup_id"];

			$storeID = Mage::app()->getStore()->getId();

			//Check Cookie 
			$cookiePopup = Mage::getSingleton('core/cookie')->get('cookie_popup_'.$storeID);

			// If there's any cookie or new promo or preview mode
			if (!$cookiePopup || $cookiePopup != $popupID || $this->getRequest()->getParam('popup') == 'preview'){
				
				if(!$this->getRequest()->getParam('popup')) Mage::getSingleton('core/cookie')->set('cookie_popup_'.$storeID, $popupID, (3600 * 24 * 7));
				if(!empty($arrPopup["popup_image"])){
					$pathImage = Mage::getBaseDir('media')."/popup/".$arrPopup["popup_image"];
					if (file_exists($pathImage)){
						$imageObj = new Varien_Image($pathImage);
						$arrPopup['popup_image_width'] = $imageObj->getOriginalWidth();
						$arrPopup['popup_image_height'] = $imageObj->getOriginalHeight();		
					}
				}
				
				return $arrPopup;
			}
		}else{
			return false;
		}
	}
	
	public function getFormActionUrl()
    {
        return $this->getUrl('popup/index/ajaxsubscribe', array('_secure' => true));
    }
}
