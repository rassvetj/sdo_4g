<?php
class HM_View_Helper_ServerFile extends Zend_View_Helper_FormHidden
{
    public function serverFile($name, $value = null, array $attribs = null)
    {
        if (!empty($value)) {
            $preview = $value;
        }
        if (!empty($attribs['preview'])) {
            $preview = $attribs['preview'];
            unset($attribs['preview']);
        }
        if (!empty($preview)) {
            $info = pathinfo($preview);
            if (in_array($info['extension'], array('jpg', 'jpeg', 'gif', 'bmp', 'png'))) {
                $preview = '<img src="'.$preview.'">';
            } else {
                $preview = '<p>'.$preview.'</p>';
            }
        }
        $content = $this->formHidden($name, $value, $attribs);

		$btn_view = _('Обзор');
        $btn_reset = _('Убрать');
        $content .= '<div class="filePreview">'.$preview.'</div><input type="button" value="'.$btn_view.'"><input type="button" value="'.$btn_reset.'">';

        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/elfinder-1.2/js/elfinder.full.js'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/elfinder-1.2/css/elfinder.css'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/elfinder-1.2/css/elfinder-over.css'));

        $js = <<<JS
jQuery(document).ready(function(){
    var input = $('input[name="$name"]');
    var filePreview = input.parent().find('.filePreview');
    input.parent().find('input[value="$btn_reset"]').on('click', function(){
        input.val('');
        filePreview.html('');
    });
    input.parent().find('input[value="$btn_view"]').on('click', function(){
        $('<div id=\"myelfinder\" />').elfinder({
            url : '/storage/index/elfinder',
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
            editorCallback : function(path, fileObj) {
                var imgExts = ['jpg', 'jpeg', 'gif', 'bmp', 'png'];
                var extension = path.substr( (path.lastIndexOf('.') +1) );
                path = path.replace(/^.*\/\/[^\/]+/, '');
                input.val(path);
                if($.inArray(extension, imgExts) == -1) {
                    filePreview.html('<p>'+path+'</p>');
                } else {
                    filePreview.html('<img src="'+path+'">');
                }
            }
        });
    });
});
JS;
        $this->view->inlineScript(Zend_View_Helper_HeadScript::SCRIPT)->appendScript($js);

        return $content;
    }

}