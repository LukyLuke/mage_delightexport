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
 * Edit an Exportfile
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @author     delight software gmbh <info@delightsoftware.com>
 */
class Delight_Delightexport_Block_Files_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

	public function __construct() {
		parent::__construct();
		$this->_blockGroup = 'delightexport';
		$this->_controller = 'files';
		$this->_mode = 'edit';

		$model = Mage::registry('current_file');
		//$this->_removeButton('reset');
		$this->_updateButton('save', 'label', $this->__('Save File'));
		$this->_updateButton('save', 'id', 'save_button');
		$this->_updateButton('delete', 'label', $this->__('Delete File'));
		if (!$model->getId()) {
			$this->_removeButton('delete');
		}
	}

	public function getHeaderText() {
		if (!is_null(Mage::registry('current_file')->getId())) {
			return $this->__('Edit File "%s"', $this->htmlEscape(Mage::registry('current_file')->getFileName()));
		} else {
			return $this->__('New File');
		}
	}

	public function getHeaderCssClass() {
		return 'icon-head head-customer-groups';
	}

}
