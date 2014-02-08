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
 * Formular to edit an Exportfile
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @author     delight software gmbh <info@delightsoftware.com>
 */
class Delight_Delightexport_Block_Files_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

	protected function _prepareForm() {
		$form = new Varien_Data_Form();

		$fileModel = $this->getFileModel();

		$fieldset = $form->addFieldset('base_fieldset', array('legend' => $this->__('File-Configuration')));

		// Filename and FileID
		$fieldset->addField('file_id', 'hidden', array(
			'name' => 'file_id',
			'value' => $fileModel->getEntityId()
		));
		$fieldset->addField('file_name', 'text', array(
			'label' => $this->__('Filename'),
			'title' => $this->__('Filename'),
			'name' => 'file_name',
			'required' => true,
			'value' => $fileModel->getFileName()
		));

		// File-Types as a DropDown
		$options = Mage::helper('delightexport')->getFileTypes();
		$fieldset->addField('file_type', 'select', array(
			'label' => $this->__('File Type'),
			'name' => 'file_type',
			'values' => $options,
			'value' => $fileModel->getFileType(),
			'required' => true
		));

		// Websites
		$options = Mage::helper('delightexport')->getWebsites();
		$fieldset->addField('store_ids', 'multiselect', array(
			'label' => $this->__('Store'),
			'name' => 'store_ids',
			'values' => $options,
			'value' => $fileModel->getStoreId(),
			'required' => true
		));

		// Description
		$fieldset->addField('file_description', 'textarea', array(
			'label' => $this->__('File Description'),
			'name' => 'file_description',
			'value' => $fileModel->getFileDescription()
		));

		// Show special Fieldset for Attributes
		$attributes = $fileModel->getAttributesCollection();
		$fieldset = $form->addFieldset('attributes_fieldset', array('legend' => $this->__('Attributes-Configuration')));
		$fieldset->addField('attributes', 'text', array(
			'name' => 'attributes',
			'value' => $attributes
		));
		$form->getElement('attributes')->setRenderer(
			$this->getLayout()->createBlock('delightexport/files_edit_renderer_attributes')
		);

		$form->addValues($fileModel->getData());
		$form->setUseContainer(true);
		$form->setId('edit_form');
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