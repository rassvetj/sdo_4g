<?php

class Lesson_EvaluationController extends HM_Controller_Action_Subject
{
    protected $_module = 'lesson';
    protected $_controller = 'evaluation';

    protected $_subjectId = 0;
    protected $_lessonId = 0;


    const GRID_SWITCHER_EVALUATED = 0; //оцениваемые
    const GRID_SWITCHER_EVALUATOR = 1; //оценщики

    public function init()
    {
        parent::init();
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $this->_subjectId = $subjectId;
        $this->_lessonId = $lessonId;
    }

    public function userListAction() {
        $subjectId = $this->_subjectId;
        $lessonId = $this->_lessonId;

        $all = Bvb_Grid::getGridSwitcherParamById('evaluatorUsers');
        $grid_switcher = $this->_getParam('all', ($all !== null) ? $all : self::GRID_SWITCHER_EVALUATED);

        $select = $this->getService('User')->getSelect();

        $ph = new Zend_Db_Expr('0');
        $cols = array(
            'MID' => $ph,
            'MID_evaluated' => 'se.MID_evaluated',
            'fio_evaluator' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p_evaluator.LastName, ' ') , p_evaluator.FirstName), ' '), p_evaluator.Patronymic)"),
            'fio_evaluated' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p_evaluated.LastName, ' ') , p_evaluated.FirstName), ' '), p_evaluated.Patronymic)"),
            'interview_title' => 'i.title',
            'interview_status' => 'i2.type',
            'lesson_end_date' => new Zend_Db_Expr("DATE_FORMAT((if (sch.timetype = 1, schID.endRelative, sch.end)), '%d.%m.%Y %H:%i')"),
            'date_limit' => new Zend_Db_Expr("DATE_FORMAT(ses.date_limit, '%d.%m.%Y')"),
            'evaluators_list' => $ph,
            'average_mark' => $ph,
            'marks_count' => new Zend_Db_Expr("COUNT(se.evaluator_mark)"),
            'set_marks_count' => new Zend_Db_Expr("SUM(if(se.evaluator_mark = -1 OR se.consider_in_average_mark = 0, 0, 1))"),
            'set_marks_count_real' => new Zend_Db_Expr("SUM(if(se.evaluator_mark = -1, 0, 1))"),
            'marks_sum' => new Zend_Db_Expr("SUM(if(se.evaluator_mark = -1 OR se.consider_in_average_mark = 0, 0, se.evaluator_mark))"),
            'marks_sum_real' => new Zend_Db_Expr("SUM(if(se.evaluator_mark = -1, 0, se.evaluator_mark))"),
            'criterions' => $ph,
            'evaluator_mark' => $ph,
            'mark_deviation' => $ph,
            'final_mark' => 'schID.V_STATUS',
            'timetype' => 'sch.timetype',
            'consider_in_average_mark' => $ph,
            'evaluators_work_mark' => $ph,
        );
        $group = array(
            'se.MID_evaluated'
        );
        switch ($grid_switcher) {
            case self::GRID_SWITCHER_EVALUATED:
                $cols['MID'] = 'se.MID_evaluated';
                $cols['MID_evaluator'] = 'se.MID_evaluator';
                break;
            case self::GRID_SWITCHER_EVALUATOR:
                $cols['MID'] = 'se2.MID_evaluator';
                $cols['MID_evaluator'] = 'se2.MID_evaluator';
                $cols['evaluator_mark'] = 'se2.evaluator_mark';
                $cols['consider_in_average_mark'] = 'se2.consider_in_average_mark';
                $cols['criterions'] = 'se2.criterions';
                $cols['evaluators_work_mark'] = 'se2.evaluators_work_mark';
                break;
        }

        $select->from(array('se' => 'schedule_evaluators'), $cols);
        $select->joinLeft(array('schID' => 'scheduleID'), 'schID.MID = se.MID_evaluated AND schID.SHEID = se.SHEID', array());
        $select->joinLeft(array('ses' => 'schedule_evaluation_settings'), 'ses.SHEID = schID.SHEID', array());
        $select->joinInner(array('p_evaluated' => 'People'), 'p_evaluated.MID = se.MID_evaluated', array());

        //первое сообщение
        $select->joinLeft(array('i' => 'interview'), 'i.lesson_id = schID.SHEID AND i.user_id = 0 AND i.to_whom = schID.MID', array());
        //последнее сообщение
        $subSelect = $this->getService('User')->getSelect();
        $subSelect->from('interview', array(
            'last_interview_hash' => 'interview.interview_hash',
            'last_interview_id' => 'MAX(interview.interview_id)'
        ))
            ->where('interview.lesson_id = ?', $lessonId)
            ->group(array('interview.interview_hash'));
        $select->joinInner(array('i_last' => $subSelect), 'i_last.last_interview_hash = i.interview_hash', array());
        $select->joinLeft(array('i2' => 'interview'), 'i2.interview_hash = i_last.last_interview_hash AND i2.interview_id = i_last.last_interview_id', array());

        $select->joinLeft(array('sch' => 'schedule'), 'sch.SHEID = schID.SHEID', array());

        switch ($grid_switcher) {
            case self::GRID_SWITCHER_EVALUATED:
                $select->joinInner(array('p_evaluator' => 'People'), 'p_evaluator.MID = se.MID_evaluator', array());
                break;
            case self::GRID_SWITCHER_EVALUATOR:
                $select->joinLeft(array('se2' => 'schedule_evaluators'), 'se2.MID_evaluated = se.MID_evaluated AND se2.SHEID = se.SHEID', array());
                $select->joinInner(array('p_evaluator' => 'People'), 'p_evaluator.MID = se2.MID_evaluator', array());
                array_push($group, 'se2.MID_evaluator');
                break;
        }

        $select->where('schID.SHEID = ? AND schID.MID != 0', $lessonId);

        $select->group($group);

