<?php

class HM_Form_Channel extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);

        $this->addElement('hidden', 'id', array(
            'required' => false,
            'Filters' => array(
                'Int'
            )
        ));
        
        $this->addElement('text', 'name', array(
            'label' => _('Название канала'),
            'Required' => true,
            'Validators' => array(
                array(
                    'validator' => 'StringLength',
                    'options' => array('max' => 255, 'min' => 3)
                )
            )
        ));
        
        $this->addElement('UiMultiSelect', 'users',
            array(
                'Label' => '',
                'Required' => false,
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array(
                    'Int'
                ),
                'jQueryParams' => array(
                    'remoteUrl' => $this->getView()->url(array(
                        'module' => 'chat', 
                        'controller' => 'index', 
                        'action' => 'users-list',
                        'ajax' => true
                    ), null, null)
                ),
                'class' => 'multiselect'
            )
        );
        
        $this->addElement('RadioGroup', 'date_access', array(
            'Label' => '',
            'MultiOptions' => HM_Chat_ChatChannelsModel::getDateTypes(),
            'form' => $this,
            'dependences' => array(HM_Chat_ChatChannelsModel::TIMETYPE_FREE => array(),
                                   HM_Chat_ChatChannelsModel::TIMETYPE_DATES => array('start_date', 'end_date'),
                                   HM_Chat_ChatChannelsModel::TIMETYPE_TIMES => array('current_date', 'time')
                             ),
             'onClick' => "if(this.value == 2) { $('#time-slider').slider('enable'); } else { $('#time-slider').slider('disable'); }"
        ));
        $this->addElement('DatePicker', 'start_date', array(            
            'Label' => _('Дата начала работы канала'),
            'Required' => false,
            'value' => date("d.m.Y"),
            'dateFormat' => 'dd.mm.yy'
        ));
        $this->addElement('DatePicker', 'end_date', array(            
            'Label' => _('Дата конца работы канала'),
            'Required' => false,
            'value' => date("d.m.Y"),
            'dateFormat' => 'dd.mm.yy'
        ));
        $this->addElement('DatePicker', 'current_date', array(
            'Label' => _('Дата'),
            'Required' => false,
            'value' => date("d.m.Y"),
            'dateFormat' => 'dd.mm.yy'
        ));
        $this->addElement('TimeSlider', 'time', array(
            'Label' => _('Время работы канала'),
            'Required' => false,
            'value' => array('9:00', '18:00'),
            'style' => 'background-color: #72A5C7; width: 500px;'
        ));
        
        $this->addElement('checkbox', 'show_history', array(
            'label' => _('Сохранять в системе протокол чата'),
            'value' => true
        ));

        $this->addDisplayGroup(
            array(
                  'id',
                  'name',
                  'show_history'
            ),
            'CommonChannelGroup',
            array('legend' => _('Общие свойства'))
        );

        $this->addDisplayGroup(
            array(
                'users'
            ),
            'UsersGroup',
            array('legend' => _('Список участников'))
        );
        
        $this->addDisplayGroup(
            array(
                'date_access', 
                'start_date',
                'end_date',
                'current_date',
                'time'
            ),
            'DateChannelGroup',
            array('legend' => _('Время доступности канала'))
        );
        
        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));
        
        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'chat', 'controller' => 'index', 'action' => 'index'))
        ));
        
        
        parent::init(); // required!
    }
    
    public function render(Zend_View_Interface $view = null)
    {
        $res = parent::render($view);
        $this->getView()->jquery()->addOnLoad("if($('input[name=date_access]:checked').val() != '2') { $('#time-slider').slider('disable'); }");
        return $res;
    }
}