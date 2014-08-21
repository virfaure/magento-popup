<?php
class Etailers_Popup_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    		
    }
    
    protected function _initPopupActive()
	{
		$popup = Mage::register('popup', "PP");
		return $popup;
	}    
	
	protected function ajaxsubscribeAction()
	{
		
		$dataPost = $this->getRequest()->getPost(); // popup_email, popup_accept
		$result = array();
		
        if ($dataPost) {

            try {
                $postObject = new Varien_Object();
                $postObject->setData($dataPost);

                $error = false;

                if (!Zend_Validate::is(trim($dataPost['popup_email']), 'EmailAddress')) {
                    $error = true;
                }

                if ($dataPost['popup_accept'] != 1) {
                    $error = true;
                }

                if ($error) {
                    $result['state'] = "error";
                    $result['message'] = Mage::helper('newsletter')->__('Unable to submit your request. Please, try again later');
                }
                
                $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($dataPost['popup_email']); 
				if(!$subscriber->getId()){
					//Newsletter subscribe
                    Mage::register('popup_id', $dataPost['popup_popup_id']);
					$status = Mage::getModel('newsletter/subscriber')->subscribe($dataPost['popup_email']);
					if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
						$message = Mage::helper('popup')->__('Confirmation request has been sent.');
					}
					else {
						$message = Mage::helper('popup')->__('Thank you for your subscription.');
					}
					$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($dataPost['popup_email']);
				}else{
					$message = Mage::helper('popup')->__('You are already subscribed to our newsletter. Thank you !');
				}
                    
				// Save Last Popup Id in newsletter_popup_subcriber
				Mage::getModel('popup/popup')->saveLastPopupId($subscriber->getId(), $dataPost['popup_popup_id']);

				$result['state'] = "success";
                $result['message'] = $message;
                
                Mage::getSingleton('core/session')->getMessages(true);
                Mage::getSingleton('core/session')->addSuccess($message);
                
                $refererUrl = $this->_getRefererUrl();
				if (empty($refererUrl)) {
					$refererUrl = empty($defaultUrl) ? Mage::getBaseUrl() : $defaultUrl;
				}

                $result['redirect'] = $refererUrl;
                
            } catch (Exception $e) {
                $message = Mage::helper('newsletter')->__('Unable to submit your request. Please, try again later');
                $result['state'] = "error";
                $result['message'] = $message;
            }

        } else {
			$result['state'] = "error";
			$result['message'] = $message = Mage::helper('newsletter')->__('Unable to submit your request. Please, try again later');;
        }
                
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
}
