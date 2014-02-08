<?php
/**
 * DelightSerial Customisation by delight software gmbh for Magento
 *
 * DISCLAIMER
 *
 * Do not edit or add code to this file if you wish to upgrade this Module to newer
 * versions in the future.
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @copyright  Copyright (c) 2001-2011 delight software gmbh (http://www.delightsoftware.com/)
 */

/**
 * Grid shows all exported files
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @author     delight software gmbh <info@delightsoftware.com>
 */
class Delight_Delightexport_Block_Files_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('files_grid');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection() {
		$collection = Mage::getResourceModel('delightexport/files_collection');
		$this->setCollection($collection);
		parent::_prepareCollection();
		return $this;
	}

	protected function _prepareColumns() {
		$this->addColumn('file_name', array(
			'header' => $this->__('Filename'),
			'width' => '300px',
			'index' => 'file_name'
		));
		$this->addColumn('file_description', array(
			'header' => $this->__('Description'),
			'index' => 'file_description'
		));
		$this->addColumn('file_type', array(
			'header' => $this->__('Filetype'),
			'width' => '80px',
			'index' => 'file_type',
			'type' => 'options',
			'options' => Mage::helper('delightexport')->getGridFileTypes()
		));
		$this->addColumn('store_ids', array(
			'header' => $this->__('Websites'),
			'width' => '150px',
			'index' => 'store_ids',
			'sortable' => false,
			'filter' => false,
			'renderer' => 'delightexport/files_grid_renderer_stores'
		));
		$this->addColumn('action', array(
			'header' => $this->__('Actions'),
			'width' => '100px',
			'filter' => false,
			'sortable' => false,
			'no_link' => false,
			'renderer' => 'delightexport/files_grid_renderer_actions'
		));
		return parent::_prepareColumns();
	}

	public function getRowUrl($row) {
		return $this->getUrl('*/*/edit', array('file_id' => $row->getId()));
	}

	public function getGridUrl() {
		return $this->getUrl('*/*/grid', array('_current' => true));
	}

}
?>