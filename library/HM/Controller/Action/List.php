<?php

class HM_Controller_Action_List extends HM_Controller_Action
{
    protected $_data = array(); 
    
    protected function _export($format) 
    {
        switch ($format) {
            case 'excel':
                $this->_exportToExcel();
            break;
            default:
                $this->view->error = _('Данный формат выгрузки не поддерживается');;
            break;
        }
    }
    
    // duplicated from Bvb_Grid_Deploy_Excel
    protected function _exportToExcel()
    {
		$title = Zend_Registry::get('serviceContainer')->getService('Option')->getOption('windowTitle');
		$attribs = $this->_getExportAttribs();

		if (is_array ( $this->_data ) && count($this->_data)>65569) {
		    throw new HM_Exception('Maximum number of recordsa allowed is 65569');
		}

		$xml = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>
<Workbook xmlns:x="urn:schemas-microsoft-com:office:excel"
  xmlns="urn:schemas-microsoft-com:office:spreadsheet"
  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';

		$xml .= '<Worksheet ss:Name="' .  $title  . '" ss:Description="' .  $title  . '"><ss:Table>';

		$xml .= '<ss:Row>';
		foreach ($attribs as $title => $value) {

			//$type = ! is_numeric ($value ['value'] ) ? 'String' : 'Number';
            $type = 'String';

			$xml .= '<ss:Cell><Data ss:Type="' . $type . '">' . $title . '</Data></ss:Cell>';
		}
		$xml .= '</ss:Row>';

		if (is_array ( $this->_data )) {
			foreach ( $this->_data as $item ) {

				$xml .= '<ss:Row>';
			    foreach ($attribs as $title => $method) {
					//$type = ! is_numeric ( $value ['value'] ) ? 'String' : 'Number';
                    $type = 'String';
                    $value = '';
                    if (method_exists($item, $method)) {
                        $value = $item->$method();
                    } elseif (!empty($item->$method)) {
                        $value = $item->$method;
                    } 
					$xml .= '<ss:Cell><Data ss:Type="' . $type . '">' . $value . '</Data></ss:Cell>';
				}
				$xml .= '</ss:Row>';
			}
		}

		$xml .= '</ss:Table></Worksheet>';
		$xml .= '</Workbook>';

    	$request = Zend_Controller_Front::getInstance()->getRequest();
		$contentType = strpos($request->getHeader('user_agent'), 'opera') ? 'application/x-download' : 'application/excel';
		$fileName = date(HM_Controller_Action_Crud::EXPORT_FILENAME);
    	ob_end_clean();        	
    	
    	/*
    	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");        	
    	header("Cache-Control: no-cache");
    	header("Pragma: no-cache");
    	*/
    	
		header('Content-type: '.$contentType);
		header('Content-Disposition: attachment; filename="' . $fileName . '.xls"');
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Pragma: public");
		header("Content-Transfer-Encoding: binary");
        
		echo $xml;
		exit();
    }
}