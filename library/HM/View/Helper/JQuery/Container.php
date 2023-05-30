<?php
class HM_View_Helper_JQuery_Container extends ZendX_JQuery_View_Helper_JQuery_Container
{

    protected $_charset = "UTF-8";

    /**
     * Renders all javascript file related stuff of the jQuery enviroment.
     *
     * @return string
     */
    protected function _renderScriptTags()
    {
        $scriptTags = '';
        if( ($this->getRenderMode() & ZendX_JQuery::RENDER_LIBRARY) > 0) {
            $source = $this->_getJQueryLibraryPath();

            $scriptTags .= '<script type="text/javascript" src="' . $source . '" charset="'.$this->_charset.'"></script>'.PHP_EOL;

            if($this->uiIsEnabled()) {
                $uiPath = $this->_getJQueryUiLibraryPath();
                $scriptTags .= '<script type="text/javascript" src="'.$uiPath.'" charset="'.$this->_charset.'"></script>'.PHP_EOL;
            }

            if(ZendX_JQuery_View_Helper_JQuery::getNoConflictMode() == true) {
                $scriptTags .= '<script type="text/javascript">var $j = jQuery.noConflict();</script>'.PHP_EOL;
            }
        }

        if( ($this->getRenderMode() & ZendX_JQuery::RENDER_SOURCES) > 0) {
            foreach($this->getJavascriptFiles() AS $javascriptFile) {
                $scriptTags .= '<script type="text/javascript" src="' . $javascriptFile . '" charset="'.$this->_charset.'"></script>'.PHP_EOL;
            }
        }

        return $scriptTags;
    }

    private function __checkAndDefaults()
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $this->setRenderMode(ZendX_JQuery::RENDER_ALL & ~ZendX_JQuery::RENDER_LIBRARY);

        $this->_isXhtml = $this->view->doctype()->isXhtml();
        
        return true;
    }
    public function headLink()
    {
        return $this->__checkAndDefaults() ? $this->_renderStylesheets() : '';
    }

    public function headScript()
    {
        return $this->__checkAndDefaults() ? $this->_renderScriptTags() : '';
    }

    public function inlineScript()
    {
        return $this->__checkAndDefaults() ? $this->_renderExtras() : '';
    }
}