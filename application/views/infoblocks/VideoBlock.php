<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_VideoBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'video';

    public function videoBlock($title = null, $attribs = null, $options = null)
    {
        //$url = $this->view->url(array('module'=> 'resource', 'controller' => 'index', 'action' => 'data', 'resource_id' => $resource->resource_id), null, true);
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/mediaelement/mediaelement-and-player.min.js'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/mediaelement/mediaelementplayer.css'));
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/video/style.css');
        $this->view->videos = $this->getService('FilesVideoblock')->getVideoList();
        $this->view->showEditLink= $isModerator = $this->getService('Activity')->isUserActivityPotentialModerator(
            $this->getService('User')->getCurrentUserId()
        );
        $content = $this->view->render('videoBlock.tpl');
        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}
/**
 * Created by JetBrains PhpStorm.
 * User: sitnikov
 * Date: 29.05.13
 * Time: 14:19
 * To change this template use File | Settings | File Templates.
 */