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
 * Renderer for File-Attributes
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @author     delight software gmbh <info@delightsoftware.com>
 */
class Delight_Delightexport_Block_Files_Edit_Renderer_Attributes extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{

	/**
	 * Define the template file
	 */
	public function __construct() {
		$this->setTemplate('delightexport/attributes/edit.phtml');
	}

	/**
	 * Retrieve the currently selected File
	 * @return Delight_Delightexport_Model_File
	 */
	public function getFile() {
		return Mage::registry('current_file');
	}

	/**
	 * Render HTML
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	public function render(Varien_Data_Form_Element_Abstract $element) {
		$this->setElement($element);
		return $this->toHtml();
	}

	/**
	 * Set form element instance
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return Delight_Delightexport_Block_Files_Edit_Renderer_Attributes
	 */
	public function setElement(Varien_Data_Form_Element_Abstract $element) {
		$this->_element = $element;
		return $this;
	}

	/**
	 * Retrieve form element instance
	 *
	 * @return Delight_Delightexport_Block_Files_Edit_Renderer_Attributes
	 */
	public function getElement() {
		return $this->_element;
	}

	/**
	 * Prepare Attribute values
	 *
	 * @return array
	 */
	public function getValues() {
		$values = array();
		$data   = $this->getElement()->getValue();

		foreach ($data as $row) {
			$values[] = array(
				'entity_id' => $row->getEntityId(),
				'file_id' => $row->getFileId(),
				'attribute' => $row->getAttribute(),
				'export_field' => $row->getExportField(),
				'format' => $row->getFormat(),
				'pre_text' => $row->getPreText(),
				'post_text' => $row->getPostText(),
				'list_separator' => $row->getListSeparator(),
				'config' => json_encode(unserialize($row->getConfig()))
			);
		}

		return $values;
	}

	/**
	 * Return a List for Template to show all Fieldtypes as a DropDown
	 * @return array
	 */
	public function getFieldTypesList() {
		return Mage::helper('delightexport')->getFieldTypes();
	}

	/**
	 * Return a List for Template to show all Productattributes as a DropDown
	 * @return array
	 */
	public function getProductAttributes() {
		return Mage::helper('delightexport')->getProductAttributes();
	}

	/**
	 * Prepare global layout
	 * Add "Add tier" button to layout
	 *
	 * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Tier
	 */
	protected function _prepareLayout() {
		$button = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setData(array(
				'label'     => Mage::helper('delightexport')->__('Add Attribute'),
				'onclick'   => 'return attributesControl.addItem()',
				'class'     => 'add'
			));
		$button->setName('add_attribute');

		$this->setChild('add_button', $button);
		return parent::_prepareLayout();
	}

	/**
	 * Retrieve Add Attribute Item button HTML
	 *
	 * @return string
	 */
	public function getAddButtonHtml() {
		return $this->getChildHtml('add_button');
	}
}