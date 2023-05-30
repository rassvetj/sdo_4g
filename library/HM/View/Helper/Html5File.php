<?php
class HM_View_Helper_Html5File extends Zend_View_Helper_FormFile
{
    public function html5File($name, $attribs = null, $options = null)
    {
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/fileupload/jquery.iframe-transport.min.js'));
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/fileupload/jquery.fileupload.js'));
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/fileupload/jquery.fileupload-ui.min.js'));
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/fileupload/jquery.fileupload-fileremove.js'));

        $params = array(
            'file_size_limit'   => $this->_convertIniToInteger(trim(ini_get('upload_max_filesize'))),
            'file_types'        => '*.*',
            'file_sample'       => null,
            'file_upload_limit' => 1,
            'upload_url'        => $this->view->url(array('module' => 'file', 'controller' => 'upload', 'action' => 'index'))
        );

        foreach($params as $param => $value) {
            if (isset($options[$param])) {
                $params[$param] = $options[$param];
            }
        }

        if (!$params['file_upload_limit'] || $params['file_upload_limit'] < 0) {
            $params['file_upload_limit'] = 0;
        }
        if (!$params['file_size_limit'] || $params['file_size_limit'] < 0) {
            $params['file_size_limit'] = $this->_convertIniToInteger(trim(ini_get('upload_max_filesize')));
        }

        $inputTypeFileAttrs = array('id' => $this->view->id($name));
        if ($params['file_upload_limit'] > 1 || $params['file_upload_limit'] === 0) {
            $inputTypeFileAttrs['multiple'] = 'multiple';
        }
        $inputTypeFileAttrs['accept'] = implode(',', $this->_getMimeTypes($params['file_types']));

        $uniqId = uniqid();
        $formData = array(
            array('name' => 'uniqid', 'value' => $uniqId),
            array('name' => 'sessid', 'value' => session_id()),
            array('name' => 'ishtml', 'value' => 'yes')
        );
        /**
         * Добавлен callback - done для jquery ajax;
         * Забирается ссылка на удаление файла.
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 22 january 2012
         */
        $jsOptions = array(
            'url'            => $params['upload_url'],
            'dropZone'       => new Zend_Json_Expr('$("#" + '. Zend_Json::encode($inputTypeFileAttrs['id']) .').closest("dd.element").add("#" + '. Zend_Json::encode($inputTypeFileAttrs['id'].'-label') .')'),
            'formData'       => new Zend_Json_Expr('function (form) { return '.Zend_Json::encode($formData).'; }'),
            'filesContainer' => "#{$inputTypeFileAttrs['id']}-list",
            'dataType'       => 'json',
            'autoUpload'     => true,
            'errorMessages'  => array(
                'acceptFileTypes'  => _('Неверный тип файла'),
                'maxFileSize'      => _('Превышен макимально допустимый размер файла'),
                'maxNumberOfFiles' => _('Уже добавлено максимальное количество файлов'),
            ),
            'done' => new Zend_Json_Expr("function(data,xhr){
                for(var i = 0;i < xhr.result.length; i++){
                    var item = xhr.result[i];
                    var li = $('.file-upload-list > li > .name:contains(' + item.name + ')').parent();
                    var link = li.find('.cancel-upload > a');
                    link.attr('href', item.delete_url);
                }
                $(this).fileupload('enable');
                // включаем кнопку сохранения формы
                $(this).closest('form').find('input[type=\"submit\"]').removeAttr('disabled');
                // почему-то enable-disable не пашет((
                $('#{$inputTypeFileAttrs['id']}').closest('.ui-button').find('.hm-fileinput-mask').remove();
            }"),
            'add' => new Zend_Json_Expr("function (e,data){
                var id;
                var name;
                $.each(data.files, function (index, file) {
                            var min = 1;
                            var max = 1000000;
                            file.id = Math.floor(Math.random() * (max - min + 1)) + min;
                            id = file.id;
                        });
                $.each(data.files, function (index, file) {
                            name = file.name;
                        });
                        
                var \$mask = $('<div class=\"hm-fileinput-mask\"></div>');
                \$mask.css({
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    backgroundColor: 'white',
                    opacity: 0.5,
                    cursor: 'default'
                });
                \$mask.on('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                });
                // отключаем кнопку сохранения формы
                $(this).closest('form').find('input[type=\"submit\"]').attr('disabled', 'disabled');
                // почему-то enable-disable не пашет((
                $('#{$inputTypeFileAttrs['id']}').closest('.ui-button').append(\$mask);
                $(this).fileupload('disable');
                
                $('#{$inputTypeFileAttrs['id']}-list').append(
                   '<li id=\"'+id+'-list\">'+
                        '<div class=\"name\">'+
                            name+
                        '</div>'+
                        '<div class=\"cancel-upload\">'+
                            '<a href=\"#\"></a>'+
                        '</div>'+
                        '<div class=\"progress\">'+
                            '<div class=\"bar\"  style=\"width:0px;\"></div>'+
                        '</div>'+
                    '</li>');
                data.submit()
                    .progress(function (e, data) {})
            }"),
            'progress' => new Zend_Json_Expr("function (e, data) {
                var id;
                $.each(data.files, function (index, file) {
                             id = file.id;
                        });
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#{$inputTypeFileAttrs['id']}-list #'+id+'-list .progress .bar').css(
                    'width',
                    progress + '%'
                );
            }"),
            'success' => new Zend_Json_Expr("function (e, data) {
                if(data == 'success'){
                    $('#{$inputTypeFileAttrs['id']}-list .progress .bar').css(
                    'background-color', \"#80bd90\");
                    $('#{$inputTypeFileAttrs['id']}-list .progress .bar').css(
                    'width',
                    100 + '%');
                }else{
                    $('#{$inputTypeFileAttrs['id']}-list .progress .bar').not('[width=\"100%\"]').css(
                    'background-color', \"#f37746\");
                }
            }"),
        );
        if ($params['file_upload_limit'] !== 0) {
            $jsOptions['maxNumberOfFiles'] = $params['file_upload_limit'];
        }
        if ($params['file_size_limit'] !== 0) {
            $jsOptions['maxFileSize'] = $params['file_size_limit'];
        }
        if ($params['file_types']) {
            $jsOptions['acceptFileTypes'] = new Zend_Json_Expr($this->_getTypesRegEx($params['file_types']));
        }

        $content  = '<span class="els-masked-inputfile ui-button"><span>'._("Обзор").'</span>'.$this->view->formFile($name, $inputTypeFileAttrs).'</span>';
        $content .= '<span class="els-inputfile-infoblock">';
        $content .= '<span class="els-inputfile-maxfilesize">'._("Максимальный размер загружаемого файла").': '.$this->_toByteString($params['file_size_limit']).'</span>';
        if ($params['file_types']) {
            $extensions = HM_Mime_Info::getExtensionsArray($params['file_types']);
            if (!empty($extensions)) {
                $content .= '<span class="els-inputfile-acceptfiletypes">'._("Разрешённые типы файлов").': '.implode(', ', $extensions).'</span>';
            }
        }
        if ($params['file_upload_limit'] !== 0) {
            $content .= '<span class="els-inputfile-maxnumberoffiles">'._("Допустимое количество файлов").': '.$params['file_upload_limit'].'</span>';
        }
        if (!empty($params['file_sample'])) {
            $content .= '<span><a href="' . $params['file_sample'] . '" target="_blank">'._("Пример файла").'</a></span>';
        }
        $content .= '</span>';
        
        $populate = '';
        $attribs = is_array($attribs) ? $attribs : array($attribs);
        foreach ($attribs as $attrib) {
            if (!is_a($attrib, 'HM_File_FileModel')) continue;
            $fileId = $attrib->getId();
            $displayName = $attrib->getDisplayName();
            $displayName = ($url = $attrib->getUrl()) ? "<a href='{$url}' target='_blank'>{$displayName}</a>" : $displayName; 
            $populate .= '
            <li id="li-populate-' . $fileId . '">
                <input type="hidden" name="' . $name . '-populate[]" id="' . $name . '-populate-' . $fileId . '" value="' . $fileId . '">
                <div>' . $displayName . '</div>
                <div class="cancel-populate" id="cancel-populate-' . $fileId . '">
                    <a href="#"></a>
                </div>
                <div class="progress">
                    <div class="bar" style="width:100%; background-color: #80bd90"></div>
                </div>                
            </li>';
            $jsOptions['maxNumberOfFiles']--;
        }
        $jsOptions['maxNumberOfFiles'] = max($jsOptions['maxNumberOfFiles'], 0);
        
        $content .= '
        <ul id="'.$inputTypeFileAttrs['id'].'-list" class="file-upload-list file-populate">' . $populate . '</ul>
        <ul id="'.$inputTypeFileAttrs['id'].'-list" class="file-upload-list"></ul>';
        $content .= $this->view->formHidden($name, $uniqId);

        // TODO: apply draghover plugin on dd.element
        $fileUploaderId = Zend_Json::encode($inputTypeFileAttrs['id']);
        $this->view->jQuery()->addOnLoad('$("#" + ' . $fileUploaderId . ').fileupload('.Zend_Json::encode(
            $jsOptions,
            false,
            array('enableJsonExprFinder' => true)
        ).');');
        
        if (!empty($populate)) {
            $this->view->jQuery()->addOnLoad('$(".cancel-populate").click(function(){
                var id = $(this).attr("id");
                var fileId = id.replace("cancel", "' . $name . '");
                $("#" + fileId).val(0);
                var liId = id.replace("cancel", "li");
                $("#" + liId).css("display", "none");
                $("#" + ' . $fileUploaderId . ').fileupload("option", "maxNumberOfFiles", $("#" + ' . $fileUploaderId . ').fileupload("option", "maxNumberOfFiles") + 1);
                return false;
            });');
        }
        
        return $content;
    }

    private function _toByteString($size)
    {
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        for ($i=0; $size >= 1024 && $i < 9; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . $sizes[$i];
    }

    private function _convertBytes($value)
    {
        if (is_numeric( $value )) {
            return $value;
        } else {
            $value_length = strlen($value);
            $qty = substr($value, 0, $value_length - 1);
            $unit = strtolower(substr($value, $value_length - 1));
            switch ( $unit ) {
            case 'k':
                $qty *= 1024;
                break;
            case 'm':
                $qty *= 1048576;
                break;
            case 'g':
                $qty *= 1073741824;
                break;
            }
            return $qty;
        }
    }

    private function _convertIniToInteger($setting)
    {
        return $this->_convertBytes($setting);
    }
    
    private function _getMimeTypes($extensions)
    {
        return HM_Mime_Info::extensionsToMime($extensions);
    }
    
    private function _getTypesRegEx($extensions)
    {
        $extensions = HM_Mime_Info::getExtensionsArray($extensions);
        $extensionsFiltered = array();

        foreach ($extensions as $extension) {
            if ($extension == '*') {
                $extensionsFiltered[] = '[^\.]*';
            } else {
                $extensionsFiltered[] = preg_quote($extension);
            }
        }

        if (!empty($extensions)) {
            return '/\.('.implode('|', $extensionsFiltered).')$/i';
        }
    }
}