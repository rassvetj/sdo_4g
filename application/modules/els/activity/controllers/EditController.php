<?php

class Activity_EditController extends HM_Controller_Action {


    public function indexAction()
    {

        $form = new HM_Form_Services();
        $request = $this->_request;
        if ($request->isPost())
        {
            if ($form->isValid($request->getParams()))
            {
                $collaboration = HM_Activity_ActivityModel::getTabActivities(false);
                $collaborationUrls = HM_Activity_ActivityModel::getCollaborationUrls();
                foreach($collaborationUrls as $key => $val){
                    $collaboration[$key]= array('name' => $collaboration[$key]);
                    if(is_array($val)){
                        $collaboration[$key]['url'] = $this->view->url($val);
                    }else{
                        $collaboration[$key]['url'] = $val;
                    }
                }

                $result = array();
                foreach($form->getValue('activity') as $key => $value){
                    if(isset($collaboration[$value])){
                        $result[$value] = $collaboration[$value];
                    }

                }

                $this->getService('Option')->setOption('activity', serialize($result));

                //$this->_flashMessenger->addMessage(_('Параметры отображения сервисов взаимодействия успешно изменены.'));
                $this->_redirector->gotoSimple('index', 'index', strtolower($this->service));


            } else
            {

            }
        } else
        {

            $data = $this->getService('Option')->getOption('activity');

            $data = unserialize($data);
            $services = HM_Activity_ActivityModel::getTabActivities();

            $res= array();
            if (is_array($data)) {
                foreach($data as $key => $val){
                    $res[] = $key;
                }
            }

            $form->populate(array('activity' => $res)
            );

        }

        $this->view->form = $form;
    }




}