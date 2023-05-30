<?php
class News_ViewController extends HM_Controller_Action
{
    public function indexAction()
    {
        $newsId = (int) $this->_getParam('news_id', 0);
        $news = $this->getService('News')->getOne($this->getService('News')->find($newsId));

        $this->view->news = $news;
    }
}