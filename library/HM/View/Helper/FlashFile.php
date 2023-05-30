<?php
class HM_View_Helper_FlashFile extends Zend_View_Helper_FormFile
{
    public function flashFile($name, $attribs = null, $options = null)
    {
        $this->view->headScript()->appendFile($this->view->baseUrl('js/swfupload/swfupload.js'));
        $this->view->headScript()->appendFile($this->view->baseUrl('js/lib/jquery/jquery.swfupload.js'));
        $this->view->headScript()->appendFile($this->view->baseUrl('js/lib/jquery/jquery.flashfile.js'));
        $this->view->headLink()->appendStylesheet($this->view->baseUrl('css/content-modules/swfupload.css'));

        $content =
        $this->view->formHidden($name, uniqid(), array('id' => $name.'-uniqid'))                
        .'<div id="'.$name.'" class="swfupload-control">
        <input type="button" id="'.$name.'-button" />
        <ol id="log"></ol>
        </div>';

        $upload_url = $this->view->baseUrl($this->view->url(array('module' => 'file', 'controller' => 'upload', 'action' => 'index')));
        $file_post_name = $name;
        $file_size_limit = $this->_convertIniToInteger(trim(ini_get('upload_max_filesize')));
        $file_types = '*.*';
        $file_types_description = _('Файлы');
        $file_upload_limit = 5;
        $flash_url = $this->view->baseUrl('js/swfupload/swfupload.swf');
        $button_image_url = $this->view->baseUrl('js/swfupload/wdp_buttons_upload_114x29.png');
        $button_width = 76;
        $button_height = 29;
        $button_placeholder = "$('#$name-button')[0]";
        $debug = Zend_Registry::get('config')->form->file->debug;

        $params = array(
            'file_post_name',
            'file_size_limit',
            'file_types',
            'file_types_description',
            'file_upload_limit',
            'debug',
            'flash_url',
            'button_image_url',
            'button_width',
            'button_height',
            'button_placeholder',
            'upload_url'
        );

        foreach($params as $v) {
            if (isset($options[$v])) {
                $$v = $options[$v];
            }
        }


        $js = "
        $('#".$name."').flashfile({
            upload_url: '$upload_url',
            file_post_name: '$file_post_name',
            file_size_limit : '$file_size_limit',
            file_types : '$file_types',
            file_types_description : '$file_types_description',
            file_upload_limit : $file_upload_limit,
            flash_url : '$flash_url',
            button_image_url : '$button_image_url',
            button_width : $button_width,
            button_height : $button_height,
            button_placeholder : $button_placeholder,
            session_id: '".session_id()."',
            debug: $debug
        })

        ";

        $this->view->jQuery()->addOnLoad($js);

        return $content;
    }

    private function _convertIniToInteger($setting)
    {
        if (!is_numeric($setting)) {
            $type = strtoupper(substr($setting, -1));
            $setting = (integer) substr($setting, 0, -1);

 
        }

        return (integer) $setting;
    }

}