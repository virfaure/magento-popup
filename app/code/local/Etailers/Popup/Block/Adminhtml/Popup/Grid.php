<?php

class Etailers_Popup_Block_Adminhtml_Popup_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('popupGrid');
      $this->setDefaultSort('popup_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('popup/popup')->getCollection();
      /* @var $collection Mage_Cms_Model_Mysql4_Page_Collection */
      $collection->setFirstStoreFlag(true);
      $this->setCollection($collection);

      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('popup_id', array(
          'header'    => Mage::helper('popup')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'popup_id',
      ));

      $this->addColumn('popup_title', array(
          'header'    => Mage::helper('popup')->__('Title'),
          'align'     =>'left',
          'index'     => 'popup_title',
      ));

	  $this->addColumn('popup_date_start', array(
          'header'    => Mage::helper('popup')->__('Start Date'),
          'align'     =>'left',
          'type'      => 'date',
          'index'     => 'popup_date_start',
      ));
      
      
      $this->addColumn('popup_date_end', array(
          'header'    => Mage::helper('popup')->__('End Date'),
          'align'     =>'left',
          'type'      => 'date',
          'index'     => 'popup_date_end',
      ));

	/**
	* Check is single store mode
	*/
	if (!Mage::app()->isSingleStoreMode()) {
		$this->addColumn('store_id', array(
			'header'        => Mage::helper('popup')->__('Store View'),
			'index'         => 'store_id',
			'type'          => 'store',
			'store_all'     => true,
			'store_view'    => true,
			'sortable'      => false,
			'filter_condition_callback'
							=> array($this, '_filterStoreCondition'),
		));
	}
        
      $this->addColumn('status', array(
          'header'    => Mage::helper('popup')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => Mage::getModel("popup/status")->getOptionArray(),
      ));
      
      $this->addColumn('popup_coupon', array(
          'header'    => Mage::helper('popup')->__('Coupon'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'popup_coupon',
          'type'      => 'options',
          'options'   => array(
			0 => '--',
			1 => 'Si'
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('popup')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('popup')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('popup')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('popup')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('popup_id');
        $this->getMassactionBlock()->setFormFieldName('popup');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('popup')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('popup')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('popup/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('popup')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('popup')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

	protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }
    
  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
