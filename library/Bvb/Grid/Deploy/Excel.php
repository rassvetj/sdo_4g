<?php

/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Bvb_Grid
 * @copyright  Copyright (c)  (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id: Excel.php 1160 2010-05-14 14:58:52Z bento.vilas.boas@gmail.com $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */

class Bvb_Grid_Deploy_Excel extends Bvb_Grid  implements Bvb_Grid_Deploy_DeployInterface {

	const OUTPUT = 'excel';

	public $deploy = array ();

    public function __construct($options) {

		if (! in_array ( self::OUTPUT, $this->_export )) {
			echo $this->__ ( "You dont' have permission to export the results to this format" );
			die ();
		}

        $this->_setRemoveHiddenFields(true);
		parent::__construct ($options);
	}


    public function deploy() {

		$this->setNumberRecordsPerPage ( 0 );

		parent::deploy ();

		if(!isset($this->options['title']))
		{
		    $this->options['title'] = _('Лист 1');//($title = Zend_Registry::get('serviceContainer')->getService('Option')->getOption('windowTitle')) ? $title : _('���� 1');
		}

		$titles = parent::_buildTitles ();
		$wsData = parent::_buildGrid ();
		$sql = parent::_buildSqlExp ();


		if (is_array ( $wsData ) && count($wsData)>65569) {
		    throw new Bvb_Grid_Exception('Maximum number of recordsa allowed is 65569');
		}

		$xml = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>
<Workbook xmlns:x="urn:schemas-microsoft-com:office:excel"
  xmlns="urn:schemas-microsoft-com:office:spreadsheet"
  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
                
                $xml .= '<Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Arial Cyr" x:CharSet="204"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s21">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
  </Style>
 </Styles>';                       
                
		$xml .= '<Worksheet ss:Name="' .  $this->options['title']  . '" ss:Description="' .  $this->options['title']  . '"><ss:Table>';

		$xml .= '<ss:Row>';
		foreach ( $titles as $value ) {

			//$type = ! is_numeric ($value ['value'] ) ? 'String' : 'Number';
            $type = 'String';

            $value['value'] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $value['value']);
			$xml .= '<ss:Cell><Data ss:Type="' . $type . '">' . $value ['value'] . '</Data></ss:Cell>';
		}
		$xml .= '</ss:Row>';

		if (is_array ( $wsData )) {
			foreach ( $wsData as $row ) {

				$xml .= '<ss:Row>';
				$a = 1;
				foreach ( $row as $value ) {
                                        $value['value'] = str_replace('</p>', '&#10;', $value['value']);
                                        $value['value'] = strip_tags($value['value']);                        

					//$type = ! is_numeric ( $value ['value'] ) ? 'String' : 'Number';
                                        $type = 'String';

                                        $value['value'] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $value['value']);

					$xml .= '<ss:Cell ss:StyleID="s21"><Data ss:Type="' . $type . '">' . $value ['value'] . '</Data></ss:Cell>';

					$a ++;
				}
				$xml .= '</ss:Row>';
			}

		}

		if (is_array ( $sql )) {
			$xml .= '<ss:Row>';
			foreach ( $sql as $value ) {

				$type = ! is_numeric ( $value ['value'] ) ? 'String' : 'Number';

                $value['value'] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $value['value']);

				$xml .= '<ss:Cell><Data ss:Type="' . $type . '">' . $value ['value'] . '</Data></ss:Cell>';
			}
			$xml .= '</ss:Row>';
		}

		$xml .= '</ss:Table></Worksheet>';

		$xml .= '</Workbook>';


        if (! isset($this->deploy['save'])) {
            $this->deploy['save'] = false;
        }

        if (! isset($this->deploy['download'])) {
            $this->deploy['download'] = false;
        }

        if ($this->deploy['save'] != 1 && $this->deploy['download'] != 1) {
            throw new Exception('Nothing to do. Please specify download&&|save options');
        }


        if (empty($this->deploy['name'])) {
            $this->deploy['name'] = date(HM_Controller_Action_Crud::EXPORT_FILENAME);
        }

        if (substr($this->deploy['name'], - 4) == '.xls') {
            $this->deploy['name'] = substr($this->deploy['name'], 0, - 4);
        }

        $this->deploy['dir'] = rtrim($this->deploy['dir'], '/') . '/';

        if (! is_dir($this->deploy['dir'])) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not a dir');
        }

        if (! is_writable($this->deploy['dir'])) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not writable');
        }

        if ($this->deploy['save'] == 1) {
            file_put_contents($this->deploy['dir'] . $this->deploy['name'] . ".xls", $xml);
        }

        if ($this->deploy['download'] == 1) {
        	$request = Zend_Controller_Front::getInstance()->getRequest();
			$contentType = strpos($request->getHeader('user_agent'), 'opera') ? 'application/x-download' : 'application/excel';
        	ob_end_clean();        	
        	
        	/*
        	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");        	
        	header("Cache-Control: no-cache");
        	header("Pragma: no-cache");
        	*/
        	
			header('Content-type: '.$contentType);
			header('Content-Disposition: attachment; filename="' . $this->deploy['name'] . '.xls"');
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false);
			header("Pragma: public");
			header("Content-Transfer-Encoding: binary");
            
			echo $xml;
        }

		exit();
	}

}