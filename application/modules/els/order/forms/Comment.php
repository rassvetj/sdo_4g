<?php
class HM_Form_Comment extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('comment');
        $this->setAction($this->getView()->url(array('module' => 'order', 'controller' => 'list', 'action' => 'reject-by', 'subject_id' => $this->getParam('subject_id', 0))));

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'order', 'controller' => 'list', 'action' => 'index', 'subject_id' => $this->getParam('subject_id', 0)))
        ));

        $this->addElement('hidden', 'postMassIds_grid', array(
            'required' => true,
            'filters' => array(
            )
        ));

        $this->addElement('hidden', 'subject_id', array(
            'required' => false,
            'filters' => array(
                'Int'
            )
        ));


        $this->addElement('textarea', 'comments_all', array(
            'Label' => _('Комментарий'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',255,0)
            )
        ));

        //$this->getElement('message')->addFilter(new HM_Filter_Utf8());

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'subject_id',
                'comments_all'
            ),
            'rejectGroup1',
            array('legend' => _('Всем'))
        );

        $elements = array();
        $ids = explode(',', $this->getParam('postMassIds_grid', 0));
        if (count($ids)) {
            $orders = $this->getService('Claimant')->findDependence(array('User', 'Subject'), $ids);
            foreach($orders as $order) {
                if ($order->status == HM_Role_ClaimantModel::STATUS_REJECTED) continue;
                $name = 'comments_'.$order->SID;
                $elements[] = $name;
                $this->addElement('textarea', $name, array(
                    'Label' => sprintf('%s, %s', $order->getUser()->getName(), $order->getSubject()->name),
                    'Required' => false,
                    'Validators' => array(
                        array('StringLength',255,3)
                    )
                ));
            }
        }

        if ($elements) {
            $this->addDisplayGroup(
                $elements,
                'rejectGroup2',
                array('legend' => _('Персонально'))
            );
        }

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}

}