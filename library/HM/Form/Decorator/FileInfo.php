<?php
class HM_Form_Decorator_FileInfo extends Zend_Form_Decorator_Abstract {

    protected $_tag = '';

    public function render($content) {
        // Получаем объект элемента к которому применяется декоратор
        $element = $this->getElement();

        // Проверяем объект вида зарегистрированного для формы
        if (null === $element->getView()) {
            return $content;
        }
        
        $file = $this->getOption('file');
        $name = $this->getOption('name');
        
        if (file_exists($file)) {
            if (null == $name) {
                $name = basename($file); 
            }
            
            $filesize = new Zend_Measure_Binary(filesize($file));
            
            $html = sprintf('%s, %s', $name, $filesize->convertTo(Zend_Measure_Binary::KILOBYTE));
            
            if ($download = $this->getOption('download')) {
            	if (strlen($download)) {
            	   $html = sprintf('<a href="%s">'._('скачать').'</a> ', $download).$html;	
            	}            	
            }
            
        } else {
            $html = _('нет');
        }
        
        return $content . '<br>' . $html;
    }
}