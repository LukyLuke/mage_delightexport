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
 * Helper Class
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @author     delight software gmbh <info@delightsoftware.com>
 */
class Delight_Delightexport_Helper_Data extends Mage_Core_Helper_Abstract
{
	const FILE_TYPE_CSV = 0;
	const FILE_TYPE_XML = 1;

	const FIELD_TYPE_TEXT = 0;
	const FIELD_TYPE_NUMBER = 1;
	const FIELD_TYPE_LIST = 2;
	const FIELD_TYPE_DASHLIST = 3;
	const FIELD_TYPE_SLASHLIST = 4;

	public function getFileTypes() {
		return array(
			array('value'=>self::FILE_TYPE_CSV, 'title'=>$this->__('CSV Export'), 'label'=>$this->__('CSV Export'), 'extension'=>'csv'),
			array('value'=>self::FILE_TYPE_XML, 'title'=>$this->__('XML Export'), 'label'=>$this->__('XML Export'), 'extension'=>'xml')
		);
	}

	public function getGridFileTypes() {
		return array(
			self::FILE_TYPE_CSV => $this->__('CSV Export'),
			self::FILE_TYPE_XML => $this->__('XML Export')
		);
	}

	public function getWebsites() {
		$websites = Mage::app()->getWebsites(false);
		$options = array();
		foreach ($websites as $w) {
			$options[] = array('value'=>$w->getId(), 'title'=>$w->getName(), 'label'=>$w->getName(), 'code'=>$w->getCode());
		}
		return $options;
	}

	public function getFieldTypes() {
		return array(
			array('value'=>self::FIELD_TYPE_TEXT,      'title'=>$this->__('Textvalue'),       'label'=>$this->__('Textvalue')),
			array('value'=>self::FIELD_TYPE_NUMBER,    'title'=>$this->__('Number'),          'label'=>$this->__('Number')),
			array('value'=>self::FIELD_TYPE_LIST,      'title'=>$this->__('Seperated List'),  'label'=>$this->__('Seperated List'))
		);
	}

	public function getProductAttributes() {
		function cmp($a, $b) {
			return strcasecmp($a['label'], $b['label']);
		}

		$attributes = Mage::getModel('catalog/product')->getResource()->loadAllAttributes()->getSortedAttributes();
		$result = array();
		foreach ($attributes as $attribute) {
			if (!$attribute->getId() || $attribute->isScopeGlobal()) {
				$scope = 'global';
			} elseif ($attribute->isScopeWebsite()) {
				$scope = 'website';
			} else {
				$scope = 'store';
			}

			$result[] = array(
				'id'       => $attribute->getId(),
				'code'     => $attribute->getAttributeCode(),
				'type'     => $attribute->getFrontendInput(),
				'label'    => $attribute->getFrontendLabel() ? $attribute->getFrontendLabel() : $this->__($attribute->getAttributeCode()),
				'required' => $attribute->getIsRequired(),
				'scope'    => $scope
			);
		}

		// Add some special additional attributes we want to be able to export
		$result[] = array(
			'id'       => null,
			'code'     => 'category_labels',
			'type'     => null,
			'label'    => $this->__('category_labels'),
			'required' => false,
			'scope'    => $scope
		);
		$result[] = array(
			'id'       => null,
			'code'     => 'currency',
			'type'     => null,
			'label'    => $this->__('currency'),
			'required' => false,
			'scope'    => $scope
		);
		$result[] = array(
			'id'       => null,
			'code'     => 'shipment_min',
			'type'     => null,
			'label'    => $this->__('shipment_min'),
			'required' => false,
			'scope'    => $scope
		);
		$result[] = array(
			'id'       => null,
			'code'     => 'shipment_max',
			'type'     => null,
			'label'    => $this->__('shipment_max'),
			'required' => false,
			'scope'    => $scope
		);

		usort($result, 'cmp');
		return $result;
	}

