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
 * Grid-Field-Renderer for Stores a File is in
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @author     delight software gmbh <info@delightsoftware.com>
 */
class Delight_Delightexport_Block_Files_Grid_Renderer_Stores extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	public function render(Varien_Object $row) {
		$selected = explode(',', $row->getStoreIds());
		$stores = Mage::helper('delightexport')->getWebsites();
		$html = '';
		foreach($stores as $store) {
			if (in_array($store['value'], $selected)) {
				$html .= (empty($html) ? '' : '<br/>').$store['title'];
			}
		}
		return $html;
	}
}