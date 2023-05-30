<?php

class HM_DataSheet_Deploy_Table extends HM_DataSheet implements HM_DataSheet_Deploy_Interface
{
    private $_template = 'table.tpl';

    public function getTemplate()
    {
        return $this->_template;
    }

    public function deploy()
    {
        parent::deploy();

        $scriptPath = realpath(APPLICATION_PATH.'/../library/HM/DataSheet/Template');
        $view = $this->getView();

        $paths = $view->getScriptPaths();
        $view->setScriptPath($scriptPath);

        $view->sheet = $this;

        $content = $view->render($this->getTemplate());

        $view->setScriptPath($paths);

        return $content;
    }
}