<?php

class Etailers_Popup_Model_Status extends Varien_Object
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('popup')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('popup')->__('Disabled')
        );
    }
}