<?php
class HM_Form_Evaluator extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('id', 'evaluate');
        $this->setAttrib('enctype', 'multipart/form-data');

        $request = Zend_Controller_Front::getInstance()->getRequest();

        if ($this->isAjaxRequest()) {
            $this->setAction(
                $this->getView()->url(
                    array(
                        'module' => $request->getModuleName(),
                        'controller' => $request->getControllerName(),
                        'action' => $request->getActionName(),
                        'referer_redirect' => 1
                    )
                )
            );
        }

        $lessonId = $request->getParam('lesson_id', 0);
        /** @var HM_Lesson_Evaluation_EvaluationSettingsService $lesSettings */
        $lesSettings = $this->getService('LessonEvaluationSettings');
        $evaluationSettings = $lesSettings->getEvaluationSettings($lessonId);

        $criterions = unserialize($evaluationSettings->criterions);
        //получаем описание критериев
        $criterionsDescr_collection = $this->getService('Criterion')->fetchAll();
        $criterionsDescr_id = array();
        $criterionsDescr_title = array();
        foreach ($criterionsDescr_collection as $criterion) {
            $criterionsDescr_id[$criterion->id] = $criterion->description;
            $criterionsDescr_title[$criterion->title] = $criterion->description;
        }

        //список элементов - критериев (оценка и название критерия)
        $criterionElements_mark = array();
        $criterionElements_title = array();
        //пользовательские критерии
        if ($evaluationSettings->scale_id) {
            $scaleValues = $this->getService('ScaleValue')->fetchAll(
                array('scale_id = ?' => (int)$evaluationSettings->scale_id)
            )->getList('value', 'text');

            $counter = 0;

            foreach($criterions as $criterionId => $criterionTitle) {
                $elName = 'criterion_mark_'.$counter;
                array_push($criterionElements_mark, $elName);
                array_push($criterionElements_title, $criterionTitle);
                $description = null;
                //находим описание по названию
                if ($criterionsDescr_title[$criterionTitle]) $description = $criterionsDescr_title[$criterionTitle];
                //находим описание по id
                if ($criterionsDescr_id[$criterionId]) $description = $criterionsDescr_id[$criterionId];

                $this->addElement('select', $elName, array(
                    'label' => $criterionTitle,
                    'Description' => $description,
                    'Required' => true,
                    'multiOptions' => $scaleValues
                ));
                $counter++;
            }
        //оценка от 0 до 100
        } else {
            $counter = 0;

            foreach($criterions as $criterionId => $criterionTitle) {
                $elName = 'criterion_mark_'.$counter;
                array_push($criterionElements_mark, $elName);
                array_push($criterionElements_title, $criterionTitle);
                $description = null;
                //находим описание по названию
                if ($criterionsDescr_title[$criterionTitle]) $description = $criterionsDescr_title[$criterionTitle];
                //находим описание по id
                if ($criterionsDescr_id[$criterionId]) $description = $criterionsDescr_id[$criterionId];

                $this->addElement('slider', $elName, array(
                        'Label' => $criterionTitle,
                        'Description' => $description,
                        'Required' => true,
                        'jQueryParams' => array(
                            'range' => false,
                            'min' => 0,
                            'max' => 100,
                            'step' => 1,
                            'value' => 0
                        ),
                        'class' => 'mark_slider'
                    )
                );
                $counter++;
            }
        }

        $student_id = $request->getParam('student_id', 0);
        $user_id = $request->getParam('user_id', 0);
        if ($student_id) {
            $this->addElement('hidden', 'student_id', array(
                'label' => _('student_id'),
                'value'=> $student_id
            ));
        } elseif ($user_id) {
            $this->addElement('hidden', 'user_id', array(
                'label' => _('user_id'),
                'value'=> $user_id
            ));
        }

        //список элементов - критериев (оценка и название критерия)
        $this->addElement('hidden', 'criterionElements_mark', array(
            'label' => _('criterionElements_mark'),
            'value'=> implode(',',$criterionElements_mark)
        ));
        $this->addElement('hidden', 'criterionElements_title', array(
            'label' => _('criterionElements_title'),
            'value'=> implode(',',$criterionElements_title)
        ));

        $this->addElement('hidden', 'is_evaluate_form', array(
            'value'=> 1
        ));

        $view = $this->getView();
        $view->headLink()->appendStylesheet($view->baseUrl('css/content-modules/score.css'));
        $this->addElement('html', 'average_mark', array(
            'value' => '<div class="score_red number_number" style="font-size: 24px; margin-right: 15px;"><span align="center" id="average_mark">-</span></div>'
        ));

        $this->addElement('Submit', 'evaluate_button', array('Label' => _('Оценить'),'id'=>'evaluate'));
        $this->setAttrib('onSubmit', 'if (confirm("'._('После выставления оценки, вы не сможете её изменить. Продолжить?').'")) return true; return false;');

        $this->addDisplayGroup(
            array_merge(
                $criterionElements_mark,
                array(
                    'average_mark',
                    'student_id',
                    'user_id',
                    'evaluate_form',
                    'criterionElements_mark',
                    'criterionElements_title',
                    'evaluate_button',
                )
            ),
            'evaluateGroup',
            array('legend' => _('Предварительная оценка'))
        );

        parent::init(); // required!
	}

}
