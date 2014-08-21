<?php

class Etailers_Popup_Model_Mysql4_Popup_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	/**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;


    /**
     * Define resource model
     *
     */
    protected function _construct()
    {
        $this->_init('popup/popup');
        $this->_map['fields']['popup_id'] = 'main_table.popup_id';
        $this->_map['fields']['store']   = 'store_table.store_id';
    }

    
    public function toOptionArray()
    {
        return $this->_toOptionArray('popup_id', 'popup_title');
    }
	

    /**
     * Set first store flag
     *
     * @param bool $flag
     * @return Etailers_Contest_Model_Resource_Contest_Collection
     */
    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return Etailers_Contest_Model_Resource_Contest_Collection
     */
    protected function _afterLoad()
    {
        if ($this->_previewFlag) {
            $items = $this->getColumnValues('popup_id');
            $connection = $this->getConnection();
            if (count($items)) {
                $select = $connection->select()
                        ->from(array('cps'=>$this->getTable('popup/popup_store')))
                        ->where('cps.popup_id IN (?)', $items);

                if ($result = $connection->fetchPairs($select)) {
                    foreach ($this as $item) {
                        if (!isset($result[$item->getData('popup_id')])) {
                            continue;
                        }
                        if ($result[$item->getData('popup_id')] == 0) {
                            $stores = Mage::app()->getStores(false, true);
                            $storeId = current($stores)->getId();
                            $storeCode = key($stores);
                        } else {
                            $storeId = $result[$item->getData('popup_id')];
                            $storeCode = Mage::app()->getStore($storeId)->getCode();
                        }
                        $item->setData('_first_store_id', $storeId);
                        $item->setData('store_code', $storeCode);                        
                    }
                }
            }
        }

        return parent::_afterLoad();
    }

    /**
     * Add filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @param bool $withAdmin
     * @return Etailers_Contest_Model_Resource_Contest_Collection
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            if ($store instanceof Mage_Core_Model_Store) {
                $store = array($store->getId());
            }

            if (!is_array($store)) {
                $store = array($store);
            }

            if ($withAdmin) {
                $store[] = Mage_Core_Model_App::ADMIN_STORE_ID;
            }

            $this->addFilter('store', array('in' => $store), 'public');
        }
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                array('store_table' => $this->getTable('popup/popup_store')),
                'main_table.popup_id = store_table.popup_id',
                array()
            )->group('main_table.popup_id');

            /*
             * Allow analytic functions usage because of one field grouping
             */
            $this->_useAnalyticFunction = true;
        }
        return parent::_renderFiltersBefore();
    }


    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();

        $countSelect->reset(Zend_Db_Select::GROUP);

        return $countSelect;
    }
}
