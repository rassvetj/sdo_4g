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
 * @version    $Id: Date.php 1186 2010-05-21 18:16:48Z bento.vilas.boas@gmail.com $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */


class Bvb_Grid_Filters_Render_Date extends Bvb_Grid_Filters_Render_RenderAbstract
{


  function getFields ()
    {
        return array('from', 'to');
    }

/*   function normalize($value,$part ='')
    {    
    //echo date('d.m.Y',strtotime($value));
        return date('d.m.Y',strtotime($value));
    }*/


    public function getConditions ()
    {
        return array('from' => '>=', 'to' => '<=');
    }


    function render ()
    {
        $this->removeAttribute('id');
        $date_from_id = "filter_".$this->getGridId().$this->getFieldName()."_from";
        $date_to_id = "filter_".$this->getGridId().$this->getFieldName()."_to";
        //$this->setAttribute('style','width:85px !important;');
        //print_r($this);
        $script="
                    $('#{$date_from_id}, #{$date_to_id}').datepicker({
                           showOn: 'button',
                           buttonImage: '".$this->getView()->serverUrl()."/images/icons/calendar.png',
                           buttonImageOnly: true });";
        $this->getView()->jQuery()->addOnload($script);

        return '<div class="grid-filter-daterange"><div class="grid-filter-daterange-item grid-filter-daterange-from"><div class="wrapFiltersInput"><label for="'.$date_from_id.'">'.$this->__('From').":</label>".$this->getView()->formText($this->getFieldName().'[from]', urldecode($this->getDefaultValue('from')), array_merge($this->getAttributes(),array('id'=>'filter_'.$this->getGridId().$this->getFieldName().'_from'))).'<span class="clearFilterSpan">&nbsp;</span></div></div>'
        .'<div class="grid-filter-daterange-item grid-filter-daterange-to"><div class="wrapFiltersInput"><label for="'.$date_to_id.'">'.$this->__('To').":</label>".$this->getView()->formText($this->getFieldName().'[to]', urldecode($this->getDefaultValue('to')), array_merge($this->getAttributes(),array('id'=>'filter_'.$this->getGridId().$this->getFieldName().'_to'))).'<span class="clearFilterSpan">&nbsp;</span></div></div>';
    }

    public function transform($date, $key)
    {
	$date = urldecode($date);

        $date = str_replace('-', '.', $date);
        $dateObject = new Zend_Date($date, 'dd.MM.yyyy');
        if ($key == 'to') {
            $value = $dateObject->toString('yyyy-MM-dd 23:59:59');
        } else {
            $value = $dateObject->toString('yyyy-MM-dd');
        }
        
        return $value;
    
    }

    
}