<?php
class BlockingMessageController extends HM_Controller_Action
{
    protected $user = false;

    public function init()
    {
        parent::init();

        $this->user  = $this->getService('User')->getCurrentUser();
    }
}