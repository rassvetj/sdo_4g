<?php

class HM_Form_Element_JQLists extends Zend_Form_Element
{
    
    public $list1Name;
    public $list1Title;
    public $list1Options;
    public $list2Name;
    public $list2Title;
    public $list2Options;
    public $filter;

    public function render(Zend_View_Interface $view = null)
    {

        if (null == $view)
        {
            $view = $this->getView();
        }
        
        $content = $view->jQLists( $this->getName(), array (
            'title' => $this->list1Title, 
            'name' => $this->list1Name, 
            'options' => $this->list1Options ), 
            array (
                'title' => $this->list2Title, 
                'name' => $this->list2Name, 
            	'options' => $this->list2Options ), 
            $this->filter );
        foreach ( $this->getDecorators() as $decorator )
        {
            
            if (! ($decorator instanceof Zend_Form_Decorator_ViewHelper))
            {
                $decorator->setElement( $this );
                $content = $decorator->render( $content );
            }
        }
        
        return $content;
    
    }
    
 public function setValue($arr){
     $this->list1Options=$arr[0];
     $this->list2Options=$arr[1];
 
 } 

 
 public function getValue(){
     return array($this->list1Options,$this->list2Options);
 
 }
    
}