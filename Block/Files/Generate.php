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
 * Generate an Export-File
 *
 * @category   Custom
 * @package    Delight_Delightexport
 * @author     delight software gmbh <info@delightsoftware.com>
 */
class Delight_Delightexport_Block_Files_Generate extends Mage_Adminhtml_Block_Widget_Form_Container {

	public function __construct() {
		parent::__construct();
		$this->_blockGroup = 'delightexport';
		$this->_controller = 'files';
		$this->_mode = 'generate';

		$this->_removeButton('reset');
		$this->_removeButton('delete');
		$this->_removeButton('save');
		$this->_removeButton('delete');

		$model = Mage::registry('current_file');

		$this->_formInitScripts[] = '
            var progressUpdater = function() {
                return {
                    process: function(p) {
	                    new Ajax.Request("'.$this->getUrl('*/*/doGenerate').'", {
	                        parameters: { file:'.$model->getEntityId().', created:p, num:10 },
	                        method: \'post\',
	                        evalScripts: false,
	                        onComplete: function(transport) {
	                            var s = transport.responseJSON || {created:0,pages:0,percent:0,error:true,message:\'Unknown Error\'};
	                            console.debug(transport.responseText);
	                            $(\'delightexport_progress_bar_state\').innerHTML = s.created+\' '.$this->__('of').' \'+s.pages;
	                            $(\'delightexport_progress_bar\').style.width = s.percent+\'%\';
	                            if ((s.created != s.pages) && !s.error)
	                                progressUpdater.process(s.created);
	                            if (s.error)
	                                $(\'delightexport_progress_bar_state\').innerHTML = s.message;
                            },
                            onFailure: function() {
                                $(\'delightexport_progress_bar_state\').innerHTML = "'.$this->__('Error while generating the File').'";
                            }
                        });
                    }
                }
            }();

             Event.observe(window, \'load\', function(){
             	progressUpdater.process(0);
           });
        ';
	}

	public function getHeaderText() {
		return $this->__('Generate Export-File');
	}

	public function getHeaderCssClass() {
		return 'icon-head head-customer-groups';
	}

}
