<?php

class Etailers_Popup_Model_Mysql4_Popup extends Mage_Core_Model_Mysql4_Abstract
{
	/**
     * Store model
     *
     * @var null|Mage_Core_Model_Store
     */
    protected $_store  = null;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('popup/popup', 'popup_id');
        $this->_read = $this->_getReadAdapter();
        $this->_write = $this->_getWriteAdapter();
    }

    /**
     * Process popup data before deleting
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Etailers_Popup_Model_Resource_Popup
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        
        // Popup STORE
        $condition = array(
            'popup_id = ?'     => (int) $object->getId(),
        );
        $this->_getWriteAdapter()->delete($this->getTable('popup/popup_store'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Process popup data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Etailers_Popup_Model_Resource_Popup
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        /*
         * For two attributes which represent timestamp data in DB
         * we should make converting such as:
         * If they are empty we need to convert them into DB
         * type NULL so in DB they will be empty and not some default value
         */
        
        // modify create / update dates
        if ($object->isObjectNew() && !$object->hasCreationTime()) {
            $object->setCreationTime(Mage::getSingleton('core/date')->gmtDate());
        }

        $object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * Assign popup to store views
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Etailers_Popup_Model_Resource_Popup
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table  = $this->getTable('popup/popup_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = array(
                'popup_id = ?'     => (int) $object->getId(),
                'store_id IN (?)' => $delete
            );

            $this->_getWriteAdapter()->delete($table, $where);
        }

        if ($insert) {
            $data = array();

            foreach ($insert as $storeId) {
                $data[] = array(
                    'popup_id'  => (int) $object->getId(),
                    'store_id' => (int) $storeId
                );
            }

            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    /**
     * Perform operations after object load
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Etailers_Popup_Model_Resource_Popup
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());

            $object->setData('store_id', $stores);

        }

        return parent::_afterLoad($object);
    }


    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param Etailers_Popup_Model_Popup $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID, (int)$object->getStoreId());
            $select->join(
                array('popup_store' => $this->getTable('popup/popup_store')),
                $this->getMainTable() . '.popup_id = popup_store.popup_id',
                array())
                ->where('popup_store.store_id IN (?)', $storeIds)
                ->order('popup_store.store_id DESC')
                ->limit(1);
        }

        return $select;
    }
    

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($popupId)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('popup/popup_store'), 'store_id')
            ->where('popup_id = ?',(int)$popupId);

        return $adapter->fetchCol($select);
    }

    /**
     * Set store model
     *
     * @param Mage_Core_Model_Store $store
     * @return Etailers_Popup_Model_Resource_Popup
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return Mage::app()->getStore($this->_store);
    }
    
    /**
     * Get Active Popup
     *
     */
    public function getPopupActive()
    {
       $storeID = Mage::app()->getStore()->getId();
       
       $select = $this->_read->select()
            ->from($this->getMainTable())
            ->join(array('ps' => 'popup_store'), 'ps.popup_id = popup.popup_id', array('store_id'))
            ->where('popup.status = ?', Etailers_Popup_Model_Status::STATUS_ENABLED)
            ->where('popup.popup_date_start <= ?', date("Y-m-d"))
            ->where('popup.popup_date_end >= ? OR popup.popup_date_end IS NULL', date("Y-m-d"))
            ->where('ps.store_id = ? OR ps.store_id = 0', Mage::app()->getStore()->getId())
            ->limit(1);
            
        $result = $this->_read->fetchAll($select, array('store_id'=>$storeID));

        if (empty($result)) {
            return array();
        }

        return $result[0];
    }
    
    /**
     * Get Preview Popup
     *
     */
    public function getPopupPreview($popupId)
    {
        $result = null;

        if(is_numeric($popupId)){
            $storeID = Mage::app()->getStore()->getId();

            $select = $this->_read->select()
                 ->from($this->getMainTable())
                 ->join(array('ps' => 'popup_store'), 'ps.popup_id = popup.popup_id', array('store_id'))
                 ->where('popup.popup_id = ?', $popupId)
                 ->where('ps.store_id = ? OR ps.store_id = 0', Mage::app()->getStore()->getId())
                 ->limit(1);

            $result = $this->_read->fetchAll($select, array('store_id'=>$storeID));
        }
        
        if (empty($result)) {
            return array();
        }

        return $result[0];
    }
    
    public function saveLastPopupId($subscriberID, $popupID)
    {
        $data['subscriber_id'] = $subscriberID;
        $data['last_popup_id'] = $popupID;
        
        try{
            $select = $this->_read->select()
                ->from("newsletter_popup_subcriber")
                ->where('subscriber_id=:subscriber_id');
            $result = $this->_read->fetchRow($select, array('subscriber_id'=>$subscriberID));

            if (!$result) {
                $this->_write->insert("newsletter_popup_subcriber", $data);       
            }else{
                $this->_write->update("newsletter_popup_subcriber", $data, array('subscriber_id = ?' => $subscriberID));       
            } 
            return true;
        }catch (Exception $e) {
            Mage::log($e->getMessage());
            return null;
        }
    }
}
