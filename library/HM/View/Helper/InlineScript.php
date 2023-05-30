<?php
require_once 'Zend/View/Helper/InlineScript.php';

class HM_View_Helper_InlineScript extends Zend_View_Helper_InlineScript
{

    public function captureEnd($offset = null)
    {
        if (is_string($offset) && strlen($offset) > 0)
        {
            $content                   = ob_get_clean();
            $type                      = $this->_captureScriptType;
            $attrs                     = $this->_captureScriptAttrs;
            $this->_captureScriptType  = null;
            $this->_captureScriptAttrs = null;
            $this->_captureLock        = false;

            $this->offsetSetScript($offset, $content, $type, $attrs);
        }
        else
        {
            parent::captureEnd();
        }
    }

}
