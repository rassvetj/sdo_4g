<?php

class HM_View_Helper_WikiEditorControls extends Zend_View_Helper_FormElement
{
    public function wikiEditorControls($id, $value = null, array $attribs = array())
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
    
        $config = Zend_Registry::get('config');
        $this->view->headLink()->appendStylesheet($config->url->base.'css/wiki.css');
        $this->view->headScript()->appendFile($config->url->base.'js/wiki-editor-controls.js');
        $this->view->headScript()->appendFile( $config->url->base.('js/lib/mathml/ASCIIMathMLwFallback.js') );
        $locale = new Zend_Locale(Zend_Locale::findLocale());
        $language = $locale->getLanguage();
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/elfinder-1.2/js/elfinder.full.js'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/elfinder-1.2/css/elfinder.css'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/elfinder-1.2/css/elfinder-over.css'));
        $langFile = '/js/lib/elfinder-1.2/js/i18n/elfinder.'.$language.'.js';
        if (is_file(PUBLIC_PATH.$langFile)) {
            $this->view->headScript()->appendFile($this->view->serverUrl($langFile));
        }
        
        $js = "new WikiEditor('$id', {connectorUrl: '".$attribs['connectorUrl']."', lang: '$language'})";
        $this->view->jQuery()->addOnload($js);
        
        $attribs['disableLinks'] = isset($attribs['disableLinks']) ? $attribs['disableLinks'] : false;
        
        $html = '<div class="hm-wiki-editor">   
                    <div tabindex="200" class="button but_strong" title="'._('Жирный').'"></div>
                    <div tabindex="200" class="button but_em" title="'._('Курсив').'"></div>
                    <div tabindex="200" class="button but_ins" title="'._('Подчеркнутый').'"></div>
                    <div tabindex="200" class="button but_del" title="'._('Зачеркнутый').'"></div>
                    <div tabindex="200" class="button but_code" title="'._('Вставка кода').'"></div>
                    <div class="ediorSpacer">&nbsp;</div>
                    <div tabindex="200" class="button but_h1" title="'._('Заголовок 1').'"></div>
                    <div tabindex="200" class="button but_h2" title="'._('Заголовок 2').'"></div>
                    <div tabindex="200" class="button but_h3" title="'._('Заголовок 3').'"></div>
                    <div class="ediorSpacer">&nbsp;</div>
                    <div tabindex="200" class="button but_ul" title="'._('Маркированный список').'"></div>
                    <div tabindex="200" class="button but_ol" title="'._('Нумерованный список').'"></div>
                    <div class="ediorSpacer">&nbsp;</div>
                    <div tabindex="200" class="button but_bq" title="'._('Цитата').'"></div>
                    <div tabindex="200" class="button but_unbq" title="'._('Удалить цитату').'"></div>
                    <div tabindex="200" class="button but_pre" title="'._('Заранее форматированный текст').'"></div>
                    <div class="ediorSpacer">&nbsp;</div>';
        if(!$attribs['disableLinks']) {
            $html .= '    <div tabindex="200" class="button but_link" title="'._('Ссылка на страницу Wiki').'"></div>';
        }
        $html .= '    <div tabindex="200" class="button but_img" title="'._('Вставка изображения').'"></div>
                <div class="ediorSpacer"></div>';

        $html .= '    <div tabindex="200" class="button but_swf" title="'._('Вставка swf-файла').'"></div>
                      <div tabindex="200" class="button but_video" title="'._('Вставка видео-файла').'"></div>
                 <div class="ediorSpacer"></div>';

        $html .= '    <div tabindex="200" class="button but_math" title="'._('Новая формула').'"></div>
                      <div tabindex="200" class="button but_math_element" title="'._('Вставка элемента формулы').'"></div>';
        $html .='</div>
                <div class="spacer"></div>';
        
        $html .= '<textarea name="' . $this->view->escape($id) . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . $this->_htmlAttribs($attribs) . '>'
                . $this->view->escape($value) . '</textarea>';
        return $html;
    }
}
