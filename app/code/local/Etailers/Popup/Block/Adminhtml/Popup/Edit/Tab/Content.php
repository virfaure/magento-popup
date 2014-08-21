<?php

class Etailers_Popup_Block_Adminhtml_Popup_Edit_Tab_Content extends Mage_Adminhtml_Block_Widget_Form
{
 
	/**
	* Load Wysiwyg on demand and Prepare layout
	*/
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		}
	}
 
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('content_form', array('legend'=>Mage::helper('popup')->__('Popup Content')));
     
      /*$wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
            array('tab_id' => $this->getTabId())
      );*/

		$wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
           array(
				'tab_id' => $this->getTabId(),
				'add_widgets' => false, 
				'add_variables' => false, 
				'files_browser_window_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'), 
				'files_browser_window_width' => (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_width'), 
				'files_browser_window_height'=> (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_height')
		
			)
		); 



    $fieldset->addField('popup_image', 'image', array(
       'label'     => Mage::helper('popup')->__('Background Image'),
       'required'  => false,
       'name'      => 'popup_image',
       'path'      => 'popup/',
       'after_element_html' => '<small style="position: absolute;">'.Mage::helper('popup')->__('Allowed extensions : jpg, jpeg, gif, png').'</small>',
       ));
     
		
	 $fieldset->addField('popup_url', 'text', array(
		  'label'     => Mage::helper('popup')->__('Popup Link Url'),
		  'name'      => 'popup_url',
		  'style'     => 'width:700px;',
		  'after_element_html' => '<small>'.Mage::helper('popup')->__('Example').': {{store url=\'customer/account/create\'}}</small>',
		   
	  ));
		 
	  $fieldset->addField('popup_newsletter', 'checkbox', array(
          'label'     => Mage::helper('popup')->__('Newsletter Subscription'),
          'name'      => 'popup_newsletter',
          'value' 	  => 1,
          'onclick'   => 'addNewsletter(this.checked);activeEmailTemplate(this.checked);'
      ));
      
      $fieldset->addField('popup_email_template', 'select', array(
          'label'     => Mage::helper('popup')->__('Template Email'),
          'name'      => 'popup_email_template',
          'values'    => Mage::getModel('adminhtml/system_config_source_email_template')->toOptionArray(), 
      ));
       
      $fieldset->addField('popup_content_html', 'editor', array(
          'name'      => 'popup_content_html',
          'label'     => Mage::helper('popup')->__('Content'),
          'title'     => Mage::helper('popup')->__('Content'),
          'style'     => 'width:700px; height:200px;',
          'config'    => $wysiwygConfig,
      ));

     
      if ( Mage::getSingleton('adminhtml/session')->getPopupData() )
      {   $popupData = Mage::getSingleton('adminhtml/session')->getPopupData();
          $form->setValues($popupData);
          $form->getElement('popup_newsletter')->setIsChecked($popupData["popup_newsletter"]);
          Mage::getSingleton('adminhtml/session')->setPopupData(null);
      } elseif ( Mage::registry('popup_data') ) {
          $popupData = Mage::registry('popup_data')->getData();
          $form->getElement('popup_newsletter')->setIsChecked($popupData["popup_newsletter"]);
          $form->setValues($popupData);
      }
      return parent::_prepareForm();
  }
}
