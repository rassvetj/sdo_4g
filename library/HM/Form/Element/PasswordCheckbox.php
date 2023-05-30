<?php

class HM_Form_Element_PasswordCheckbox extends Zend_Form_Element_Checkbox {
    
        public function render(Zend_View_Interface $view = null) {
        if (null == $view) {
            $view = $this->getView();
        }

        


        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }


        $script = '<script type="text/javascript">
        	
           $("#'.$this->id.'").change(function(){
    
    			var checked = $(this).attr("checked");
    			
    			if(checked == true){
    		';
         
        foreach ($this->inputs as $val){
            $script.='$("#'.$val.'").attr("disabled", true);'.PHP_EOL;
             
        }

        $script .= '}
    			else{';

        foreach ($this->inputs as $val){
            $script.='$("#'.$val.'").attr("disabled", false);'.PHP_EOL;
             
        }
        $script.='
                }
    		});
    		';
        

        if($this->getValue() == 1){
            $script.='$(document).ready(function() {
            ';
            
            foreach ($this->inputs as $val){
                $script.='$("#'.$val.'").attr("disabled", true);'.PHP_EOL;
                 
            }

            $script.='});';
        }
        
        
        $script.='
        </script>';
  

        return parent::render($view).$script;

    }
}