<?php



class Bvb_Grid_Filters_Render_DatePicker extends Bvb_Grid_Filters_Render_RenderAbstract
{


   function render ()
    {

        $name = $this->getFieldName();
        $value = $this->getDefaultValue();
        
        if ($value != "")
        {
            $value_n = $value;
            $dateObject = new Zend_Date($value, 'yyyy-MM-dd');
            
            $value=$dateObject->toString('dd.MM.yyyy');
        }
        
       /*
        * Этот код нужно изменить и сделать так чтобы в подгружаемом 
        * контенте тоже работал ajax
        *  
        *  $content = <<< CONTENT
        <script type="text/javascript">
		$(function (){
		//alert($.fn.jquery);
		 $("#{$name}").datepicker({ dateFormat: 'dd.mm.yy',  altField: '#filter_grid{$name}', altFormat: 'yy-mm-dd'});
				

		});
</script>

<input type="hidden" id="filter_grid{$name}" name="{$name}" value="{$value_n}">   
<input type="text" id="{$name}" value="{$value}"> 
        
        
        
CONTENT;*/
        
        
        $content = <<< CONTENT
      <input type="text" value="{$value}" id="filter_grid{$name}" name="{$name}">
CONTENT;
        
        
        
        
        return $content;
        
        
        
    }
    
    function getFields(){
    
        return true;
    }
    
    
    
}