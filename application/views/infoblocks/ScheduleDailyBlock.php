<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_ScheduleDailyBlock extends HM_View_Infoblock_ScreenForm
{

    CONST LESSON_LIMIT = 20;
    
    protected $id = 'schedule';

    public function scheduleDailyBlock($title = null, $attribs = null, $options = null)
    {
        $begin = isset($options['begin']) ? $options['begin'] : strtotime(date('Y-m-d'));
        $end = $begin + 60*60*24;
        $now = time();

        $ajax = isset($options['ajax']);
        $currentUserId = (int) $this->getService('User')->getCurrentUserId();

        /**
         * modify #12475
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 10.01.2013
         */
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)) {
            $rows = $this->getCurrentActiveLessons($begin,$currentUserId);
        } else {
            $rows = $this->getCurrentActiveLessonsStudent($begin,$end,$currentUserId);
        }
        /**
         * end modify
         */
        $subjects = false;
        $sequence = $students = array();

        if (!empty($rows)) {
            $lessonService = $this->getService('Lesson');
            $userService = $this->getService('User');
            $aclService = $this->getService('Acl');
            $isTeacher = $aclService->inheritsRole($userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER);
            $select=$this->getService('Teacher')->getSelect()->from(array('t' => 'Teachers'))
                        ->joinLeft(array('p' => 'People'),'p.MID = t.MID',array('id' => 'p.MID',
                                       'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"))
                );
            $res=$select->query()->fetchAll();
            $teachers=array();
            foreach($res as $row){
                $teachers += array($row['id']=>$row['fio']);
            }
            // Формируем коллекцию заранее, что бы потом не обращатся к БД в цикле
            $sheduleIds = array();
            $i = 0;
            foreach($rows as $row){
                if($i > self::LESSON_LIMIT){
                    break;
                }
                $sheduleIds[] = $row['SHEID'];
                $i++;
            }
            $this->view->lessonCount = count($rows);
            $this->view->lessonLimit = self::LESSON_LIMIT;
                    
            $lessonsColl = $lessonService->fetchAllDependence('Assign', array(
                'SHEID IN(' . implode(',', $sheduleIds) . ')'
            ));

            // Возможно данное действие и лишнее, но оно гаррантирует соответствие "SHEID"
            $lessonsCollection = array();
            foreach($lessonsColl as $lesson) {
                $lessonsCollection[$lesson->SHEID] = $lesson;
            }

            unset($sheduleIds, $lessonsColl);

            foreach($rows as $row) {
                // Это не дело, гонять в цикле запросы к БД
                // $lesson = $this->getService('Lesson')->fetchAllDependence('Assign', 'SHEID = ' . $row['SHEID'])->current();
                /** @var $lesson HM_Lesson_LessonModel */
                $lesson = $lessonsCollection[$row['SHEID']];
                if (!$lesson) continue;

                $students[$lesson->SHEID] = array();

                if (!$isTeacher && $lesson->isRelative()) {
                    if ((strtotime($lesson->getBeginDateRelative()) >= $end)
                        || strtotime($lesson->getEndDateRelative()) < $begin) {
                        continue;
                    }
                }

                if(!$isTeacher){
                    $lesson->teacher = array('id' => $row['teacher'],'fio' => $teachers[$row['teacher']]);
                }
                if ($lesson->timetype == HM_Lesson_LessonModel::TIMETYPE_FREE) {
                    $lesson->begin = date('Y-m-d H:i', $begin);
                    $lesson->end   = date('Y-m-d H:i', $end);
                }

                if ($lesson->isConditionalLesson() && !$lessonService->isLaunchConditionSatisfied($lesson->SHEID, $lesson, false) && !$isTeacher){
                    continue;
                }

                if (!$isTeacher && ($row['mark'] == -1) && $lesson->vedomost && !$lesson->recommend && strtotime($lesson->end) && !$lesson->isTimeFree() && (strtotime($lesson->end) < time())){
                    $prefix = '0';
                    $lesson->overdue = true;
                }
                else{
                     $prefix = $lesson->recommend ? '9' : '';
                     $lesson->overdue = false;
                }

                $lessons = array();
                if (isset($sequence[$row['CID']])) {
                    $lessons = $subjects[$sequence[$row['CID']]]['lessons'];
                    unset($subjects[$sequence[$row['CID']]]);
                }
                $key = $row['subject'].$row['begin'].$row['CID'];
                $sequence[$row['CID']] = $key;
                $subjects[$key]['title'] = $row['subject'];
                $subjects[$key]['subject_id'] = $row['CID'];
                $lessons[str_pad($row['order'], 3, '0', STR_PAD_LEFT).$prefix.$row['begin'].$row['SHEID']] = $lesson;
                //$lessons[str_pad($row['order'], 3, '0', STR_PAD_LEFT).$row['SHEID']] = $lesson;
                $subjects[$key]['lessons'] = $lessons;
            }

            if (is_array($subjects)) {
                ksort($subjects);
            }
        }

        // #7882
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER) && count($students)) {
            $select = $this->getService('User')->getSelect();

            $select->from(
                array('s' => 'schedule'),
                array(
                    's.SHEID',
                    'p.MID',
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                    'st.time_registered',
                    'sid.beginRelative',
                    'sid.endRelative'
                )
            )->joinInner(array('sid' => 'scheduleID'), 'sid.SHEID = s.SHEID', array())
             ->joinInner(array('st' => 'Students'), 'st.CID = s.CID AND st.MID = sid.MID', array())
             ->joinInner(array('p' => 'People'), 'p.MID = sid.MID', array())
             ->where('s.SHEID IN (?)', array_keys($students));

            $rows = $select->query()->fetchAll();
            if (!empty($rows)) {
                $students = array();
                foreach($rows as $row) {
                    $students[$row['SHEID']][$row['MID']] = array(
                        'fio'           => $row['fio'],
                        'regtime'       => $row['time_registered'],
                        'beginRelative' => $row['beginRelative'],
                        'endRelative'   => $row['endRelative']);
                }
            }
        }

        $this->view->ajax = $ajax;
        $this->view->begin = date('d.m.Y', $begin);
        $this->view->end = date('d.m.Y', $end);
        $this->view->subjects = $subjects;
        $this->view->students = $students;

        $content = $this->view->render('scheduleDailyBlock.tpl');

        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/schedule-daily/style.css');

        return parent::screenForm($title, $content, $attribs);
    }

    /**
     * Функция выбирает занятия с учетом новых параметров.
     * примерно так:
     * Курсы с ручным стартом должны иметь статус "Идет"
     * Курсы со строгим соответствием должны совпадать по датам.
     * Курсы с фиксированной длинной должны иметь дату начала не раньше сегодня + longtime
     * остальные или PERIOD_FREE,PERIOD_DATE AND PERIOD_RESTRICTION_DECENT
     * Занятия в курсах timetype (2)
     * Занятия в курсах recommend = 1
     * Занятия в курсах timetype (0,3) и сейчас между началом и концом
     * Занятия в курсах timetype (1) и сейчас между день начала+день начала курса и день начала+день конца курса.
     *
     * @auhtor Artem Smirnov <tonakai.personal@gmail.com>
     * @date 10.01.2013
     *
     * @param $nowTime
     * @param $currentUserId
     *
     * @return array
     */
    public function getCurrentActiveLessons($nowTime,$currentUserId)
    {
        $day = 60*60*24;
        $select = $this->getService('User')->getSelect();
        $select->from(array('s' => 'schedule'), array(
            's.*',
            'subject' => 'subjects.name',
            'subject_begin' => 'UNIX_TIMESTAMP(subjects.begin)',
            'subject_end' => 'UNIX_TIMESTAMP(subjects.end)',
        ))
            ->joinInner('subjects', 's.CID = subjects.subid', array())
            ->joinInner('Teachers', 's.CID = Teachers.CID', array())
            ->where('Teachers.MID = ?', $currentUserId)
            ->where($this->getService('User')->quoteInto('s.teacher = ?', $currentUserId))
            ->where($this->getService('User')->quoteInto(array(
                    '((subjects.period_restriction_type = ? ',
                    'AND subjects.state = ?) OR',
                    '(subjects.period_restriction_type = ? ',
                    'AND UNIX_TIMESTAMP(subjects.begin) <= ? ',
                    'AND UNIX_TIMESTAMP(subjects.end) > ?) OR',
                    '(subjects.period = ? ',
                    'AND UNIX_TIMESTAMP(subjects.begin) <= ? ',
                    'AND UNIX_TIMESTAMP(subjects.begin) + subjects.longtime*60*60*24 > ?) OR',
                    '(subjects.period = ?) OR',
                    '(subjects.period_restriction_type = ?))'
                ), array(
                    HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                    HM_Subject_SubjectModel::STATE_ACTUAL,
                    HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,$nowTime,$nowTime,
                    HM_Subject_SubjectModel::PERIOD_FIXED,$nowTime,$nowTime,
                    HM_Subject_SubjectModel::PERIOD_FREE,
                    HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT
                )))
            ->where($this->getService('User')->quoteInto(
                    array(
                        '(s.timetype IN (?)',
                        ' OR (s.timetype IN (?)',
                        ' AND GREATEST(UNIX_TIMESTAMP(s.begin), ?)',
                        ' < LEAST(UNIX_TIMESTAMP(s.end), ?) ))'
                    ),
                    array(
                        array(HM_Lesson_LessonModel::TIMETYPE_FREE, HM_Lesson_LessonModel::TIMETYPE_RELATIVE),
                        array(HM_Lesson_LessonModel::TIMETYPE_TIMES, HM_Lesson_LessonModel::TIMETYPE_DATES),
                        $nowTime,
                        $nowTime + $day
                    )));
        $lessons = $select->query()->fetchAll();
        $rows = array();
        //решил сделать так, а не условием в sql потому что толком не знаю, можно ли использовать конструкции case end
        //убирает лишние уроки с относительными диапазонами, которые закончились или не начинались
        if(!empty($lessons))
            foreach($lessons as $id => $lesson)
            {
                $passed = true;
                if($lesson['timetype'] == HM_Lesson_LessonModel::TIMETYPE_RELATIVE)
                {
                    if($lesson['startday'] > 0)
                    {
                        if($nowTime < $lesson['subject_start'] + $lesson['startday']*$day)
                        {
                            $passed = false;
                        }
                    }
                    else
                    {
                        if($nowTime < $lesson['subject_end'] + $lesson['startday']*$day)
                        {
                            $passed = false;
                        }
                    }
                    if($lesson['stopday'] > 0)
                    {
                        if($nowTime > $lesson['subject_start'] + $lesson['stopday']*$day)
                        {
                            $passed = false;
                        }
                    }
                    else
                    {
                        if($nowTime > $lesson['subject_end'] + $lesson['stopday']*$day)
                        {
                            $passed = false;
                        }
                    }
                }
                if($passed){
                    $rows[$id] = $lesson;
                }
            }
        return $rows;
    }

    /**
     * Функция выбирает занятия для слушателей.
     * алгоритм не изменен, просто вынесен в отдельный блок
     *
     * @auhtor Artem Smirnov <tonakai.personal@gmail.com>
     * @date 29.12.2012
     *
     * @param $begin
     * @param $end
     * @param $currentUserId
     *
     * @return array
     */
    public function getCurrentActiveLessonsStudent($begin,$end,$currentUserId)
    {
        $select = $this->getService('User')->getSelect();
        $select->from(array('s' => 'schedule'), array(
            's.*',
            'subject' => 'subjects.name',
            'regtime' => 'Students.time_registered',
            'mark' => 'scheduleID.V_STATUS',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(People.LastName, ' ') , People.FirstName), ' '), People.Patronymic)"),
        ))
            ->joinInner('subjects', 's.CID = subjects.subid', array())
            ->joinInner('scheduleID', 's.SHEID = scheduleID.SHEID')
            ->joinInner('Students', 'subjects.subid = Students.CID', array())
            ->joinInner('Teachers', 's.CID = Teachers.CID', array())
            ->joinInner('People','Teachers.MID = People.MID')
            ->where('Students.MID = ?', $currentUserId)
            ->where('scheduleID.MID = ?', $currentUserId)
            ->where('isfree = ? OR isfree IS NULL', HM_Lesson_LessonModel::MODE_PLAN);
        $subjectPeriodWhere = $this->getService('Subject')->quoteInto(
            array(
                '(subjects.period IN (?',
                ', ?))',
                ' OR ( subjects.period = ?',
                ' AND (UNIX_TIMESTAMP(subjects.begin) <= ?)',
                ' AND (UNIX_TIMESTAMP(subjects.end) > ?))',
//                        ' OR ( subjects.period = ?',
//                        ' AND (UNIX_TIMESTAMP(subjects.begin_planned) <= ?)',
//                        ' AND (UNIX_TIMESTAMP(subjects.end_planned) > ?))',
            ),
            array(
                HM_Subject_SubjectModel::PERIOD_FIXED,
                HM_Subject_SubjectModel::PERIOD_FREE,
                HM_Subject_SubjectModel::PERIOD_DATES,
                $begin,
                $begin,
//                        HM_Subject_SubjectModel::PERIOD_DATES,
//                        $begin,
//                        $begin,
            )
        );
        $select->where($subjectPeriodWhere);
        $select->where(
            $this->getService('User')->quoteInto(
                array(
                    '(s.timetype IN (?)',
                    ' OR (s.timetype IN (?)',
                    ' AND GREATEST(UNIX_TIMESTAMP(s.begin), ?)',
                    ' < LEAST(UNIX_TIMESTAMP(s.end), ?) ))'
                ),
                array(
                    array(HM_Lesson_LessonModel::TIMETYPE_FREE, HM_Lesson_LessonModel::TIMETYPE_RELATIVE),
                    array(HM_Lesson_LessonModel::TIMETYPE_TIMES, HM_Lesson_LessonModel::TIMETYPE_DATES),
                    $begin,
                    $end
                )
            )
        );
        return $select->query()->fetchAll();
    }

}