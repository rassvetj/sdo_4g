<?php
require_once "ZendX/JQuery/View/Helper/UiWidget.php";

class HM_View_Helper_JqTinyMce extends ZendX_JQuery_View_Helper_UiWidget
{
    public function JqTinyMce($id, $value = null, array $params = array(), array $attribs = array())
    {
        // default settings
        
        $defaults = Zend_Registry::get('config')->wysiwyg->params->toArray();
        $toolbarOptions = Zend_Registry::get('config')->wysiwyg->options->toArray();
        $toolbar = isset($attribs['toolbar'])
            ? $attribs['toolbar']
            : (isset($params['toolbar']) ? $params['toolbar'] : $defaults['toolbar']);
        $config = $defaults;
        
        if (isset($toolbar) and isset($toolbarOptions[ $toolbar ])) {
            foreach($toolbarOptions[ $toolbar ] as $name => $paramValue) {
                $config[$name] = $paramValue;
            }
        }
        
        if (isset($config['script_load_url'])) {
            if (is_array($config['script_load_url'])) {
                foreach ($config['script_load_url'] as $key => $val) {
                    $config['script_load_url'][$key] = $this->view->serverUrl($val);
                }
            } else {
                $config['script_load_url'] = array(
                    $this->view->serverUrl($config['script_load_url'])
                );
            }
        }
        if (isset($config['content_css'])) {
            $config['content_css'] = $this->view->serverUrl($config['content_css']);
        }
        if (isset($config['connectorUrl'])) {
            $config['connectorUrl'] = $this->view->serverUrl($config['connectorUrl']);
        }
        if (isset($config['popup_css'])) {
            $config['popup_css'] = $this->view->serverUrl($config['popup_css']);
        }

        foreach($params as $name => $paramValue) {
            $config[$name] = $paramValue;
        }
        foreach($attribs as $name => $paramValue) {
            $config[$name] = $paramValue;
        }

        $scriptUrls = $config['script_load_url'];
        unset($config['script_load_url']);
        $locale = new Zend_Locale(Zend_Locale::findLocale());
        $config['language'] = $locale->getLanguage();
        $config['elements']="nourlconvert";
        $config['convert_urls']=false;
        if ($config['fmAllow']) {
            $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/elfinder-1.2/js/elfinder.full.js'));
            $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/elfinder-1.2/css/elfinder.css'));
            $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/elfinder-1.2/css/elfinder-over.css'));
            $langFile = '/js/lib/elfinder-1.2/js/i18n/elfinder.'.$config['language'].'.js';
            if (is_file(PUBLIC_PATH.$langFile)) {
                $this->view->headScript()->appendFile($this->view->serverUrl($langFile));
            }
            $config['file_browser_callback'] = new Zend_Json_Expr("function (field_name, url, type, win) {
                $('<div/>').attr('id', '{$this->view->id('elfinder')}').elfinder({
                    url: ".Zend_Json::encode($config['connectorUrl']).",
                    lang: '{$config['language']}',
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
                    },
                    dialog : { width : 900, modal : true, title : ".Zend_Json::encode(_("Файловое хранилище"))." },
                    closeOnEditorCallback : true,
                    editorCallback: function (url) {
                        win.document.forms[0].elements[field_name].value = url;
                    }
                });
            }");
        }
        unset($config['fmAllow']);
        unset($config['connectorUrl']);
        unset($config['toolbar']);
        
        $hasStyleselect = false;
        for ($i = 1; $i < 5; ++$i) {
            if (isset($config[ "theme_advanced_buttons$i" ]) && strpos($config[ "theme_advanced_buttons$i" ], "styleselect") !== FALSE) {
                $hasStyleselect = true;
                break;
            }
        }
        
        if ($hasStyleselect) {
            // This should be configurable
            $config['style_formats'] = array(
                array('title' => _('Абзац'), 'block' => 'p'),
                array('title' => _('Заголовок 1'), 'block' => 'h1'),
                array('title' => _('Заголовок 2'), 'block' => 'h2'),
                array('title' => _('Заголовок 3'), 'block' => 'h3'),
                array('title' => _('Преформатированный текст'), 'block' => 'pre'),
                array('title' => _('Список определений'), 'block' => 'dl'),
                array('title' => _('Определяемый термин'), 'block' => 'dt'),
                array('title' => _('Определение термина'), 'block' => 'dd'),
                array('title' => _('Адрес'), 'block' => 'address'),
                array('title' => _('Блочная цитата'), 'block' => 'blockquote'),
                array('title' => _('Строчные стили')),
                array('title' => _('Цитата'), 'inline' => 'q'),
                array('title' => _('Сноска'), 'inline' => 'cite'),
                array('title' => _('Аббревиатура'), 'inline' => 'abbr'),
                array('title' => _('Акроним'), 'inline' => 'acronym'),
                array('title' => _('Термин'), 'inline' => 'dfn'),
                //array('title' => 'Emphasis', 'inline' => 'em'),
                //array('title' => 'Strong emphasis', 'inline' => 'strong'),
                //array('title' => 'Sample output', 'inline' => 'samp'),
                //array('title' => 'Keyboard', 'inline' => 'kbd'),
                //array('title' => 'Variable', 'inline' => 'var'),
                array('title' => _('Программный код'), 'inline' => 'code')
            );
        }
        
        unset($attribs['lang']);
        unset($attribs['language']);
        unset($attribs['connectorUrl']);
        unset($attribs['fmAllow']);
        unset($attribs['toolbar']);
        if (isset($config['width'])) {
            $attribs['width'] = $config['width'];
        }
        if (isset($config['height'])) {
            $attribs['height'] = $config['height'];
        }
        if (isset($config['id'])) {
            $attribs['id'] = $config['id'];
        }
        $attribs = $this->_prepareAttributes($id, $value, $attribs);
        $config = ZendX_JQuery::encodeJson($config);

        $js = sprintf('%s("#%s").tinymce(%s);',
                ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
                $attribs['id'],
                $config
        );

        $this->jquery->addOnLoad($js);
        foreach ($scriptUrls as $script) {
            $this->jquery->addJavascriptFile($script);
        }
        // TODO: move to stylesheets!
        $this->view->headStyle()->appendStyle("table.mceToolbar tr { display: block; white-space: normal; } table.mceToolbar td { display: block; float: left; white-space: nowrap; }");

        return $this->view->formTextarea($id, $value, $attribs);
    }
}