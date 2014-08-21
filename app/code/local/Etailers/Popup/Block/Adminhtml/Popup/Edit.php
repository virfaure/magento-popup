<?php

class Etailers_Popup_Block_Adminhtml_Popup_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'popup';
        $this->_controller = 'adminhtml_popup';
        
        $this->_updateButton('save', 'label', Mage::helper('popup')->__('Save Popup'));
        $this->_updateButton('delete', 'label', Mage::helper('popup')->__('Delete Popup'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
        
       
        $id = $this->getRequest()->getParam('id');
        if($id){
            $this->_addButton('previewpopup', array(
                'label'     => Mage::helper('adminhtml')->__('Preview Popup'),
                'onclick'   => "previewPopup($id)"
            ), -100);
        }
        
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('popup_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'popup_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'popup_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            function previewPopupOld(){
                winCompare = new Window({className:'magento',title:'Preview Popup',width:700,height:400,minimizable:false,maximizable:false,showEffectOptions:{duration:0.4},hideEffectOptions:{duration:0.4}});
				winCompare.setZIndex(100);
				winCompare.setAjaxContent('".Mage::helper("adminhtml")->getUrl("popup/adminhtml_popup/preview/")."', {method: 'post', parameters: $('edit_form').serialize(true) }, true, true);
            }
            
            function previewPopup(popupID){
               // Save Content + Image and open Homepage of one of selected store
               var params = [
                    'height='+screen.height,
                    'width='+screen.width,
                    'fullscreen=yes' // only works in IE, but here for completeness
                ].join(',');
                
               new Ajax.Request('".Mage::helper("adminhtml")->getUrl("popup/adminhtml_popup/preview/")."?id='+popupID, 
				{
					method:'post', 
					asynchronous:true, 
					evalScripts:false, 
					onComplete:function(request, json){
						// open new Window => homepage + store_id
						response = eval('(' + request.responseText + ')');
						if(response.state == 'success'){
							window.open(response.url, 'new_window', params);
						}
					}, 
					parameters:Form.serialize($('edit_form'))
			});
               
            }
            
            function addNewsletter(checked){
				if(checked) $('popup_content_html').value = $('popup_content_html').value + \"<div class='popup-newsletter'>{{block type='popup/popup' area='frontend' template='etailers/popup/newsletter-subscribe.phtml'}}</div>\";
			}
            
            function activeEmailTemplate(checked){
				if(checked) $('popup_email_template').disabled = false;
                else $('popup_email_template').disabled = true;
			}
                       
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('popup_data') && Mage::registry('popup_data')->getId() ) {
            return Mage::helper('popup')->__("Edit Popup '%s'", $this->htmlEscape(Mage::registry('popup_data')->getPopupTitle()));
        } else {
            return Mage::helper('popup')->__('Add Popup');
        }
    }
}
