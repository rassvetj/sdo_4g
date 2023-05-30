<?php
class HM_Controller_Action_Orgstructure extends HM_Controller_Action_Extended
{

    protected $service      = 'Orgstructure';
    protected $idParamName  = 'org_id';
    protected $idFieldName  = 'soid';

    public function init()
    {
        /*
        $this->view->setExtendedFile('default.tpl');
        $this->view->setContextNavigation(
            'subject',
            array(
                $this->idName => $this->id
            )
        );
         *
         */
        parent::init();
    }

}