<?php

class Etailers_Popup_Adminhtml_PopupController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('popup/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('popup/popup')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('popup_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('popup/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Popup Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Popup News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
				$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            }

			$this->_addContent($this->getLayout()->createBlock('popup/adminhtml_popup_edit'))
				->_addLeft($this->getLayout()->createBlock('popup/adminhtml_popup_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('popup')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			
			if(isset($data["popup_coupon"])){
				$data["popup_coupon"] = 1;                            
			}else{
                $data["popup_coupon"] = 0;       
            }
			
			if(isset($data["popup_newsletter"])){
				$data["popup_newsletter"] = 1;                            
			}else{
                 $data["popup_newsletter"] = 0;       
            }
			
			if(isset($_FILES['popup_image']['name']) && $_FILES['popup_image']['name'] != '') {
				try {	
					// Starting upload 	
					$uploader = new Varien_File_Uploader('popup_image');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS . "/popup/" . DS;
					$uploader->save($path, $_FILES['popup_image']['name']);
					
					$imageUrl = $path.$_FILES['popup_image']['name'];
					if (file_exists($imageUrl)){
                        $imageObj = new Varien_Image($imageUrl);
                        $imageObj->constrainOnly(TRUE);
                        $imageObj->keepAspectRatio(TRUE);
                        $imageObj->keepTransparency(TRUE);
                        $imageObj->keepFrame(FALSE);
                        $imageObj->resize(600);
                        $imageObj->save($imageUrl);
					}
					
				} catch (Exception $e) {
					 $this->_getSession()->addError($e->getMessage());
		        }
	        
		        //this way the name is saved in DB
	  			$data['popup_image'] = $_FILES['popup_image']['name'];
			}else
			{
				if(isset($data['popup_image']['delete']) && $data['popup_image']['delete'] == 1) {
					 $data['popup_image'] = '';
				} else {
					unset($data['popup_image']);
				}
			}
			
	  		// Format Date 
	  		$data = $this->_filterDates($data, array('popup_date_start', 'popup_date_end'));
	  			        
			$model = Mage::getModel('popup/popup');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('popup')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('popup')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
	
	public function previewAction() {
		//return Store Code with url to Open
		$result = array();
                
                $id  = $this->getRequest()->getParam('id');
		
		if ($data = $this->getRequest()->getPost()) {
			
                    if(!empty($data["stores"])){
                        $arrStores = explode(",", $data["stores"][0]);
                    }
                        /*
			foreach($arrStores as $key => $store){
				if($store != 0) {
                                    $storeId = $store;
                                    break;
				}
			}
			*/
                        //$result['url'] = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);            
            
                    $storeId = $arrStores[0];
                    $storeCode = Mage::app()->getStore($storeId)->getCode();
                    $result['state'] = "success";	
                    $result['url'] = Mage::getStoreConfig('web/unsecure/base_url')."?___store=".$storeCode."&popup=preview&popupid=".$id;
		}else{
			//No Data do nothing
			$result['state'] = "error";
		}
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
	
	/*
	public function previewOldAction() {
        
		if ($data = $this->getRequest()->getPost()) {
			//print_r($data);
			
			try {
				// If there is a link, process template tag
				if(!empty($data["popup_url"])){
					$helper = Mage::helper('cms');
					$processor = $helper->getPageTemplateProcessor();
					$data["popup_url"] = $processor->filter($data["popup_url"]);
				}
				
				// If there is a content, process template tag
				if(!empty($data["popup_content_html"])){
					$helper = Mage::helper('cms');
					$processor = $helper->getPageTemplateProcessor();
					$data["popup_content_html"] = $processor->filter($data["popup_content_html"]);
				}
				
				if(!empty($data["popup_image"]["value"])){
					$data["popup_image"] = $data["popup_image"]["value"];
					$pathImage = Mage::getBaseDir('media')."/popup/".$data["popup_image"];
					if (file_exists($pathImage)){
						$imageObj = new Varien_Image($pathImage);
						$data['popup_image_width'] = $imageObj->getOriginalWidth();
						$data['popup_image_height'] = $imageObj->getOriginalHeight();		
					}
				}
				
				//print_r($data);
				
				$background = "";
				$width = "";
				$image = "";
				if($data['popup_image']){
					$background = 'background:url('.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."popup/".$data['popup_image'].') top left no-repeat;';
					$image = '<img src='.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$data['popup_image'].' alt="" />';
					if($data['popup_image_width']) $width = 'width:'.$data['popup_image_width'].'px; height:'.$data['popup_image_height'].'px;';
				} 
		
				$html = '<div>
							<div id="divPopup" style="'.$background.' '.$width.'">';
								if($data['popup_content_html']) $html .=  $data['popup_content_html'];
				$html .= '	</div>
						</div>';
				
				echo $html;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                return;
            }
        }
	}
	*/
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('popup/popup');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $popupIds = $this->getRequest()->getParam('popup');
        if(!is_array($popupIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($popupIds as $popupId) {
                    $popup = Mage::getModel('popup/popup')->load($popupId);
                    $popup->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($popupIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $popupIds = $this->getRequest()->getParam('popup');
        if(!is_array($popupIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($popupIds as $popupId) {
                    $popup = Mage::getSingleton('popup/popup')
                        ->load($popupId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($popupIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'popup.csv';
        $content    = $this->getLayout()->createBlock('popup/adminhtml_popup_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'popup.xml';
        $content    = $this->getLayout()->createBlock('popup/adminhtml_popup_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }
    
	protected function _saveImage()
    {
		if(isset($_FILES['popup_image']['name']) && $_FILES['popup_image']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('popup_image');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS . "/popup/" . DS;
					$uploader->save($path, $_FILES['popup_image']['name']);
					
					$imageUrl = $path.$_FILES['popup_image']['name'];
					if (file_exists($imageUrl)){
                        $imageObj = new Varien_Image($imageUrl);
                        $imageObj->constrainOnly(TRUE);
                        $imageObj->keepAspectRatio(TRUE);
                        $imageObj->keepFrame(FALSE);
                        $imageObj->resize(600);
                        $imageObj->save($imageUrl);
					}
					
				} catch (Exception $e) {
					 $this->_getSession()->addError($e->getMessage());
		        }
	        
		        //this way the name is saved in DB
	  			$data['popup_image'] = $_FILES['popup_image']['name'];
			}else
			{
				if(isset($data['popup_image']['delete']) && $data['popup_image']['delete'] == 1) {
					 $data['popup_image'] = '';
				} else {
					unset($data['popup_image']);
				}
			}
	}

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}
