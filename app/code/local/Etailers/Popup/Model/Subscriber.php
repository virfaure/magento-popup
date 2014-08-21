<?php

class Etailers_Popup_Model_Subscriber extends Mage_Newsletter_Model_Subscriber
{
	
    /**
     * Send Email Request Confirmation Newsletter
     *
     */
    public function sendConfirmationRequestEmail()
    {
        if ($this->getImportMode()) {
            return $this;
        }

        //If Popup has a specified template email
        $popupTemplate = null;
        $popupObj = Mage::getModel('popup/popup')->load(Mage::registry('popup_id'));
        if($popupObj->getId()){
            $popupTemplate = $popupObj->getPopupEmailTemplate();
        }
        
        if(!$popupTemplate){
            if(!Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_TEMPLATE) || !Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_IDENTITY))  {
                return $this;
            }
            $popupTemplate = Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_TEMPLATE);
        }
        
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $email = Mage::getModel('core/email_template');

        $email->sendTransactional(
            $popupTemplate,
            Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL_IDENTITY),
            $this->getEmail(),
            $this->getName(),
            array('subscriber'=>$this)
        );

        $translate->setTranslateInline(true);
        Mage::unregister('popup_id');
        
        return $this;
    }

    /**
     * Send Email Confirmation Newsletter
     *
     */
    public function sendConfirmationSuccessEmail()
    {
        if ($this->getImportMode()) {
            return $this;
        }

        //If Popup has a specified template email
        $popupTemplate = null;
        $popupId = Mage::registry('popup_id');
        $popupObj = Mage::getModel('popup/popup')->load(Mage::registry('popup_id'));
        if($popupObj->getId()){
            $popupTemplate = $popupObj->getPopupEmailTemplate();
        }
        
        if(!$popupTemplate){
            if(!Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE) || !Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY))  {
                return $this;
            }
            $popupTemplate = Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE);
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $email = Mage::getModel('core/email_template');

        $email->sendTransactional(
            $popupTemplate,
            Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY),
            $this->getEmail(),
            $this->getName(),
            array('subscriber'=>$this)
        );

        $translate->setTranslateInline(true);
        Mage::unregister('popup_id');
        
        return $this;
    }
    
}
