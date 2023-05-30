<?php
class HM_Form_Element_ElRTE extends Zend_Form_Element_Text
{
    public $helper = 'elRTE';
    
    public function __construct($spec, $options = null)
    {
        // wysiwyg редактор можно настраивать в config.ini, поэтому
        // для того, что-бы инстанцировать elRTE напрямую нет причин!
        if (Zend_Registry::get('config')->wysiwyg->editor != 'elRTE') {
            throw new HM_Exception(_("elRTE не является wysiwyg редактором по умолчанию."));
        }
        parent::__construct($spec, $options = null);
    }
}