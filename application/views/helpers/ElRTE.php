<?php
class HM_View_Helper_ElRTE extends HM_View_Helper_Abstract
{
    public function elRTE($id, $value = null, $attribs = null)
    {
        $config = Zend_Registry::get('config');
        
        $defaults = $config->wysiwyg->params;
        foreach($defaults as $name => $paramValue) {
            if (!isset($attribs[$name])) {
                $attribs[$name] = $paramValue;
            }
        }

        if (isset($attribs['toolbar'])) {
            if ($config->wysiwyg->options->{$attribs['toolbar']}) {
                foreach($config->wysiwyg->options->{$attribs['toolbar']} as $name => $paramValue) {
                    if ($name == 'connectorUrl') {
                        $paramValue = $this->view->serverUrl($paramValue);
                    }
                    $attribs[$name] = $paramValue;
                }
            }
        }
        
        $this->view->headScript()->appendFile($config->url->base.'js/lib/elrte-1.3/js/elrte.min.js');
        $langFile = $config->url->base.'js/lib/elrte-1.3/js/i18n/elrte.'.$attribs['lang'].'.js';
        if(!file_exists(APPLICATION_PATH . '/../public' . $langFile)) {
            $langFile = $config->url->base.'js/lib/elrte-1.3/js/i18n/elrte.ru.js';
        }
        $this->view->headScript()->appendFile($langFile);
        $this->view->headLink()->appendStylesheet($config->url->base.'js/lib/elrte-1.3/css/elrte.full.css');
        $this->view->headLink()->appendStylesheet($config->url->base.'js/lib/elrte-1.3/css/elrte.full-over.css');

        $js = "elRTE.prototype.options.panels.blockquote = ['blockquote'];\n
               elRTE.prototype.options.panels.mhCopypasteTiny = ['pasteformattext', 'removeformat'];\n
               elRTE.prototype.options.panels.mhStyleTiny = ['bold', 'italic', 'underline', 'strikethrough'];\n
               elRTE.prototype.options.panels.mhFormatTiny = ['fontsize'];\n
               elRTE.prototype.options.panels.mhLinksTiny = ['link', 'unlink'];\n
               elRTE.prototype.options.panels.mhMediaTiny = ['image'];\n
               elRTE.prototype.options.toolbars.hmToolbarTiny = ['mhCopypasteTiny', 'undoredo', 'mhStyleTiny', 'alignment', 'mhFormatTiny', 'lists', 'mhLinksTiny', 'mhMediaTiny'];\n
               elRTE.prototype.options.toolbars.hmToolbarMaxi = ['copypaste', 'undoredo', 'elfinder', 'style', 'alignment', 'direction', 'colors', 'format', 'indent', 'lists', 'links', 'elements', 'media', 'tables'];\n";
        
        
        $connectorUrl = $attribs['connectorUrl'];
        unset($attribs['connectorUrl']);
        unset($attribs['id']);
        
        $attribs['cssfiles'] = $attribs['cssfiles']->toArray();
        foreach ($attribs['cssfiles'] as $key => $val) {
            $attribs['cssfiles'][$key] = $config->url->base.$val;
        }
        $params = ZendX_JQuery::encodeJson($attribs);
        $js .= "var opts = $params;\n";
        
        if($attribs['fmAllow']) {
        $this->view->headScript()->appendFile($config->url->base.'js/lib/elfinder-1.2/js/elfinder.full.js');
        $this->view->headLink()->appendStylesheet($config->url->base.'js/lib/elfinder-1.2/css/elfinder.css');
        $this->view->headLink()->appendStylesheet($config->url->base.'js/lib/elfinder-1.2/css/elfinder-over.css');
        $langFile = $config->url->base.'js/lib/elfinder-1.2/js/i18n/elfinder.'.$attribs['lang'].'.js';
        if(!file_exists(APPLICATION_PATH . '/../public' . $langFile)) {
            $langFile = $config->url->base.'js/lib/elfinder-1.2/js/i18n/elfinder.ru.js';
        }
        $this->view->headScript()->appendFile($langFile);
        $finderName = 'elrte-finder-'.$this->view->subjectName.$this->view->subjectId;
            $js .= "opts.fmOpen = function(callback) {
                    $('<div id=\"$finderName\" />').elfinder({
                    url : '$connectorUrl',
                        lang: '".$attribs['lang']."',
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
                        dialog : { width : 900, modal : true, title : '' },
                        closeOnEditorCallback : true,
                        editorCallback : callback
                    });
            }\n";
                }
        $js .= sprintf('%s("#%s").elrte(opts);',
            ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
            $id
        );

        
        $this->view->jQuery()->addOnload("setTimeout(function(){\n".$js."\n}, 200);");
        return '<textarea id="'.$id.'">'.$value.'</textarea>';
    }
}