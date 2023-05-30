<?php
class HM_View_Helper_Like extends HM_View_Helper_Abstract
{

    public function like($itemType, $itemId, $like)
    {
        $view = $this->view;
        
        $view->userId      = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        $view->containerId = "hm_like_{$itemType}_{$itemId}";
        $view->itemType    = $itemType;
        $view->itemId      = $itemId;
        
        if ($like) {
            $view->count_like    = (int) $like['count_like'];
            $view->count_dislike = (int) $like['count_dislike'];
            $view->vote          = (int) $like['user_vote'];
        } else {
            $view->count_like    = 0;
            $view->count_dislike = 0;
            $view->vote          = 0;
        }
        
        return $view->render('like/like.tpl');
    }
    
}