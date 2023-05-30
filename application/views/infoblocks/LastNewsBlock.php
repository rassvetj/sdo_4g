<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_LastNewsBlock extends HM_View_Infoblock_ScreenForm
{
    const ITEMS_COUNT = 5;

    protected $id = 'lastnews';

    //Определяем класс отличный от других
    protected $class = 'scrollable';

    public function lastNewsBlock($title = null, $attribs = null, $options = null)
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');

        $data = $serviceContainer->getService('Option')->getOption('activity');
        $data = unserialize($data);

        $services = HM_Activity_ActivityModel::getTabActivities();

        $allow = false;
        if (is_array($data) && count($data)) {
            foreach($data as $key => $item) {
                if ($key == HM_Activity_ActivityModel::ACTIVITY_NEWS) {
                    $allow = true;
                    break;
                }
            }
        }

        $news = false;
        if ($allow) {
            $news = $serviceContainer->getService('News')->fetchAll("(subject_name = '' OR subject_name IS NULL) AND subject_id = 0", 'created DESC', self::ITEMS_COUNT);
        }
        $this->view->allow = $allow;
        $this->view->serviceName = $services[HM_Activity_ActivityModel::ACTIVITY_NEWS];
        $this->view->news = $news;

        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/lastnews/style.css');

        $content = $this->view->render('lastNewsBlock.tpl');
        return parent::screenForm($title, $content, $attribs);

    }
}