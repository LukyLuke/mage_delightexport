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
 * Grid-Field-Renderer for Actions
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @author     delight software gmbh <info@delightsoftware.com>
 */
class Delight_Delightexport_Block_Files_Grid_Renderer_Actions extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {
	public function render(Varien_Object $row) {
		$this->getColumn()->setActions(array(array(
			'url'     => $this->getUrl('*/*/edit', array('file_id' => $row->getEntityId())),
			'caption' => Mage::helper('delightexport')->__('Edit'),
		), array(
			'url'     => $this->getUrl('*/*/delete', array('file_id' => $row->getEntityId())),
			'confirm' => Mage::helper('delightexport')->__('Do you really want delete this Export-File'),
			'caption' => Mage::helper('delightexport')->__('Delete'),
		), array(
			'url'     => $this->getUrl('*/*/generate', array('file_id' => $row->getEntityId())),
			'confirm' => Mage::helper('delightexport')->__('Do you really want to generate this Export-File'),
			'caption' => Mage::helper('delightexport')->__('Generate'),
		)));

		// Show DropDown
		if ($this->getColumn()->getNoLink()) {
			return parent::render($row);
		}

		// Show Links
		$out = '';
		$actions = $this->getColumn()->getActions();
		foreach ($actions as $action) {
			if (is_array($action)) {
				if (!empty($out)) {
					$out .= '<br/>';
				}
				$out .= $this->_toLinkHtml($action, $row);
			}
		}
		return $out;
	}
}