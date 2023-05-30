<?php

class Bvb_Grid_Filters_Render_SubStatus extends Bvb_Grid_Filters_Render_RenderAbstract
{


  function getFields ()
    {
        return array('from', 'to');
    }

    function normalize($value,$part ='')
    {
        return date('Y-m-d',strtotime($value));
    }


    public function getConditions ()
    {
        return '=';
    }


    function render ()
    {
       return "fdfsdfd<br/>fsdfsdf";
    }

}