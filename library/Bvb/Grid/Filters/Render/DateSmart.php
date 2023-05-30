<?php 	//[che 5.06.2014 #16976]
	// Для защиты Oracle от "мусора" - мало ли что введут пользователи и программисты в поле даты!
class Bvb_Grid_Filters_Render_DateSmart extends Bvb_Grid_Filters_Render_RenderAbstract
{
  function getFields ()
    {
        return array('from', 'to');
    }

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

	$out = '<div class="grid-filter-daterange"><div class="grid-filter-daterange-item grid-filter-daterange-from"><div class="wrapFiltersInput"><label for="'.$date_from_id.'">'.$this->__('From').":</label>".$this->getView()->formText($this->getFieldName().'[from]', urldecode($this->getDefaultValue('from')), array_merge($this->getAttributes(),array('id'=>'filter_'.$this->getGridId().$this->getFieldName().'_from'))).'<span class="clearFilterSpan">&nbsp;</span></div></div>'
        .'<div class="grid-filter-daterange-item grid-filter-daterange-to"><div class="wrapFiltersInput"><label for="'.$date_to_id.'">'.$this->__('To').":</label>".$this->getView()->formText($this->getFieldName().'[to]', urldecode($this->getDefaultValue('to')), array_merge($this->getAttributes(),array('id'=>'filter_'.$this->getGridId().$this->getFieldName().'_to'))).'<span class="clearFilterSpan">&nbsp;</span></div></div>';

        return $out;
    }

    public function transform($date, $key)
    {
	$date = urldecode($date);
        $date = str_replace('-', '.', $date);
        $date = explode('.', $date);

	foreach($date as $i=>$d) 
		$date[$i] = intval($d);

	$D = $M = $Y = -1;
        switch(count($date))
        {
        case 1:
	    $date = $date[0];
	    if($date==0)
	    {
                $D = 1;
                $M = 1;
                $Y = $key == 'to'?2100:0;
            }
	    else if($date<=31)
            {
                $D = $date;
                $M = date("m");
                $Y = date("Y");
            }
            else if($date<=12)
            {
                $D = date("d");
                $M = $date;
                $Y = date("Y");
            }
            else 
            {
                $D = date("d");
                $M = date("m");
                $Y = $date;
            }
        break;

	case 2:
	    if($date[0]>=12 || $date[1]<=12)
            {
                $D = $date[0];
                $M = $date[1];
                $Y = date("Y");
            }
            else if($date[1]<=12)
            {
                $D = date("d");
                $M = $date[0];
                $Y = $date[1];
            }
        break;

        case 3:
                $D = $date[0];
                $M = $date[1];
                $Y = $date[2];
        break;
        }

	$Y = ($Y<2000?$Y+2000:$Y);
	$date = sprintf('%02d.%02d.%02d', $D, $M, $Y);
        $dateObject = new Zend_Date($date, 'dd.MM.yyyy');
        if ($key == 'to') {
            $value = $dateObject->toString('yyyy-MM-dd 23:59:59');
        } else {
            $value = $dateObject->toString('yyyy-MM-dd');
        }

        return $value;
    
    }
    
}