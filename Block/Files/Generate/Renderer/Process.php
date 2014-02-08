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
 * Renderer to show the Create-Process
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @author     delight software gmbh <info@delightsoftware.com>
 */
class Delight_Delightexport_Block_Files_Generate_Renderer_Process extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{
	public function render(Varien_Data_Form_Element_Abstract $element) {
		$this->setElement($element);
		return $this->toHtml();
	}

	protected function _toHtml() {
		$html  = '<div id="delightexport_progress_bar_container" style="text-align:center;border:1px inset #6F8992;position:relative;">';
		$html .= '<div id="delightexport_progress_bar" style="background:#EB5E00;display:block;width:0%;">&nbsp;</div>';
		$html .= '<span id="delightexport_progress_bar_state" style="margin:0 auto;position:absolute;top:0;color:black;font-weight:bold;">0%</span>';
		$html .= '</div>';
		return $html;
	}

}