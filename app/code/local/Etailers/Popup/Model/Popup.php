<?php

class Etailers_Popup_Model_Popup extends Mage_Core_Model_Abstract
{
	/**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'etailers_popup';
    
    public function _construct()
    {
        //parent::_construct();
        $this->_init('popup/popup');
    }
    
    /**
     * Get Active Popup
     *
     */
    public function getPopupActive()
    {
        return $this->getResource()->getPopupActive();
    }
    
    /**
     * Get Active Popup
     *
     */
    public function getPopupPreview($popupId)
    {
        return $this->getResource()->getPopupPreview($popupId);
    }
    
    /**
     * Save Popup ID with Subscriber ID
     *
     */
    public function saveLastPopupId($subscriberId, $popupID){
        return $this->getResource()->saveLastPopupId($subscriberId, $popupID);
    }
}
