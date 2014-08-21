<?php
class Etailers_Popup_Block_Adminhtml_Popup extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_popup';
    $this->_blockGroup = 'popup';
    $this->_headerText = Mage::helper('popup')->__('Popup Manager');
    $this->_addButtonLabel = Mage::helper('popup')->__('Add Popup');
    parent::__construct();
  }
}
