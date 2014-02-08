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
 * Files-Controller
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @author     delight software gmbh <info@delightsoftware.com>
 */
class Delight_Delightexport_FilesController extends Mage_Adminhtml_Controller_Action {

	protected function _initFile() {
		Mage::register('current_file', Mage::getModel('delightexport/files'));
		$fileId = $this->getRequest()->getParam('file_id');
		if (!is_null($fileId)) {
			Mage::registry('current_file')->load($fileId);
		}
	}

	protected function _initAction($submenu='Files') {
		$this->loadLayout()
			->_setActiveMenu('promp/delightexport/files')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Promo'), Mage::helper('adminhtml')->__('Promo'))
			->_addBreadcrumb(Mage::helper('delightexport')->__('Delight-Export'), Mage::helper('delightexport')->__('Delight-Export'))
			->_addBreadcrumb(Mage::helper('delightexport')->__($submenu), Mage::helper('delightexport')->__($submenu));
		return $this;
	}

	public function indexAction() {
		$this->_initAction()
			->_addBreadcrumb(Mage::helper('delightexport')->__('DelightExport Files'), Mage::helper('delightexport')->__('Files'))
			->_addContent($this->getLayout()->createBlock('delightexport/files'))
			->renderLayout();
	}

	public function newAction() {
		try {
			$this->_initFile();
			$this->_initAction()
				->_addBreadcrumb(Mage::helper('delightexport')->__('New ExportFile Configuration'), Mage::helper('delightexport')->__('New File Configuration'))
				->_addContent($this->getLayout()->createBlock('delightexport/files_edit'))
				->renderLayout();
		} catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
			$this->_redirect('*/*/index');
		}
	}

	public function editAction() {
		$id = $this->getRequest()->getParam('file_id');
		$model = Mage::getModel('delightexport/files');

		try {
			if ($id) {
				$model->load($id);
			}
			Mage::register('current_file', $model);

			$this->_initAction()
				->_addBreadcrumb($id ? Mage::helper('delightexport')->__('Edit Export-File') : Mage::helper('delightexport')->__('Create new Export-File'), $id ? Mage::helper('delightexport')->__('Edit Export-File') : Mage::helper('delightexport')->__('Create new Export-File'))
				->_addContent($this->getLayout()->createBlock('delightexport/files_edit'))
				->renderLayout();
		} catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
			$this->_redirect('*/*/index');
		}
	}

	public function saveAction() {
		$model = Mage::getModel('delightexport/files');
		$id = $this->getRequest()->getParam('file_id');
		if (!is_null($id)) {
			$model->load($id);
		}

		try {
			// Save the File
			Mage::dispatchEvent('delightexport_before_save', array('file'=>$model));
			$model->setFileName($this->getRequest()->getParam('file_name'))
				->setFileType($this->getRequest()->getParam('file_type'))
				->setStoreIds(implode(',', $this->getRequest()->getParam('store_ids')))
				->setFileDescription($this->getRequest()->getParam('file_description'))
				->save();

			// Delete all unassigned Attributes and set new values to configured ones
			$attributes = $this->getRequest()->getParam('attributes');
			$submitted = array();
			foreach ($attributes as $attribute) {
				$submitted[] = $attribute['entity_id'];
			}
			$configCollection = $model->getAttributesCollection();
			foreach ($configCollection as $config) {
				if (!in_array($config->getEntityId(), $submitted)) {
					$config->delete();
				} else {
					foreach ($attributes as $attribute) {
						if ($attribute['entity_id'] != $config->getEntityId()) {
							continue;
						}
						if ($attribute['delete'] == 1) {
							$config->delete();
						} else {
							$config->setFileId($id)
								->setAttribute($attribute['attribute'])
								->setExportField($attribute['export_field'])
								->setFormat($attribute['format'])
								->setPreText($attribute['pre_text'])
								->setPostText($attribute['post_text'])
								->setListSeparator($attribute['list_separator'])
								->setConfig(serialize(array()))
								->save();
						}
						unset($submitted[array_search($attribute['entity_id'], $submitted)]);
					}
				}
			}

			// Create all new Attributes
			foreach ($attributes as $attribute) {
				if (in_array($attribute['entity_id'], $submitted)) {
					Mage::getModel('delightexport/config')->setFileId($id)
						->setAttribute($attribute['attribute'])
						->setExportField($attribute['export_field'])
						->setFormat($attribute['format'])
						->setPreText($attribute['pre_text'])
						->setPostText($attribute['post_text'])
						->setConfig(serialize(array()))
						->save();
				}
			}

			Mage::dispatchEvent('delightexport_after_save', array('file'=>$model));

			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('delightexport')->__('Export-File successfully saved'));
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$this->_redirect('*/*/index');
	}

	public function deleteAction() {
		try {
			$id = $this->getRequest()->getParam('file_id');
			$model = Mage::getModel('delightexport/files');
			$model->load($id);
			Mage::dispatchEvent('delightexport_before_delete', array('file'=>$model));
			if ($model->getEntityId()) {
				$configCollection = $model->getAttributesCollection();
				foreach ($configCollection as $config) {
					$config->delete();
				}
				$model->delete();
			}
			Mage::dispatchEvent('delightexport_after_delete', array('file'=>$model));
			$this->_getSession()->addSuccess($this->__('Export-File Configuration deleted'));
		} catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}
		$this->_redirect('*/*/index');
	}

	public function generateAction() {
		$id = $this->getRequest()->getParam('file_id');
		if (!$id) {
			$this->_getSession()->addError($this->__('No Exportfile choosen to generate'));
			$this->_redirect('*/*/index');
		}
		try {
			$model = Mage::getModel('delightexport/files')->load($id);
			Mage::register('current_file', $model);

			$this->_initAction()
				->_addBreadcrumb(Mage::helper('delightexport')->__('Generate Export-File'), Mage::helper('delightexport')->__('Generate Export-File'))
				->_addContent($this->getLayout()->createBlock('delightexport/files_generate'))
				->renderLayout();
		} catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
			$this->_redirect('*/*/index');
		}
	}

	public function doGenerateAction() {
		$file = $this->getRequest()->getParam('file');
		$created = $this->getRequest()->getParam('created');
		$processNum = $this->getRequest()->getParam('num');
		if (empty($created)) {
			$created = 0;
		}
		if (empty($processNum)) {
			$processNum = 50;
		}

		$model = Mage::getModel('delightexport/files')->load($file);
		$numProducts = $model->getNumProducts();
		$max = 0;
		$storeId = null;
		$storeCreated = 0;
		foreach ($numProducts as $s => $n) {
			$max += $n;
			if (($created < $max) && is_null($storeId)) {
				$storeId = $s;
				$startNum = ($n - ($max - $created));
			}
		}

		// Load all Products from current Store and write them to the File
		$model->loadFile($storeId, ($startNum == 0));

		$collection = $model->getProductsCollection($storeId);
		$num = 0;
		foreach ($collection as $product) {
			if ($num < $startNum) { $num++; continue; }        // skip already processed products
			if ($num >= ($startNum + $processNum)) { break; }  // Don't process more than $processNum products

			$model->writeProduct($product, $storeId);
			$num++;
		}
		$model->closeFile($storeId);

		$out = new stdClass();
		$out->created = $created + ($num - $startNum);
		$out->pages = $max;
		$out->percent = ((int)($out->created*100/$out->pages));

		$this->getResponse()->clearBody();
		$this->getResponse()->setHeader('Content-Type', 'application/json', true);
		$this->getResponse()->setBody(json_encode($out));
	}

	public function gridAction() {
		$this->getResponse()->setBody($this->getLayout()->createBlock('delightexport/files_grid')->toHtml());
	}
}