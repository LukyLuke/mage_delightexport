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
 * Model for handling Files
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @author     delight software gmbh <info@delightsoftware.com>
 */
class Delight_Delightexport_Model_Files extends Mage_Core_Model_Abstract
{
	const CSV_SEPARATOR = ',';
	private $_ioAdapter = null;

	/**
	 * (non-PHPdoc)
	 * @see lib/Varien/Varien_Object#_construct()
	 */
	protected function _construct() {
		$this->_init('delightexport/files');
	}

	/**
	 * Get all Attributes as a CollectionModel
	 * @return Object
	 */
	public function getAttributesCollection() {
		return Mage::getResourceModel('delightexport/config_collection')
			->addFieldToFilter('file_id', $this->getEntityId());
	}

	/**
	 * Get num of Products in eah Store this file creates Exports for
	 * @return array( StoreId=>NumProducts, ... )
	 */
	public function getNumProducts() {
		$stores = explode(',', $this->getStoreIds());
		$num = array();
		foreach ($stores as $store) {
			$num[$store] = Mage::getResourceModel('catalog/product_collection')->setStoreId($store)->count();
		}
		return $num;
	}

	/**
	 * Get all Products as a Collection
	 * @param int $store WebsiteID / StoreId
	 * @return Object
	 */
	public function getProductsCollection($store) {
		return Mage::getResourceModel('catalog/product_collection')->setStoreId($store);
	}

	/**
	 * Get the Temporary Filename or the final one
	 * @param boolean $realFile Return the final filename or not (if not, the temporary is returned)
	 * @param int $storeId WebsiteID/StoreID
	 * @return String
	 */
	public function getRealFileName($realFile, $storeId) {
		$name = preg_replace('/[^a-z0-9_.-]+/smi', '_', $this->getFileName());

		// Append the Website-Code to the Filename
		$code = $storeId;
		foreach (Mage::helper('delightexport')->getWebsites() as $w) {
			if ($w['value'] == $storeId) {
				$code = $w['code'];
				break;
			}
		}
		$name .= '_'.$code;

		// Append the FileExtension
		$extension = 'txt';
		foreach (Mage::helper('delightexport')->getFileTypes() as $type) {
			if ($type['value'] == $this->getFileType()) {
				$extension = $type['extension'];
			}
		}

		$names = Mage::helper('delightexport')->getAbsoluteFileNames($name.'.'.$extension);
		return $names[$realFile ? 'real' : 'tmp'];
	}

	/**
	 * Initialize the loaded File, write some HeaderData to it or anything else
	 * @param int $storeId WebsiteID/StoreID
	 * @return none
	 */
	public function initFile($storeId) {
		switch ($this->getFileType()) {
			case Delight_Delightexport_Helper_Data::FILE_TYPE_CSV:
				$this->_writeCSVHeader($storeId);
				break;
		}
	}

	/**
	 * Write all Attributes from given Product to the loaded File
	 * @param Mage_Catalog_Model_Product $product Product to write attributes out
	 * @param int $storeId WebsiteID/StoreID
	 * @return none
	 */
	public function writeProduct(Mage_Catalog_Model_Product &$product, $storeId) {
		$product->setStoreId($storeId)->load($product->getId());
		$collection = $this->getAttributesCollection($storeId);
		$line = '';
		foreach ($collection as $attribute) {
			if (!empty($line)) {
				$line .= self::CSV_SEPARATOR;
			}
			$_value = Mage::helper('delightexport')->getAttributeValue($product, $attribute->getAttribute(), $attribute->getListSeparator());
			$value = '';

			switch ($attribute->getFormat()) {
				case Delight_Delightexport_Helper_Data::FIELD_TYPE_TEXT:
					$value = (string)$_value;
					break;
				case Delight_Delightexport_Helper_Data::FIELD_TYPE_NUMBER:
					$value = (float)$_value;
					break;
				case Delight_Delightexport_Helper_Data::FIELD_TYPE_LIST:
					if (!is_array($_value)) {
						$_value = array($_value);
					}
					foreach ($_value as $v) {
						if (is_array($v)) {
							$_val = '';
							foreach ($v as $_v) {
								if (!empty($_val)) {
									$_val .= $attribute->getListSeparator();
								}
								$_val .= $_v;
							}
							$_v = $_val;
						} else {
							$_v = $v;
						}

						if (!empty($value)) {
							if (is_array($v)) {
								$value .= rtrim($attribute->getListSeparator()).ltrim($attribute->getListSeparator());
							} else {
								$value .= $attribute->getListSeparator();
							}
						}
						$value .= $_v;
					}
					break;
			}

			$line .= '"'.Mage::helper('delightexport')->escapeCSV($value).'"';
		}
		$this->writeFileLine($line);
	}

	/**
	 * Create a new File or open an existent one for appending new Content
	 * @param int $storeId WebsiteID/StoreID
	 * @param boolean $newFile Create a new File or append
	 * @return none
	 */
	public function loadFile($storeId, $newFile=false) {
		$fileName = $this->getRealFileName(false, $storeId);
		$this->_ioAdapter = new Varien_Io_File();
		$this->_ioAdapter->checkAndCreateFolder(dirname($fileName));
		$this->_ioAdapter->open(array('path' => dirname($fileName)));
		if ($newFile && $this->_ioAdapter->fileExists(basename($fileName), true)) {
			$this->_ioAdapter->rm(basename($fileName));
		}
		$this->_ioAdapter->streamOpen($fileName, 'a+');
		if ($newFile) {
			$this->initFile($storeId);
		}
	}

	/**
	 * Close the loaded File (see loadFile($storeId, $newFile))
	 * @param int $storeId WebsiteId/StoreId
	 * @return none
	 */
	public function closeFile($storeId) {
		$fileName = $this->getRealFileName(false, $storeId);
		$this->_ioAdapter->streamClose($fileName);
	}

	/**
	 * Write a line of Text to the Opend File
	 * @param string $line
	 * @return none
	 */
	protected function writeFileLine($line) {
		$line = trim($line).chr(10);
		$this->_ioAdapter->streamWrite($line);
	}

	/**
	 * Write the CSV Header to the File
	 * @param int $storeId WebsiteID/StoreID
	 * @return none
	 */
	protected function _writeCSVHeader($storeId) {
		$collection = $this->getAttributesCollection($storeId);
		$header = '';
		foreach ($collection as $attribute) {
			if (!empty($header)) {
				$header .= self::CSV_SEPARATOR;
			}
			$header .= '"'.Mage::helper('delightexport')->escapeCSV($attribute->getExportField()).'"';
		}
		$this->writeFileLine($header);
	}

}