//        $query = $select->query();
//        $st = $query->getDriverStatement();
//        die($st->queryString);
        $evaluatorsList = $this->getEvaluatorsList($lessonId);
        $grid = $this->getGrid($select, array(
                'fio_evaluator' => array('title' => _('ФИО_оценщика'), 'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => ''), null, true) . '{{MID_evaluator}}') . '<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null,'user_id' => ''), null, true) . '{{MID_evaluator}}'.'">'. '{{fio_evaluator}}</a>'),
                'fio_evaluated' => array(
                    'title' => _('ФИО_оцениваемого'),
                    'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => ''), null, true) . '{{MID_evaluated}}') . '<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null,'user_id' => ''), null, true) . '{{MID_evaluated}}'.'">'. '{{fio_evaluated}}</a>'),
                'interview_title' => array(
                    'title' => _('Вариант задания'),
                    'callback' => array(
                        'function' => array($this, 'interviewTitleCallback'),
                        'params' => array('{{interview_title}}', '{{MID_evaluated}}')
                    )
                ),
                'interview_status' => array(
                    'title' => _('Статус'),
                    'callback' => array(
                        'function' => array($this, 'interviewStatusCallback'),
                        'params' => array('{{interview_status}}')
                    )
                ),
                'lesson_end_date' => array(
                    'title' => _('Сроки выполнения'),
                    'callback' => array(
                        'function' => array($this, 'lessonEndDateCallback'),
                        'params' => array('{{lesson_end_date}}', '{{timetype}}')
                    )
                ),
                'date_limit' => array(
                    'title' => _('Сроки оценивания'),
                    'callback' => array(
                        'function' => array($this, 'dateLimitCallback'),
                        'params' => array('{{date_limit}}')
                    )
                ),
                'evaluators_list' => array(
                    'title' => _('Пользователи, оценивающие работы'),
                    'callback' => array(
                        'function' => array($this, 'evaluatorsListCallback'),
                        'params' => array('{{MID_evaluated}}', $evaluatorsList)
                    )
                ),
                'average_mark' => array(
                    'title' => _('Средняя оценка'),
                    'callback' => array(
                        'function' => array($this, 'averageMarkCallback'),
                        'params' => array('{{marks_sum}}', '{{set_marks_count}}', '{{marks_sum_real}}', '{{set_marks_count_real}}')
                    )
                ),
                'marks_count' => array(
                    'title' => _('Количество оценок'),
                    'callback' => array(
                        'function' => array($this, 'marksCountCallback'),
                        'params' => array('{{marks_count}}', '{{set_marks_count}}', '{{set_marks_count_real}}')
                    )
                ),
                'final_mark' => array(
                    'title' => _('Итоговая оценка'),
                    'callback' => array(
                        'function' => array($this, 'markCallback'),
                        'params' => array('{{final_mark}}')
                    )
                ),
                'mark_deviation' => array(
                    'hidden' => false,
                    'title' => _('Отклонение'),
                    'callback' => array(
                        'function' => array($this, 'markDeviationCallback'),
                        'params' => array('{{marks_sum_real}}', '{{set_marks_count_real}}', '{{evaluator_mark}}')
                    )
                ),
                'criterions' => array(
                    'hidden' => false,
                    'title' => _('Оценка'),
                    'callback' => array(
                        'function' => array($this, 'evaluatorMarkCallback'),
                        'params' => array('{{evaluator_mark}}', '{{criterions}}')
                    )
                ),
                'consider_in_average_mark' => array(
                    'hidden' => false,
                    'title' => _('Учитывать в средней оценке'),
                    'callback' => array(
                        'function' => array($this, 'considerInAverageMarkCallback'),
                        'params' => array('{{consider_in_average_mark}}')
                    )
                ),
                'evaluators_work_mark' => array(
                    'title' => _('Оценка работы оценщика'),
                    'callback' => array(
                        'function' => array($this, 'markCallback'),
                        'params' => array('{{evaluators_work_mark}}')
                    )
                ),
            ),
            array(
                'fio_evaluator' => null,
                'fio_evaluated' => null,
                'interview_title' => null,
                'interview_status' => array('values' => HM_Interview_InterviewModel::getTypes())
            ),
            'evaluatorUsers'
        );

        //прячем лишние колонки
        $hide_cols = array(
            'MID',
            'MID_evaluated',
            'MID_evaluator',
            'set_marks_count',
            'marks_sum',
            'timetype',
            'set_marks_count_real',
            'marks_sum_real',
            'evaluator_mark'
        );
        switch ($grid_switcher) {
            case self::GRID_SWITCHER_EVALUATED:
                array_push($hide_cols,
                    'fio_evaluator',
                    'mark_deviation',
                    'date_limit',
                    'consider_in_average_mark',
                    'criterions',
                    'evaluators_work_mark'
                );
                break;
            case self::GRID_SWITCHER_EVALUATOR:
                array_push($hide_cols,
                    'lesson_end_date',
                    'average_mark',
                    'marks_count',
                    'final_mark',
                    'evaluators_list'
                );
                break;
        }
        $grid->setColumnsHidden($hide_cols);


        $grid->addAction(array(
                'module' => 'message',
                'controller' => 'send',
                'action' => 'index'
            ),
            array('MID'),
            _('Отправить сообщение')
        );

        //добавлем масс экшены
        /** @var HM_Lesson_Evaluation_EvaluatorsService $lessonEvaluatorsService */
        $lessonEvaluatorsService = $this->getService('LessonEvaluators');

        $grid->setOptions(array('ignoreMassActionsIds' => true)); //костыль для таблиц без PK
        $suitableEvaluators = array(_('Выберите оценщиков')) + $lessonEvaluatorsService->getSuitableEvaluatorsList($lessonId);
        //редактирование
        $grid->addMassAction(array('module' => 'lesson',
                'controller' => 'evaluation',
                'action' => 'assign-evaluators',
                'lesson_id' => $lessonId),
            _('Назначить оценщика'),
            _('Вы уверены?')
        );
        $grid->addSubMassActionSelect($this->view->url(array('module' => 'lesson',
                'controller' => 'evaluation',
                'action' => 'assign-evaluators',
                'lesson_id' => $lessonId)),
            'evaluatorsId[]',
            $suitableEvaluators);

        if ($grid_switcher == self::GRID_SWITCHER_EVALUATOR) {
            //удаление
            $grid->addMassAction(array('module' => 'lesson',
                    'controller' => 'evaluation',
                    'action' => 'delete-evaluators',
                    'lesson_id' => $lessonId),
                _('Удалить оценщиков'),
                _('Вы уверены?')
            );

            //учитывание в средней оценке
            $grid->addMassAction(array('module' => 'lesson',
                    'controller' => 'evaluation',
                    'action' => 'consider-mark',
                    'lesson_id' => $lessonId,
                    'consider' => 1),
                _('Учитывать в средней оценке')
            );
            $grid->addMassAction(array('module' => 'lesson',
                    'controller' => 'evaluation',
                    'action' => 'consider-mark',
                    'lesson_id' => $lessonId,
                    'consider' => 0),
                _('Не учитывать в средней оценке')
            );

            //выставление оценки оценщикам
            $grid->addMassAction(
                array('action' => 'evaluate-evaluators'),
                _('Выставить оценку оценщикам')
            );
            $grid->addSubMassActionInput(
                array(
                    $this->view->url(array('action' => 'evaluate-evaluators'))
                ),
                'mark'
            );
        }

        //массэкшн коллбэк
        switch ($grid_switcher) {
            case self::GRID_SWITCHER_EVALUATED:

                break;
            case self::GRID_SWITCHER_EVALUATOR:

                break;
        }
        $grid->setMassActionsCallback(
            array('function' => array($this,'updateMassActions'),
                'params'   => array('{{MID_evaluator}}', '{{MID_evaluated}}', '{{evaluator_mark}}', '{{set_marks_count}}')
            )
        );


        $grid->setGridSwitcher(array(
            array('title' => _('Оцениваемые (слушатели)'), 'params' => array('all' => self::GRID_SWITCHER_EVALUATED)),
            array('title' => _('Оценщики'), 'params' => array('all' => self::GRID_SWITCHER_EVALUATOR)),
        ));

        $this->view->isAjaxRequest = $this->isAjaxRequest();
        $this->view->grid = $grid->deploy();
    }

    public function evaluateEvaluatorsAction() {
        $mark = intval($this->_getParam('mark'));
        if ($mark) {
            $pairs = explode(',',$this->_getParam('postMassIds_evaluatorUsers',array()));
            foreach ($pairs as $key => $val) {
                //оценщик_оцениваемый => (оценщик, оцениваемый)
                $pair = explode('_',$val);
                $pairs[$key] = '('.(int)$pair[0].', '.(int)$pair[1].')';
            }
            /** @var HM_Lesson_Evaluation_EvaluatorsService $lessonEvaluatorsService */
            $lessonEvaluatorsService = $this->getService('LessonEvaluators');
            $lessonEvaluatorsService->updateWhere(
                array('evaluators_work_mark' => $mark),
                array(
                    'SHEID = ? AND (MID_evaluator, MID_evaluated) IN ('.implode(', ',$pairs).')' => $this->_getParam('lesson_id',0),
                )
            );
            $this->goToEvaluatorsList(_('Оценка успешно выставлена.'));
        } else {
            $this->goToEvaluatorsList(_('Ошибка при выставлении оценок оценщикам.'), HM_Notification_NotificationModel::TYPE_ERROR);
        }

    }

    public function assignEvaluatorsAction() {
        //список оценщиков, которых надо назначить
        $evaluatorsId = $this->_getParam('evaluatorsId',array());
        if (!is_array($evaluatorsId)) {
            $evaluatorsId = array($evaluatorsId);
        }
        $lessonId = $this->_getParam('lesson_id',0);

        $all_evaluators = array();
        $all_evaluated = array();
        $all_pairs = array();

        //создаём пары 'оценщик_оцениваемый'
        $pairs = explode(',',$this->_getParam('postMassIds_evaluatorUsers',array()));
        $grid_evaluatedId = array();
        $grid_evaluatorsId = array();
        $in = array();
        foreach ($pairs as $val) {
            //$val = 'оценщик_оцениваемый'
            $pair = explode('_',$val);
            array_push($grid_evaluatorsId, $pair[0]);
            array_push($grid_evaluatedId, $pair[1]);
            array_push($in, '('.$pair[0].', '.$pair[1].')');
            foreach($evaluatorsId as $v) {
                array_push($all_pairs, $v.'_'.$pair[1]);
            }
        }
        $all_pairs = array_unique($all_pairs); //убираем дубли

        /** @var HM_Lesson_Evaluation_EvaluatorsService $lessonEvaluatorsService */
        $lessonEvaluatorsService = $this->getService('LessonEvaluators');
        $grid_switcher = $this->_getParam('all', self::GRID_SWITCHER_EVALUATED);
        if ($grid_switcher == self::GRID_SWITCHER_EVALUATED) {
            //удаляем оценщиков, которые назначены на оцениваемых - $grid_evaluatedId
            $lessonEvaluatorsService->deleteBy(
                array(
                    'SHEID = ?' => $lessonId,
                    'evaluator_mark = ?' => -1,
                    'MID_evaluated IN (?)' => $grid_evaluatedId
                )
            );
        } else if ($grid_switcher == self::GRID_SWITCHER_EVALUATOR) {
            //удаляем пары оценщик_оцениваемый, которые отмечены
            $lessonEvaluatorsService->deleteBy(
                array(
                    'SHEID = ? AND evaluator_mark = -1 AND (MID_evaluator, MID_evaluated) IN('.implode(',',$in).')' => $lessonId
                )
            );
        }

        //получаем список уже существующих оценщиков
        $collection = $lessonEvaluatorsService->fetchAll(
            array(
                'SHEID = ?' => $lessonId,
                'MID_evaluated IN (?)' => $grid_evaluatedId
            )
        );
        if (count($collection)) {
            $existing_pairs = array();
            foreach ($collection as $item) {
                //$existing_pairs[] = 'оценщик_оцениваемый'
                array_push($existing_pairs, $item->MID_evaluator.'_'.$item->MID_evaluated);
            }
            //используем только те пары, которых нет в базе
            $all_pairs = array_diff($all_pairs, $existing_pairs);
        }

        foreach ($all_pairs as $val) {
            //оценщик_оцениваемый
            $pair = explode('_',$val);
            if ($pair[0] != $pair[1]) {
                array_push($all_evaluators, $pair[0]);
                array_push($all_evaluated, $pair[1]);
            }
        }

        //назначаем новых оценщиков
        if (!$lessonEvaluatorsService->assignEvaluators($lessonId, $all_evaluators, $all_evaluated)) {
            $this->goToEvaluatorsList(_('Произошла ошибка при назначении оценщиков.'), HM_Notification_NotificationModel::TYPE_ERROR);
        }
        $this->goToEvaluatorsList(_('Оценщики успешно назначены'));
    }

    public function deleteEvaluatorsAction() {
        $pairs = explode(',',$this->_getParam('postMassIds_evaluatorUsers',array()));
        $pairsCount = count($pairs);
        foreach ($pairs as $key => $val) {
            //оценщик_оцениваемый => (оценщик, оцениваемый)
            $pair = explode('_',$val);
            $pairs[$key] = '('.(int)$pair[0].', '.(int)$pair[1].')';
        }
        //удаляем оценщиков
        /** @var HM_Lesson_Evaluation_EvaluatorsService $lessonEvaluatorsService */
        $lessonEvaluatorsService = $this->getService('LessonEvaluators');
        $rowsAffected = $lessonEvaluatorsService->deleteBy(
            array(
                'SHEID = ? AND evaluator_mark = -1 AND (MID_evaluator, MID_evaluated) IN ('.implode(', ',$pairs).')' => $this->_getParam('lesson_id',0),
            )
        );
        if ($rowsAffected == $pairsCount) {
            $this->goToEvaluatorsList(_('Оценщики успешно удалены'));
        } else if ($rowsAffected > 0) {
            $this->goToEvaluatorsList(sprintf(_('Оценщиков удалено: %d из %d. Нельзя удалить оценщиков, которые уже выставили оценку.'), $rowsAffected, $pairsCount));
        } else {
            $this->goToEvaluatorsList(_('Ошибка при удалении оценщиков. Нельзя удалить оценщиков, которые уже выставили оценку.'), HM_Notification_NotificationModel::TYPE_ERROR);
        }
    }

    public function considerMarkAction() {
        $consider = $this->_getParam('consider', 0);
        $pairs = explode(',',$this->_getParam('postMassIds_evaluatorUsers',array()));
        foreach ($pairs as $key => $val) {
            //оценщик_оцениваемый => (оценщик, оцениваемый)
            $pair = explode('_',$val);
            $pairs[$key] = '('.(int)$pair[0].', '.(int)$pair[1].')';
        }
        /** @var HM_Lesson_Evaluation_EvaluatorsService $lessonEvaluatorsService */
        $lessonEvaluatorsService = $this->getService('LessonEvaluators');
        $lessonEvaluatorsService->updateWhere(
            array('consider_in_average_mark' => $consider),
            array(
                'SHEID = ? AND (MID_evaluator, MID_evaluated) IN ('.implode(', ',$pairs).')' => $this->_getParam('lesson_id',0),
            )
        );
        $this->goToEvaluatorsList(_('Настройки для оценщиков успешно изменены'));
    }

    /**
     * @param $lessonId
     * @return array $arr[MID_оцениваемого] = array('MID_оценщика' => 'ФИО')
     */
    public function getEvaluatorsList($lessonId) {
        $select = $select = $this->getService('User')->getSelect();

        $select->from(array('se' => 'schedule_evaluators'), array(
            'MID_evaluated' => 'se.MID_evaluated',
            'MID_evaluator' => 'se.MID_evaluator',
            'evaluator_fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
        ));
        $select->joinInner(array('p' => 'People'), 'p.MID = se.MID_evaluator', array());
        $select->where('se.SHEID = ?', $lessonId);

        $result = array();
        foreach ($select->query()->fetchAll() as $row) {
            $MID_evaluated = (int)$row['MID_evaluated'];
            $MID_evaluator = (int)$row['MID_evaluator'];
            $evaluator_fio = $row['evaluator_fio'];
            if (empty($result[$MID_evaluated])) {
                $result[$MID_evaluated] = array();
            }
            $result[$MID_evaluated][$MID_evaluator] = $evaluator_fio;
        }

        return $result;
    }

    public function evaluatorsListCallback($MID_evaluated, $evaluatorsList) {
        if (empty($evaluatorsList[$MID_evaluated])) return;
        $url = $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null,'user_id' => ''), null, true);
        foreach ($evaluatorsList[$MID_evaluated] as $key => $val) {
            $href =  $url . $key;
            $evaluatorsList[$MID_evaluated][$key] = '<p><a href="'.$href.'">'.$evaluatorsList[$MID_evaluated][$key].'</a></p>';
        }
        $evaluatorsCount = count($evaluatorsList[$MID_evaluated]);
        switch ($evaluatorsCount) {
            case 0:
                return _('Нет');
                break;
            case 1:
                return implode('',$evaluatorsList[$MID_evaluated]);
                break;
            default:
                return '<p class="total" style="display:block;">Пользователей:&nbsp;'.count($evaluatorsList[$MID_evaluated]).'</p>'.implode('',$evaluatorsList[$MID_evaluated]);
        }
    }

    public function interviewTitleCallback($interview_title, $MID_evaluated) {
        $href = $this->view->url(
            array(
                'module' => 'interview',
                'controller' => 'index',
                'action' => 'index',
                'lesson_id' => $this->_lessonId,
                'subject_id' => $this->_subjectId,
                'user_id' => $MID_evaluated,
            ), null, true);

        return '<a href="'.$href.'" class="dialog" title="'.$interview_title.'">
        <img src="/images/content-modules/grid/card.gif" title="'.$interview_title.'" class="ui-els-icon "></a> '.$interview_title;
    }
    public function interviewStatusCallback($interview_status) {
        $types = HM_Interview_InterviewModel::getTypes();
        return $types[$interview_status];
    }
    public function averageMarkCallback($marks_sum, $set_marks_count, $marks_sum_real, $set_marks_count_real) {
        if ($set_marks_count == 0) {
            $average_mark = _('Нет');
        } else {
            $average_mark = round($marks_sum / $set_marks_count);
        }

        if ($set_marks_count_real == 0) {
            $average_mark_real = _('Нет');
        } else {
            $average_mark_real = round($marks_sum_real / $set_marks_count_real);
        }

        if ((int)$set_marks_count != (int)$set_marks_count_real) {
            return $average_mark.'('.$average_mark_real.')<span style="color: red;">*</span>';
        } else {
            return $average_mark;
        }
    }
    public function markCallback($mark) {
        if ($mark == -1) {
            return _('Нет');
        }
        return $mark;
    }
    public function evaluatorMarkCallback($mark, $criterions) {
        if ($mark == -1) {
            return _('Нет');
        }
        if (!$criterions) return $mark;

        $criterions = unserialize($criterions);
        $criterionsCount = count($criterions);
        switch ($criterionsCount) {
            case 0:
                return $mark;
                break;
            default:
                $str = '';
                foreach ($criterions as $key => $value) {
                    $str .= '<p>'.$key.':&nbsp;'.$value.'</p>';
                }
                return '<p class="total" style="display:block;">'.$mark.'</p>'.$str;
        }
    }
    public function marksCountCallback($marks_count, $set_marks_count, $set_marks_count_real) {
        if ((int)$set_marks_count != (int)$set_marks_count_real) {
            return $set_marks_count.'('.$set_marks_count_real.')<span style="color: red;">*</span> из '.$marks_count;
        } else {
            return $set_marks_count.' из '.$marks_count;
        }
    }
    public function markDeviationCallback($marks_sum, $set_marks_count, $evaluator_mark) {
        if ($evaluator_mark == _('Нет') || $evaluator_mark == -1 || $set_marks_count == 0) return '';
        $deviation = round($evaluator_mark - $marks_sum / $set_marks_count, 2);
        return ($deviation > 0) ? '+'.$deviation : $deviation;
    }
    public function dateLimitCallback($date_limit) {
        $date_limit_time = strtotime($date_limit);
        if ($date_limit_time == 0) return _('Без ограничений');
        if ($date_limit_time < time()) {
            $date_limit = '<span style="color: red;">'.$date_limit.'</span>';
        }
        return $date_limit;
    }
    public function lessonEndDateCallback($lesson_end_date, $timetype) {
        if ($timetype == HM_Lesson_LessonModel::TIMETYPE_FREE) {
            return _('Без ограничений');
        } else {
            $lesson_end_ts = strtotime($lesson_end_date);
            if ($lesson_end_ts < time()) {
                $lesson_end_date = '<span style="color: red;">'.$lesson_end_date.'</span>';
            }
            return $lesson_end_date;
        }
    }
    public function considerInAverageMarkCallback($consider_in_average_mark) {
        if ($consider_in_average_mark == 1) {
            return _('Да');
        } else {
            return _('Нет');
        }
    }
    public function updateMassActions($MID_evaluator, $MID_evaluated, $evaluator_mark, $set_marks_count, $action) {
        //value="оценщик_оцениваемый"
        $result = str_replace('{{pk}}', $MID_evaluator.'_'.$MID_evaluated, $action);
        /** @todo Оценщиков, которые выставили оценку - удалять нельзя, можно добавить пометку об ограничении действий */
//        if ($evaluator_mark != -1 && $set_marks_count != 0) {
//            $result .= '<span style="color: red;">*</span>';
//        }
        return $result;
    }


    public function editSettingsAction() {
        $form = new HM_Form_EvaluationSettings();
        $request = $this->getRequest();

        $subjectId = $this->_subjectId;
        $lessonId = $this->_lessonId;

        $oldEvaluationSettings = $form->getEvaluationSettings($lessonId);

        $lessonService = $this->getService('Lesson');
        /** @var HM_Lesson_LessonModel $lesson */
        $lesson = $lessonService->getOne($lessonService->find($lessonId));

        if ($request->isPost() && $form->isValid($request->getPost())) {

            //Проверяем, выставил ли уже кто-нибудь оценки
            /** @var HM_Lesson_Evaluation_EvaluatorsService $lessonEvaluatorsService */
            $lessonEvaluatorsService = $this->getService('LessonEvaluators');
            $collection = $lessonEvaluatorsService->fetchAll(
                array('SHEID = ? AND evaluator_mark != -1' => $lessonId)
            );
            if ($collection->count() != 0) {
                $this->editSettingsError(_('Один или несколько оценщиков уже выставили оценки, редактирование системы оценивания невозможно!'));
            } else {
                //удаляем оценщиков
                $lessonEvaluatorsService->deleteBy(
                    array('SHEID = ?' => $lessonId)
                );
            }

            $lesson_required = $form->getValue('lesson_required', null);
            $mark_required = $form->getValue('mark_required', null);
            $date_limit = $form->getValue('date_limit', null);

            $evaluation_type = $form->getValue('evaluation_type');

            $students_assign_mode = $form->getValue('students_assign_mode');
            $tutors_assign_mode = $form->getValue('tutors_assign_mode');
            $assign_mode = ($students_assign_mode != '') ? $students_assign_mode : $tutors_assign_mode;
            if ($assign_mode == '') {
                $assign_mode = -1;
            }

            $students_evaluators_count = $form->getValue('students_evaluators_count');
            $tutors_evaluators_count = $form->getValue('tutors_evaluators_count');
            $evaluators_count = empty($students_evaluators_count) ? $tutors_evaluators_count : $students_evaluators_count;

            //критерии храним в виде сериализованного массива, т.к. выборки по ним не будет,
            //они нужны только, как промежуточное звено, на пути к финальной оценке,
            //кроме того, список критериев в системе может меняться
            $el = $form->getElement('criterions');
            $criterions = array();
            if ($elValue = $el->getValue()) {
                $elOptions = $el->getAttribs();
                $elOptions = $elOptions['multiOptions'];
                foreach ($elValue as $v) {
                    $criterions[(int)$v] = $elOptions[(int)$v];
                }
            } else {
                $criterions['-1'] = _('Оценка за занятие');
            }
            $criterions = serialize($criterions);

            $data = array(
                'SHEID' => $lessonId,
                'evaluation_type' => $evaluation_type,
                'assign_mode' => $assign_mode,
                'lesson_required' => $lesson_required,
                'mark_required' => $mark_required,
                'evaluators_count_required' => $evaluators_count,
                'date_limit' => $date_limit,
                'scale_id' => $form->getValue('scale_id', 0),
                'criterions' => $criterions
            );

            $lesService = $this->getService('LessonEvaluationSettings');
            if ($oldEvaluationSettings) {
                $newEvaluationSettings = $lesService->update($data);
            } else {
                $newEvaluationSettings = $lesService->insert($data);
            }

            //все оценщики
            $all_evaluators = array();
            //все оцениваемые
            $all_evaluated = array();


            //автоматический выбор оценщиков
            if ($newEvaluationSettings->assign_mode == HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_MODE_AUTO) {

                //получаем список подходящих оценщиков
                if ($evaluation_type == HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_TYPE_STUDENT) {
                    $suitableEvaluators = $lesson->getSuitableEvaluatorStudents($mark_required, $lesson_required);
                } else {
                    $tutors_list = $form->getValue('tutors_list');
                    $suitableEvaluators = ($tutors_list == 0) ? array() : $tutors_list;
                }

                //если оценщиков недостаточно
                if ($evaluators_count == 0) {
                    $this->editSettingsError(_('Должен быть как минимум один оценщик на одну работу'));
                }
                $suitableEvaluatorsCount = count($suitableEvaluators);
                if ($suitableEvaluatorsCount < $evaluators_count) {
                    $this->editSettingsError(sprintf(_('Недостаточно оценщиков. Подходящих оценщиков: %d'), $suitableEvaluatorsCount));
                }

                //оцниваемые
                $evaluatedMID = $lesson->getAssignedStudents()->getList('MID');
                $lessonStudentsCount = count($evaluatedMID);

                //ошибки взаимной оценки
                if ($evaluation_type == HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_TYPE_STUDENT) {
                    if ($suitableEvaluatorsCount == 1) {
                        $this->editSettingsError(sprintf(_('При взаимной оценке, должно быть как минимум два подходящих оценщика. Подходящих оценщиков: %d'), $suitableEvaluatorsCount));
                    }
                    if ($evaluators_count >= $lessonStudentsCount) {
                        $this->editSettingsError(sprintf(_('При взаимной оценке, количество оценщиков на одну работу, должно быть меньше, чем оцениваемых. Оценщиков на одну работу: %d. Оцениваемых: %d'), $evaluators_count, $lessonStudentsCount));
                    }
                }

                $evaluation_type = $newEvaluationSettings->evaluation_type;
                switch($evaluation_type) {
                    case HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_TYPE_STUDENT:
                        //оценщики
                        $evaluatorsMID = $suitableEvaluators->getList('MID');
                        break;
                    case HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_TYPE_TUTOR:
                        //оценщики
                        $evaluatorsMID = $suitableEvaluators;
                        break;
                }

                //равномерно распеределяем оценщиков по оцениваемым
                //$evaluatorsMID[index] = MID_оценщика
                shuffle($evaluatorsMID);
                //$evaluatedCount[MID_оценщика] = количество_оцениваемых
                $evaluatedCount = array_flip($evaluatorsMID);
                $evaluatedCount = array_fill_keys(array_keys($evaluatedCount), 0);

                foreach ($evaluatedMID as $evaluated) {
                    $evaluatedCount_copy = $evaluatedCount;
                    unset($evaluatedCount_copy[(int)$evaluated]);
                    //сортируем так, чтобы сперва были те, у кого меньше всего оцениваемых
                    asort($evaluatedCount_copy);

                    $counter = 0;
                    foreach ($evaluatedCount_copy as $key => $value) {
                        array_push($all_evaluated, $evaluated);
                        array_push($all_evaluators, $key);
                        $evaluatedCount[$key] += 1;
                        $counter++;
                        if ($counter == $evaluators_count) break;
                    }
                }
            }

            //ручной выбор оценщиков
            if ($newEvaluationSettings->assign_mode == HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_MODE_MANUAL) {
                $students_evaluators = $form->getValue('students_evaluators');
                $tutors_evaluators = $form->getValue('tutors_evaluators');
                //$temp_evaluators[оцениваемый] = оценщик
                $temp_evaluators = empty($students_evaluators) ? $tutors_evaluators : $students_evaluators;
                foreach ($temp_evaluators as $evaluated => $evaluator) {
                    if (!empty($evaluated) && !empty($evaluator)) {
                        array_push($all_evaluated, $evaluated);
                        array_push($all_evaluators, $evaluator);
                    }
                }
            }

            //без режима - самооценка
            if ($newEvaluationSettings->assign_mode == HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_MODE_UNDEFINED
                && $evaluation_type == HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_TYPE_SELF) {
                $all_evaluators = $all_evaluated = array_values($lesson->getAssignedStudents()->getList('MID'));
            }


            //назначаем новых оценщиков
            if ($evaluation_type != HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_TYPE_TEACHER) {
                if (!$lessonEvaluatorsService->assignEvaluators($lessonId, $all_evaluators, $all_evaluated)) {
                    $this->editSettingsError(_('Произошла ошибка при назначении оценщиков.'));
                }
            }

            $this->goToEditSettings(_('Система оценивания успешно изменена.'));
        }
        $this->view->form = $form;
    }

    private function goToEditSettings($msg = null, $msgType = HM_Notification_NotificationModel::TYPE_SUCCESS) {
        if ($msg) {
            $this->_flashMessenger->addMessage(array('message' => $msg, 'type' => $msgType));
        }
        $this->_redirector->gotoSimple('edit-settings', $this->_controller, $this->_module, array('subject_id' => $this->_subjectId, 'lesson_id' => $this->_lessonId));
    }

    private function editSettingsError($msg) {
        $this->goToEditSettings($msg, HM_Notification_NotificationModel::TYPE_ERROR);
    }

    private function goToEvaluatorsList($msg = null, $msgType = HM_Notification_NotificationModel::TYPE_SUCCESS) {
        if ($msg) {
            $this->_flashMessenger->addMessage(array('message' => $msg, 'type' => $msgType));
        }
        $this->_redirector->gotoSimple(
            'user-list',
            $this->_controller,
            $this->_module,
            array(
                'subject_id' => $this->_subjectId,
                'lesson_id' => $this->_lessonId
            )
        );
    }
}
