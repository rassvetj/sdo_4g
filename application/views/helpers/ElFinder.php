<?php
class HM_View_Helper_ElFinder extends HM_View_Helper_Abstract
{
    public function elFinder($name, $options = null, $params = null, $attribs = null)
    {
        $config = Zend_Registry::get('config');
        
        $locale = new Zend_Locale(Zend_Locale::findLocale());
        $language = $locale->getLanguage();
        
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/elfinder-1.2/js/elfinder.full.js'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/elfinder-1.2/css/elfinder.css'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/elfinder-1.2/css/elfinder-over.css'));
        $langFile = '/js/lib/elfinder-1.2/js/i18n/elfinder.'.$language.'.js';
        if (is_file(PUBLIC_PATH.$langFile)) {
            $this->view->headScript()->appendFile($this->view->serverUrl($langFile));
        }
        
        $finderName = 'finder-'.$name.'-'.$this->view->subjectName.$this->view->subjectId;
        $js = "$('#$finderName').elfinder({
                    url : ".Zend_Json::encode($options['connectorUrl']).",
                    lang : '$language',
                    view: 'list',
                    places: '',
                    toolbar : [
                        ['reload'],
                        ['select', 'open'],
                        ['mkdir', 'upload'],
                        ['rename', 'comment', 'copy', 'paste', 'rm'],
                        ['info']
                    ],
                    contextmenu : {
                        'cwd'   : ['reload', 'delim', 'mkdir', 'upload', 'paste', 'delim', 'info'],
                        'file'  : ['select', 'open', 'copy', 'cut', 'rm', 'rename', 'comment', 'info'],
                        'group' : ['copy', 'cut', 'rm', 'info']
                    }
                })";
        
        $this->view->jQuery()->addOnload($js);
        return '<div id="'.$finderName.'"></div>';
    }
}