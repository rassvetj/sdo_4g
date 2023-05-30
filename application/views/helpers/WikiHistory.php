<?php
class HM_View_Helper_WikiHistory extends HM_View_Helper_Abstract
{
    public function wikiHistory($name, $otions = null, $params = null, $attribs = null)
    {
        $compareUrl = $this->view->url(array(
            'module' => 'wiki',
            'controller' => 'index',
            'action' => 'compare',
            'subject' => $this->view->subjectName, 
            'subject_id' => $this->view->subjectId
        ));
        $restoreUrl = $this->view->url(array(
            'module' => 'wiki',
            'controller' => 'index',
            'action' => 'restore'
        ));
        $js = "var compareUrl = '".$compareUrl."'; var restoreUrl = '".$restoreUrl."';
        $('#apply').click(function(){
            var checked = $('.version-ctrl:checked').size();
            if(checked == 0) {
                alert('"._('Выберите версии страницы')."');
                return;
            }
            switch($('#actions').val()) {
                case 'compare':
                    if(checked == 1) {
                        alert('"._('Для сравнения необходимо отметить галочками две версии страницы')."');
                        return;
                    }
                    if(checked > 2) {
                        alert('"._('Для сравнения необходимо отметить галочками две версии страницы')."');
                        $('.version-ctrl:checked').each(function(){
                            if($('.version-ctrl:checked').size() > 2) {
                                $(this).attr('checked', false);
                            }
                        });
                        return;
                    }
                    var ids = $('.version-ctrl:checked');
                    $('#act_id1').val(ids.get(0).id.slice(4));
                    $('#act_id2').val(ids.get(1).id.slice(4));
                    $('#actionForm').attr('action', compareUrl).submit();
                break;
                case 'restore':
                    if(checked > 1) {
                        alert('"._('Для восстановления необходимо отметить галочкой одну версию страницы')."');
                        return;
                    }
                    var version = $('.version-ctrl:checked');
                    $('#act_version').val(version.get(0).id.slice(4));
                    $('#actionForm').attr('action', restoreUrl).submit();
                break;
                default:
                    alert('"._('Выберите действие')."');
                    return;
            }
        });";
        
        $this->view->jQuery()->addOnload($js);
        
        return $this->view->render('wikihistory.tpl');
    }
}