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
 * Form-Widget to generate a file
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @author     delight software gmbh <info@delightsoftware.com>
 */
class Delight_Delightexport_Block_Files_Generate_Form extends Mage_Adminhtml_Block_Widget_Form {

	protected function _prepareForm() {
		$form = new Varien_Data_Form();

		$fileModel = $this->getFileModel();

		// Show Special Renderer for a Process-Bar
		$fieldset = $form->addFieldset('process', array('legend' => $this->__('Current progress')));
		$fieldset->addField('progress', 'text', array(
			'label' => $this->__('Progress'),
			'name' => 'progress',
			'value' => 0
		));
		$form->getElement('progress')->setRenderer(
			$this->getLayout()->createBlock('delightexport/files_generate_renderer_Process')
		);

		//$form->addValues($fileModel->getData());
		//$form->setUseContainer(true);
		$form->setId('progress_form');
		$form->setMethod('post');
		$form->setAction($this->getSaveUrl());
		$this->setForm($form);
	}

	public function getFileModel() {
		return Mage::registry('current_file');
	}

	public function getSaveUrl() {
		return $this->getUrl('*/*/save', array('file_id' => $this->getFileModel()->getEntityId()));
	}
}