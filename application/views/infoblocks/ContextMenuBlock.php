<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_ContextMenuBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'contextMenu';

    public function contextMenuBlock($title = null, $attribs = null, $options = null)
    {
        if (empty($options['partition'])) {
            return false;
        }

        // $options['view'] ��������� ����� �������� ���������������� HM_Navigation

        $locale = Zend_Registry::get('Zend_Locale');
        $localePath = ($locale != 'ru_RU') ? '/../data/locales/' . $locale : '';
        $config = new HM_Config_Xml(APPLICATION_PATH . /*$localePath .*/ '/settings/context.xml', $options['partition']);

        if($config == null){
            return false;
        }
        $navigation = new HM_Navigation($config, $this->getService('Acl'), $options['substitutions']);

        $this->view->getSubNavigation($navigation, $options['partition'], $options['substitutions']);
        $this->view->menu = $navigation;
        
        $content = $this->view->render('contextMenuBlock.tpl');

        if($title == null) {
            return $content;
        }
        return parent::screenForm($title, $content, $attribs);

    }
}