	public function getAbsoluteFileNames($fileName) {
		$path = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).DS.'delightexport'.DS;
		$tmp  = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).DS.'tmp'.DS.'delightexport'.DS;
		return array(
			'real' => $path.$fileName,
			'tmp'  => $tmp.$fileName
		);
	}

	public function getAttributeValue(Mage_Catalog_Model_Product $product, $attrib) {
		$_attr = $this->getFunction($attrib);
		$value = '';

		switch ($_attr) {
			case 'getCategoryLabels':
				// Get all Category-Paths
				$pathList = array();
				foreach ($product->getCategoryCollection()->getData() as $cat) {
					$path = $cat['path'];
					$found = false;
					for ($i = 0; $i < count($pathList); $i++) {
						$cp = $pathList[$i];
						if (substr($path, 0, strlen($cp)) == $cp) {
							$pathList[$i] = $path;
							$found = true;
							break;
						}
					}
					if (!$found) {
						$pathList[] = $path;
					}
				}

				// Load all Categories and create a textual Pathlist
				$value = array();
				foreach ($pathList as $path) {
					$ids = explode('/', $path);
					$path = array();
					foreach ($ids as $id) {
						$cat = Mage::getModel('catalog/category')->load($id);
						if (($cat->getLevel() > 1) && ($cat->getParentId() > 0)) {
							$path[] = $cat->getName();
						}
					}
					$value[] = $path;
				}
				break;

			case 'getImage':
			case 'getThumbnail':
			case 'getSmallImage':
				$value = $product->$_attr();
				if (($value == 'nonexistent') || ($value == 'no_selection')) {
					$value = '';
				}
				if (!empty($value)) {
					$_val = $value;
					$value = '';
					foreach ($product->getMediaGalleryImages() as $media) {
						if ($media['file'] == $_val) {
							$value = $media['url'];
							break;
						}
					}

				}
				break;

			case 'getCurrency':
				$value = $product->getStore()->getCurrentCurrency()->getCurrencyCode();
				break;

			case 'getShipmentMin':
				$carriers = Mage::getSingleton('shipping/config')->getActiveCarriers($product->getStore());
				$request = Mage::getModel('shipping/rate_request')
					->setWebsiteId($product->getStore()->getWebsite()->getId())
					->setConditionName('package_weight')
					->setPackageWeight($product->getWeight())
					->setDestCountryId(Mage::getStoreConfig('general/country/default', $product->getStore()))
					->setDestRegionId(0);
				$price = null;
				foreach ($carriers as $carrier) {
					$c = $carrier->getRate($request);
					if (!is_array($c)) continue;
					if (is_null($price) || ($price > $c['price'])) {
						$price = $c['price'];
						settype($price, 'float');
					}
				}
				if (is_null($price)) {
					$price = 0.0;
				}
				$value = $price;
				break;
			case 'getShipmentMax':
				$carriers = Mage::getSingleton('shipping/config')->getActiveCarriers($product->getStore());
				$request = Mage::getModel('shipping/rate_request')
					->setWebsiteId($product->getStore()->getWebsite()->getId())
					->setConditionName('package_weight')
					->setPackageWeight($product->getWeight())
					->setDestCountryId(Mage::getStoreConfig('general/country/default', $product->getStore()))
					->setDestRegionId(0);
				$price = null;
				foreach ($carriers as $carrier) {
					$c = $carrier->getRate($request);
					if (!is_array($c)) continue;
					if (is_null($price) || ($price < $c['price'])) {
						$price = $c['price'];
						settype($price, 'float');
					}
				}
				if (is_null($price)) {
					$price = 0.0;
				}
				$value = $price;
				break;

			case 'getPrice':
			case 'getWeight':
				$value = $product->$_attr();
				settype($value, 'float');
				break;

			default:
				$value = $product->$_attr();
				break;
		}
		return $value;
	}

	public function getFunction($attrib) {
		$attrib = explode('_', $attrib);
		for ($i = 0; $i < count($attrib); $i++) {
			$attrib[$i] = ucfirst($attrib[$i]);
		}
		return 'get'.implode('', $attrib);
	}

	public function escapeCSV($str) {
		$str = (string)$str;
		$str = preg_replace('/[\r\n]+/smi', ' ', $str);
		$str = strip_tags($str);
		$str = str_replace('"', '&#34;', $str);
		return str_replace('&#34;', '""', $str);
	}
}
