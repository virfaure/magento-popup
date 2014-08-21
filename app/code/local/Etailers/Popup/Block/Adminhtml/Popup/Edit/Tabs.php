<?php

class Etailers_Popup_Block_Adminhtml_Popup_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('popup_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('popup')->__('Popup Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('popup')->__('Popup Information'),
          'title'     => Mage::helper('popup')->__('Popup Information'),
          'content'   => $this->getLayout()->createBlock('popup/adminhtml_popup_edit_tab_form')->toHtml(),
      ));
     
     $this->addTab('content_section', array(
          'label'     => Mage::helper('popup')->__('Popup Content'),
          'title'     => Mage::helper('popup')->__('Popup Content'),
          'content'   => $this->getLayout()->createBlock('popup/adminhtml_popup_edit_tab_content')->toHtml(),
      ));
     
     
      return parent::_beforeToHtml();
  }
}
