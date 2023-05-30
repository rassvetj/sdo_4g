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
 * @version    $Id: Word.php 1173 2010-05-19 18:25:41Z bento.vilas.boas@gmail.com $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */



class Bvb_Grid_Deploy_Word extends Bvb_Grid implements Bvb_Grid_Deploy_DeployInterface
{

    const OUTPUT = 'word';

    protected $options = array ();

    public $deploy;


    public function __construct( $options )
    {

        $this->_setRemoveHiddenFields(true);
        parent::__construct ($options  );

        $this->addTemplateDir ( 'Bvb/Grid/Template/Word', 'Bvb_Grid_Template_Word', 'word' );

    }

    public function deploy()
    {

        if (! in_array ( self::OUTPUT, $this->_export ))
        {
            echo $this->__ ( "You dont' have permission to export the results to this format" );
            die ();
        }

        $this->setNumberRecordsPerPage ( 0 );

        parent::deploy ();


        if (! $this->_temp['word'] instanceof Bvb_Grid_Template_Word_Word) {
            $this->setTemplate('word', 'word');
        }

        $titles = parent::_buildTitles ();

        #$nome = reset($titles);
        $wsData = parent::_buildGrid ();
        $sql = parent::_buildSqlExp ();

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

        if (substr($this->deploy['name'], - 4) == '.doc') {
            $this->deploy['name'] = substr($this->deploy['name'], 0, - 4);
        }

        $this->deploy['dir'] = rtrim($this->deploy['dir'], '/') . '/';

        if (! is_dir($this->deploy['dir'])) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not a dir');
        }

        if (! is_writable($this->deploy['dir'])) {
            throw new Bvb_Grid_Exception($this->deploy['dir'] . ' is not writable');
        }

        $fp = fopen($this->deploy['dir'] . $this->deploy['name'] . ".doc", 'w+');

        $xml = $this->_temp ['word']->globalStart ();
        fwrite($fp, $xml, mb_strlen($xml, 'windows-1251'));

        $xml = $this->_temp ['word']->titlesStart ();
        fwrite($fp, $xml, mb_strlen($xml, 'windows-1251'));

        foreach ( $titles as $value )
        {
            $value['value'] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $value['value']);
            if (($value ['field'] != $this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '') {
                $xml = str_replace ( "{{value}}", $value ['value'], $this->_temp ['word']->titlesLoop () );
                fwrite($fp, $xml, mb_strlen($xml, 'windows-1251'));
            }
        }
        $xml = $this->_temp ['word']->titlesEnd ();
        fwrite($fp, $xml, mb_strlen($xml, 'windows-1251'));


        if (is_array ( $wsData ))
        {
            /////////////////
            if ($this->getInfo('hRow,title') != '')
            {
                $bar = $wsData;

                $hbar = trim ($this->getInfo('hRow,title'));

                $p = 0;
                foreach ( $wsData [0] as $value )
                {
                    if ($value ['field'] == $hbar)
                    {
                        $hRowIndex = $p;
                    }

                    $p ++;
                }
                $aa = 0;
            }

            //////////////
            //////////////
            //////////////

            $i = 1;
            $aa = 0;
            foreach ( $wsData as $row )
            {
                ////////////
                //A linha horizontal
                if ($this->getInfo('hRow,title') != '')
                {

                 if ( ! isset($bar[$aa - 1][$hRowIndex]) ) {
                        $bar[$aa - 1][$hRowIndex]['value'] = '';
                    }

                    if ($bar [$aa] [$hRowIndex] ['value'] != $bar [$aa - 1] [$hRowIndex] ['value'])
                    {
                        $bar [$aa] [$hRowIndex] ['value'] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $bar [$aa] [$hRowIndex] ['value']);
                        $xml = str_replace ( "{{value}}", $bar [$aa] [$hRowIndex] ['value'], $this->_temp ['word']->hRow () );
                        fwrite($fp, $xml, mb_strlen($xml, 'windows-1251'));
                    }
                }
                ////////////

                $xml = $this->_temp ['word']->loopStart ();
                fwrite($fp, $xml, mb_strlen($xml, 'windows-1251'));
                $a = 1;
                foreach ( $row as $value )
                {
// через ";" выглядит адекватнее                    
                    //$value ['value'] = strip_tags ( $value ['value'], '<p>' );
                    $value['value'] = str_replace('</p><p>', '; ', $value['value']);
                    $value['value'] = strip_tags($value['value']);                    
                    
                    $value['value'] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $value['value']);
                    
                    if ((@$value ['field'] !=$this->getInfo('hRow,field') && $this->getInfo('hRow,title') != '') || $this->getInfo('hRow,title') == '') {
                        $xml = str_replace ( "{{value}}", $value ['value'], $this->_temp ['word']->loopLoop ( 2 ) );
                        fwrite($fp, $xml, mb_strlen($xml, 'windows-1251'));
                    }
                    $a ++;
                }
                $xml = $this->_temp ['word']->loopEnd ();
                fwrite($fp, $xml, mb_strlen($xml, 'windows-1251'));
                $aa ++;
                $i ++;
            }
        }


        if (is_array ( $sql ))
        {
            $xml = $this->_temp ['word']->sqlExpStart ();
            fwrite($fp, $xml, mb_strlen($xml, 'windows-1251'));
            foreach ( $sql as $value )
            {
                $value['value'] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $value['value']);

                $xml = str_replace ( "{{value}}", $value ['value'], $this->_temp ['word']->sqlExpLoop () );
                fwrite($fp, $xml, mb_strlen($xml, 'windows-1251'));
            }
            $xml = $this->_temp ['word']->sqlExpEnd ();
            fwrite($fp, $xml, mb_strlen($xml, 'windows-1251'));
        }


        $xml = $this->_temp ['word']->globalEnd ();
        fwrite($fp, $xml, mb_strlen($xml, 'windows-1251'));

        fclose($fp);
        //file_put_contents($this->deploy['dir'] . $this->deploy['name'] . ".doc", $xml);


        if ($this->deploy['download'] == 1) {        	
        	$request = Zend_Controller_Front::getInstance()->getRequest();
        	$contentType = strpos($request->getHeader('user_agent'), 'opera') ? 'application/x-download' : 'application/word';
        	ob_end_clean();
        	 
        	header('Content-type: '.$contentType);
        	header('Content-Disposition: attachment; filename="' . $this->deploy['name'] . '.doc"');
        	header("Expires: 0");
        	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        	header("Cache-Control: private",false);
        	header("Pragma: public");
        	header("Content-Transfer-Encoding: binary");
        	
        	readfile($this->deploy['dir'] . $this->deploy['name'] . '.doc');
        }

        if ($this->deploy['save'] != 1) {
            unlink($this->deploy['dir'] . $this->deploy['name'] . '.doc');
        }

        die ();
    }

}