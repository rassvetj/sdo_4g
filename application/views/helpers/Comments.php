<?php
class HM_View_Helper_Comments extends HM_View_Helper_Abstract
{
    /**
     * @param $params
     */
    public function comments($comments, $comments_count, $linksUrl, $isFullView, $commentForm, $module = 'blog')
    {


        $config = Zend_Registry::get('config');
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/content-modules/comments.css'));
        if ($comments !== null && $comments instanceof \Traversable && sizeof($comments) > 0) {
            foreach($comments as $comment) {
                $author = Zend_Registry::get('serviceContainer')->getService('User')->find($comment->user_id)->current();
                if ( $author->MID ) {
                    $comment->author_avatar = $config->url->base.$author->getPhoto();
                    $comment->author = $author->getName();
                } else {
                    $comment->author_avatar = $config->url->base.$config->src->default->photo;
                    $comment->author = _('Пользователь был удален');
                }

            }
        }
        $view = $this->view;
        $view->comments_count = $comments_count;
        $view->linksUrl = $linksUrl;
        $view->isFullView = $isFullView;
        $view->comments = $comments;
        $view->form = $commentForm;
        $view->canComment = Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed("mca:{$module}:index:comment");
        return $view->render('comments.tpl');
    }
}