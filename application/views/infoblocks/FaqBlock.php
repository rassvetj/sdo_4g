<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_FaqBlock extends HM_View_Infoblock_ScreenForm
{
    const ITEMS_COUNT = 5;

    protected $id = 'faq';

    //Определяем класс отличный от других
    protected $class = 'scrollable';

    public function faqBlock($title = null, $attribs = null, $options = null)
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');

        $order = 'RAND()';

        if ($serviceContainer->getService('Faq')->getSelect()->getAdapter() instanceof Zend_Db_Adapter_Oracle) {
            $order = 'dbms_random.value';
        }
        $currentRole = $this->getService('User')->getCurrentUserRole();
        if ($this->getService('Acl')->inheritsRole($currentRole, HM_Role_RoleModelAbstract::ROLE_ENDUSER)) $currentRole = HM_Role_RoleModelAbstract::ROLE_ENDUSER;
        if (
            $serviceContainer->getService('Acl')->inheritsRole($serviceContainer->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ADMIN)
            //in_array($serviceContainer->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_ADMIN))
        ) {
            $faqs = $serviceContainer->getService('Faq')->fetchAll(
                'published = 1',
                $order,
                self::ITEMS_COUNT
            );
        } else {
            $faqs = $serviceContainer->getService('Faq')->fetchAll(
                $serviceContainer->getService('Faq')->quoteInto('roles LIKE ?', '%'.$currentRole.'%').' AND published = \'1\'',
                $order,
                self::ITEMS_COUNT
            );
        }

        $this->view->faqs = $faqs;

        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/faq/style.css');

        $content = $this->view->render('faqBlock.tpl');
        return parent::screenForm($title, $content, $attribs);

    }
}