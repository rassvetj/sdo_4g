<?php

/*
 'level': int - уровень в иерархии отчетов
 'title': string - название отчета
 'type': int - группа отчета
 'start_tag': string - тег перед названием отчета
 'end_tag': string - тег после названия отчета
 'name': string - класс отчета
 'fields': array ['field', 'select', 'type':string|integer|date|datetime, 'format': используются макросы %d, %m, %Y, %H, %M, %s] - поля в отчёте
 'input': boolean - необходимы ли входные данные
 'input_fields': array ['name', 'type', 'select', 'relation', 'useless', 'presentation':select|'structure_select', 'filtered_select', 'format', 'dependent']
 'enable_counter' boolean - дополнительный столбец - счетчик
 'disable_filter': boolean - не выводить фильтры
 'sort_after_query': boolean - программная сортировка данных
 'enable_group': boolean - группировка строк
*/

$reports[] =
    array(
        'level' => 0,
        'title' => _("Отчеты по слушателям"),
        'type' => 1,
        'start_tag' => '<b>',
        'end_tag' => '</b>'
    );

$reports[] =
    array(
        'level' => 1,
        'title' => _("Сводные отчеты"),
        'type' => 1,
    );

$reports['UsersGroupSummaryReport'] =
    array(
        'level' => 2,
        'title' => _("Группы"),
        'type' => 1,
        'name' => 'UsersGroupSummaryReport',
        'fields' =>
            array(
//                '№' => array('field' => 'MID', 'type' => 'integer'),
                _("ФИО").' <br>'._("Слушателя") => array('field' => 'FIO'),
                _("Учётное имя (логин)") => array('field' => 'Login'),
                'E-mail' => array('field' => 'Email')
            ),
        'input' => true,
        'input_fields' => array(
            'gid' => array('name'=>_("Название группы"), 'presentation' => 'select')

        ),
        'enable_counter' => true
    );

$reports['SheduleMarksSummaryReport'] =
    array(
        'level' => 2,
        'title' => _("Оценки за занятия"),
        'type' => 1,
        'name' => 'SheduleMarksSummaryReport',
        'input' => true,
        'input_fields' => array(
            'status' => array('name' => _('Статус'), 'presentation' => 'select'),
            'CID' => array('name'=>_("Название курса"), 'presentation' => 'select', 'relation' => '='),
            'gid' => array('name'=>_("Название группы"), 'presentation' => 'select', 'relation' => '=', 'useless' => '-1'),
            'begin' => array('name' => _("С"), 'presentation' => 'date', 'format' => _('ДД.ММ.ГГГГ')),
            'end' => array('name' => _("По"), 'presentation' => 'date', 'format' => _('ДД.ММ.ГГГГ'))
        ),
        'sort_after_query' => true
    );

$reports['StudySummaryReport'] =
    array(
        'level' => 2,
        'title' => _("Статистика обучения"),
        'type' => 1,
        'name' => 'StudySummaryReport',
        'fields' =>
            array(
//                '№' => array('field' => 'MID', 'type' => 'integer'),
                _("ФИО").'<br>'._("Слушателя") => array('field' => 'FIO'),
                _("Выполнено занятий") => array('field' => 'completedClasses', 'type'=>'integer'),
                _("Не выполнено занятий") => array('field' => 'uncompletedClasses', 'type'=>'integer'),
                //_("Болел") => array('field' => 'ill', 'type'=>'integer'),
                _("Отсутствовал") => array('field' => 'absent', 'type'=>'integer'),
                _("% выполнения учебного плана") => array('field' => 'procent', 'type'=> 'integer')
            ),
        'input' => true,
        'input_fields' => array(
            'status' => array('name' => _('Статус'), 'presentation' => 'select'),
            'gid' => array('name'=>_("Название группы"), 'presentation' => 'select', 'relation' => '=')
        ),
        'sort_after_query' => true,
        'enable_counter' => true
    );

$reports['CourseStudySummaryReport'] =
    array(
        'level' => 2,
        'title' => _("Итоги обучения по курсу"),
        'type' => 1,
        'name' => 'CourseStudySummaryReport',
        'fields' =>
            array(
                _("ФИО").'<br>'._("Слушателя") => array('field' => 'fio'),
                _("Итоговая оценка") => array('field' => 'summary'),
            ),
        'input' => true,
        'input_fields' => array(
            'status' => array('name' => _('Статус'), 'presentation' => 'select'),
            'CID' => array('name'=>_("Курс"), 'presentation' => 'select', 'relation' => '='),
            'gid' => array('name'=>_("Название группы"), 'presentation' => 'select', 'relation' => '=')
        ),
        'sort_after_query' => true,
        'enable_counter' => true,
        'disable_filter' => false
    );    

/*
$reports['CompetenceSummaryReport'] = 
    array(
        'level' => 2,
        'title' => _("Критерии"),
        'type' => 1,
        'name' => 'CompetenceSummaryReport', 
        'fields' => 
            array(
                _("ФИО") => array('field' => 'people_name'),
                _("Должность") => array('field' => 'position'),
                _("Отдел") => array('field' => 'orgunit'),
                _("Уровень, %") => array('field' => 'sum', 'type' => 'integer')
            ),
        'input' => true,
        'input_fields' => array(
            'coid' => array('name'=>_("Компетенция"), 'presentation' => 'select', 'relation' => '='),
            'gid' => array('name'=>_("Название группы"), 'presentation' => 'select', 'relation' => '=', 'useless' => '0') 
        ),
        'sort_after_query' => true,
        'enable_counter' => true
    );

$reports['PollCompetenceSummaryReport'] = 
    array(
        'level' => 2,
        'title' => _("Оценки критериев (Опросы)"),
        'type' => 1,
        'name' => 'PollCompetenceSummaryReport', 
        'fields' => 
            array(
                _("ФИО") => array('field' => 'name'),
                _("Должность") => array('field' => 'position')
            ),
        'input' => true,
        'input_fields' => array(
            'pid' => array('name'=>_("Опрос"), 'presentation' => 'select', 'relation' => '='),
            'soid' => array('name'=>_("Подразделение"), 'presentation' => 'structure_select', 'relation' => '=', 'useless' => '0') 
        ),
        'sort_after_query' => true,
        'enable_counter' => true
    );
*/
$reports[] =
    array(
        'level' => 1,
        'title' => _("Персональные отчеты"),
        'type' => 1,
    );

$reports['RegInfoPersonalReport'] =
    array(
        'level' => 2,
        'title' => _("Регистрационная информация"),
        'type' => 1,
        'name' => 'RegInfoPersonalReport',
//        'fields' =>
//            array(
//                'ФИО' => array('field' => 'FIO'),
//                'Логин' => array('field' => 'Login'),
//                'E-mail' => array('field' => 'EMail'),
//            ),
        'input' => true,
        'input_fields' => array(
            'MID' => array('name'=>_("ФИО"), 'presentation' => 'filtered_select', 'relation' => '=', 'useless' => '-1')
        ),
        'sort_after_query' => true
    );
/*
$reports['BalancePersonalReport'] = 
    array(
        'level' => 2,
        'title' => _("Баланс слушателя"),
        'type' => 1,
        'name' => 'BalancePersonalReport', 
        'fields' => 
            array(
                '№ пп' => array('field' => 'n1'),
                '№ платёжного документа' => array('field' => 'n2'),
                'Дата' => array('field' => 'date', 'type' => 'date'),
                'Действие' => array('field' => 'action'),
                'Действие произвёл' => array('field' => 'who')
            ),
        'input' => true,
        'input_fields' => array(
            'track' => array('name' => _('Специальность'), 'presentation' => 'select', 'dependent' => true),
            'MID' => array('name'=>_("ФИО"), 'presentation' => 'filtered_select', 'relation' => '=', 'useless' => '-1')        
        ),
        'sort_after_query' => true
    );
 */   
if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM)
$reports['StudyPlanPersonalReport'] =
    array(
        'level' => 2,
        'title' => _("Учебный план"),
        'type' => 1,
        'name' => 'StudyPlanPersonalReport',
        'fields' =>
            array(
//                '№' => array('field' => 'CID', 'type' => 'integer'),
                _("Курс") => array('field' => 'Title'),
                _("Ведущий преподаватель") => array('field' => 'teachers'),
                _("Дата начала обучения") => array('field' => 'cBegin', 'type' => 'date'),
                _("Дата окончания обучения") => array('field' => 'cEnd', 'type' => 'date'),
                _("Условие окончания обучения") => array('field' => 'Condition'),
                _("Кол-во кредитов") => array('field' => 'credits_student', 'type' => 'integer'),
                _("Программа") => array('field' => 'program'),
            ),
        'input' => true,
        'input_fields' => array(
            'MID' => array('name'=>_("ФИО"), 'presentation' => 'filtered_select', 'relation' => '=')
        ),
        'sort_after_query' => true,
        'enable_counter' => true
    );
else
$reports['StudyPlanPersonalReport'] =
    array(
        'level' => 2,
        'title' => _("Учебный план"),
        'type' => 1,
        'name' => 'StudyPlanPersonalReport',
        'fields' =>
            array(
//                '№' => array('field' => 'CID', 'type' => 'integer'),
                _("Курс") => array('field' => 'Title'),
                _("Ведущий преподаватель") => array('field' => 'teachers'),
                _("Дата начала обучения") => array('field' => 'cBegin', 'type' => 'date'),
                _("Дата окончания обучения") => array('field' => 'cEnd', 'type' => 'date'),
//                _("Условие окончания обучения") => array('field' => 'Condition'),
            ),
        'input' => true,
        'input_fields' => array(
            'MID' => array('name'=>_("ФИО"), 'presentation' => 'filtered_select', 'relation' => '=')
        ),
        'sort_after_query' => true,
        'enable_counter' => true
    );

$reports['TestResultsPersonalReport'] =
    array(
        'level' => 2,
        'title' => _("Результаты тестирования"),
        'type' => 1,
        'name' => 'TestResultsPersonalReport',
        'fields' =>
            array(
                _("Дата попытки") => array('field' => 'stop', 'type' => 'date'),
                _("Набранный балл") => array('field' => 'bal', 'type' => 'double'),
                '%' => array('field' => 'procent', 'type' => 'integer')
            ),
        'input' => true,
        'input_fields' => array(
            'status' => array('name' => _('Статус'), 'presentation' => 'select', 'dependent' => true),
            'MID' => array('name'=>_("ФИО"), 'presentation' => 'filtered_select', 'relation' => '=', 'dependent' => true),
            'SHEID' => array('name'=>_("Название занятия"), 'presentation' => 'select', 'relation' => '=')
        ),
        'enable_counter' => true,
        'sort_after_query' => true
        /*,'plots' => array(
            array(
                'title'   => _('График'),
                'xtitle'  => _('Икс'),
                'ytitle'  => _('Игрик'),
                'type'    => 'line',
                'width'   => 400,
                'height'  => 300,
                'process' => array(
                    array(
                        'xfield'  => 'stop',
                        'yfield'  => 'bal',
                        'color'   => 'blue',
                        'legend'  => 'Легенда'
                    )
                )
            ),
            array(
                'title'   => _('Гистограмма 1'),
                'xtitle'  => _('Икс'),
                'ytitle'  => _('Игрик'),
                'type'    => 'bar',
                'width'   => 400,
                'height'  => 300,
                'process' => array(
                    array(
                        'xfield'  => 'stop',
                        'yfield'  => 'bal',
                        'color'   => 'blue',
                        'legend'  => 'Легенда 1'
                    )
                )
            ),
            array(
                'title'   => _('Гистограмма 2'),
                'xtitle'  => _('Икс'),
                'ytitle'  => _('Игрик'),
                'type'    => 'bar',
                'width'   => 400,
                'height'  => 300,
                'process' => array(
                    array(
                        'xfield'  => 'stop',
                        'yfield'  => 'bal',
                        'color'   => 'blue',
                        'legend'  => 'Легенда 1'
                    ),
                    array(
                        'xfield'  => 'stop',
                        'yfield'  => 'bal',
                        'color'   => 'red',
                        'legend'  => 'Легенда 2'
                    )
                )
            ),
            array(
                'title'   => _('Пырог'),
                'xtitle'  => _('Икс'),
                'ytitle'  => _('Игрик'),
                'type'    => 'pie',
                'width'   => 400,
                'height'  => 300,
                'process' => array(
                    array(
                        'xfield'  => 'bal', // data
                        'yfield'  => 'stop' // legends или 'legend' => array(...)
                    )
                )
            ),
            array(
                'title'   => _('Пырог 3D'),
                'xtitle'  => _('Икс'),
                'ytitle'  => _('Игрик'),
                'type'    => 'pie3d',
                'width'   => 400,
                'height'  => 300,
                'process' => array(
                    array(
                        'xfield'  => 'bal', // data
                        'yfield'  => 'stop' // legends или 'legend' => array(...)
                    )
                )
            ),
            array(
                'title'   => _('Звэзда'),
                'xtitle'  => _('Икс'),
                'ytitle'  => _('Игрик'),
                'type'    => 'radar',
                'width'   => 400,
                'height'  => 300,
                'process' => array(
                    array(
                        'xfield'  => 'bal', // data
                        'yfield'  => 'stop',
                        'color'   => 'red',
                        'legend'  => 'Легенда',
                        'fill'    => false,
                    )
                )
            )
        )*/
    );
$reports['TestCourseResultsPersonalReport'] =
    array(
        'level' => 2,
        'title' => _("Результаты тестирования по курсу"),
        'type' => 1,
        'name' => 'TestCourseResultsPersonalReport',
        'fields' =>
            array(
                _("Задание") => array('field' => 'test'),
                _("Дата попытки") => array('field' => 'stop', 'type' => 'date'),
                /*_("Набранный балл") => array('field' => 'bal', 'type' => 'double'),*/
                '%' => array('field' => 'procent'/*, 'type' => 'integer'*/)
            ),
        'input' => true,
        'input_fields' => array(
            'status' => array('name' => _('Статус'), 'presentation' => 'select', 'dependent' => true),
            'MID' => array('name'=>_("ФИО"), 'presentation' => 'filtered_select', 'relation' => '=', 'dependent' => true),
            'СID' => array('name'=>_("Курс"), 'presentation' => 'select', 'relation' => '=', 'dependent' => true)/*,
            'SHEID' => array('name'=>_("Название занятия"), 'presentation' => 'select', 'relation' => '=')*/
        ),
        'enable_counter' => true,
        'sort_after_query' => true
        /*,'plots' => array(
            array(
                'title'   => _('График'),
                'xtitle'  => _('Икс'),
                'ytitle'  => _('Игрик'),
                'type'    => 'line',
                'width'   => 400,
                'height'  => 300,
                'process' => array(
                    array(
                        'xfield'  => 'stop',
                        'yfield'  => 'bal',
                        'color'   => 'blue',
                        'legend'  => 'Легенда'
                    )
                )
            ),
            array(
                'title'   => _('Гистограмма 1'),
                'xtitle'  => _('Икс'),
                'ytitle'  => _('Игрик'),
                'type'    => 'bar',
                'width'   => 400,
                'height'  => 300,
                'process' => array(
                    array(
                        'xfield'  => 'stop',
                        'yfield'  => 'bal',
                        'color'   => 'blue',
                        'legend'  => 'Легенда 1'
                    )
                )
            ),
            array(
                'title'   => _('Гистограмма 2'),
                'xtitle'  => _('Икс'),
                'ytitle'  => _('Игрик'),
                'type'    => 'bar',
                'width'   => 400,
                'height'  => 300,
                'process' => array(
                    array(
                        'xfield'  => 'stop',
                        'yfield'  => 'bal',
                        'color'   => 'blue',
                        'legend'  => 'Легенда 1'
                    ),
                    array(
                        'xfield'  => 'stop',
                        'yfield'  => 'bal',
                        'color'   => 'red',
                        'legend'  => 'Легенда 2'
                    )
                )
            ),
            array(
                'title'   => _('Пырог'),
                'xtitle'  => _('Икс'),
                'ytitle'  => _('Игрик'),
                'type'    => 'pie',
                'width'   => 400,
                'height'  => 300,
                'process' => array(
                    array(
                        'xfield'  => 'bal', // data
                        'yfield'  => 'stop' // legends или 'legend' => array(...)
                    )
                )
            ),
            array(
                'title'   => _('Пырог 3D'),
                'xtitle'  => _('Икс'),
                'ytitle'  => _('Игрик'),
                'type'    => 'pie3d',
                'width'   => 400,
                'height'  => 300,
                'process' => array(
                    array(
                        'xfield'  => 'bal', // data
                        'yfield'  => 'stop' // legends или 'legend' => array(...)
                    )
                )
            ),
            array(
                'title'   => _('Звэзда'),
                'xtitle'  => _('Икс'),
                'ytitle'  => _('Игрик'),
                'type'    => 'radar',
                'width'   => 400,
                'height'  => 300,
                'process' => array(
                    array(
                        'xfield'  => 'bal', // data
                        'yfield'  => 'stop',
                        'color'   => 'red',
                        'legend'  => 'Легенда',
                        'fill'    => false,
                    )
                )
            )
        )*/
    );

$reports['AnswersStatsPersonalReport'] =
    array(
        'level' => 2,
        'title' => _("Статистика ответов"),
        'type' => 1,
        'name' => 'AnswersStatsPersonalReport',
        'fields' =>
            array(
                _("Вопрос") => array('field' => 'q'),
                _("Тип вопроса") => array('field' => 'qtype'),
                _("Диапазон баллов") => array('field' => 'bals'),
                _("Ответы обучаемого") => array('field' => 'answ'),
                _("Балл") => array('field' => 'bal', 'type' => 'integer'),
                '%' => array('field' => 'procent', 'type' => 'integer')
            ),
        'input' => true,
        'input_fields' => array(
            'status' => array('name' => _('Статус'), 'presentation' => 'select', 'dependent' => true),
            'MID' => array('name'=>_("ФИО"), 'presentation' => 'filtered_select', 'relation' => '=', 'dependent' => true),
            'SHEID' => array('name'=>_("Название занятия"), 'presentation' => 'select', 'relation' => '=', 'dependent' => true),
            'stid' => array('name'=>_("Попытка"), 'presentation' => 'select', 'relation' => '=')
        ),
        'enable_counter' => true,
        'sort_after_query' => true
    );

$reports['ScormStudyPersonalReport'] =
    array(
        'level' => 2,
        'title' => _("Статистика изучения материалов") . ' (SCORM, AICC)',
        'type' => 1,
        'name' => 'ScormStudyPersonalReport',
        'fields' =>
            array(
                _("Название уч. модуля") => array('field' => 'Title'),
                _("Кол-во запусков") => array('field' => 'runs', 'type' => 'integer'),
                _("Суммарное время изучения") => array('field' => 'time'),
            ),
        'input' => true,
        'input_fields' => array(
            'status' => array('name' => _('Статус'), 'presentation' => 'select', 'dependent' => true),
            'MID' => array('name'=>_("ФИО"), 'presentation' => 'filtered_select', 'relation' => '=', 'dependent' => true),
            'CID' => array('name'=>_("Название курса"), 'presentation' => 'select', 'relation' => '=')
        ),
        'enable_counter' => true,
        'sort_after_query' => true
    );

/*$reports['PollCompetencePersonalReport'] =
    array(
        'level' => 2,
        'title' => _("Оценки критериев (Опросы)"),
        'type' => 1,
        'name' => 'PollCompetencePersonalReport',
        'fields' =>
            array(
                _("Вид оценки") => array('field' => 'role'),
                _("Критерий") => array('field' => 'name'),
                _("Уровень") => array('field' => 'level', 'type' => 'integer')
            ),
        'input' => true,
        'input_fields' => array(
            'pid' => array('name'=>_("Опрос"), 'presentation' => 'select', 'relation' => '=', 'dependent' => true),
            'mid' => array('name'=>_("Обучаемый"), 'presentation' => 'select', 'relation' => '=')
        ),
        'sort_after_query' => true,
        'enable_counter' => true
    );
*/
$reports[] =
    array(
        'level' => 0,
        'title' => _("Отчеты по организации процесса обучения"),
        'type' => 2,
        'start_tag' => '<b>',
        'end_tag' => '</b>'
    );

$reports[] =
    array(
        'level' => 1,
        'title' => _("Сводные"),
        'type' => 2,
    );

$reports['CoursesSummaryReport'] =
    array(
        'level' => 2,
        'title' => _("Курсы"),
        'type' => 2,
        'name' => 'CoursesSummaryReport',
        'fields' =>
            array(
//                '№' => array('field' => 'CID', 'type' => 'integer'),
                _("Название") => array('field' => 'Title'),
                _("Тип регистрации") => array('field' => 'RegType'),
                _("Дата начала") => array('field' => 'cBegin'),
                _("Дата окончания") => array('field' => 'cEnd'),
                _("Ведущий преподаватель") => array('field' => 'teachers'),
                //_("Итоговый контроль") => array('field' => 'control'),
                _("Статус") => array('field' => 'Status'),
                _("Кол-во обучаемых") => array('field' => 'students', 'type' => 'integer')
            ),
        'sort_after_query' => true,
        'enable_counter' => true
    );

if (defined('USE_SPECIALITIES') && USE_SPECIALITIES) {
if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM) {
$reports['SpecialitySummaryReport'] =
    array(
        'level' => 2,
        'title' => _("Специальности"),
        'type' => 2,
        'name' => 'SpecialitySummaryReport',
        'fields' =>
            array(
//                '№' => array('field' => 'trid', 'type' => 'integer'),
                _("Название") => array('field' => 'name'),
                //_("Стоимость") => array('field' => 'totalcost', 'type' => 'integer'),
                _("Кол-во семестров") => array('field' => 'semestrs', 'type' => 'integer'),
                _("Кол-во обучаемых") => array('field' => 'students', 'type' => 'integer'),
                _("Кол-во кредитов") => array('field' => 'credits', 'type' => 'integer')
            ),
        'enable_counter' => true
    );
} else {
$reports['SpecialitySummaryReport'] =
    array(
        'level' => 2,
        'title' => _("Специальности"),
        'type' => 2,
        'name' => 'SpecialitySummaryReport',
        'fields' =>
            array(
//                '№' => array('field' => 'trid', 'type' => 'integer'),
                _("Название") => array('field' => 'name'),
                //_("Стоимость") => array('field' => 'totalcost', 'type' => 'integer'),
                _("Кол-во семестров") => array('field' => 'semestrs', 'type' => 'integer'),
                _("Кол-во обучаемых") => array('field' => 'students', 'type' => 'integer')
            ),
        'enable_counter' => true
    );
}
}

$reports['TeachersSummaryReport'] =
    array(
        'level' => 2,
        'title' => _("Преподаватели"),
        'type' => 2,
        'name' => 'TeachersSummaryReport',
        'fields' =>
            array(
//                '№' => array('field' => 'MID', 'type' => 'integer'),
                _("ФИО") => array('field' => 'FIO'),
                _("Учетное имя (логин)") => array('field' => 'Login'),
                'E-mail' => array('field' => 'EMail'),
                _("Кол-во курсов") => array('field' => 'Courses', 'type' => 'integer')
            ),
        'enable_counter' => true
    );

$reports[] =
    array(
        'level' => 1,
        'title' => _("Подробные"),
        'type' => 2,
    );

if (defined('USE_SPECIALITIES') && USE_SPECIALITIES) {
if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM) {
$reports['SpecialityDetailedReport'] =
    array(
        'level' => 2,
        'title' => _("Планы специальностей"),
        'type' => 2,
        'name' => 'SpecialityDetailedReport',
        'fields' =>
            array(
//                '№' => array('field' => 'cid', 'type' => 'integer'),
                _("Название курса") => array('field' => 'CourseTitle'),
                _("Семестр") => array('field' => 'level', 'type' => 'integer'),
                _("Количество обучаемых") => array('field' => 'students', 'type' => 'integer'),
                _("Количество кредитов") => array('field' => 'credits_student', 'type' => 'integer')
            ),
        'input' => true,
        'input_fields' => array(
            'trid' => array('name'=>_("Название специальности"), 'presentation' => 'select', 'relation' => '=')
        ),
        'enable_counter' => true

    );
} else {
$reports['SpecialityDetailedReport'] =
    array(
        'level' => 2,
        'title' => _("Планы специальностей"),
        'type' => 2,
        'name' => 'SpecialityDetailedReport',
        'fields' =>
            array(
//                '№' => array('field' => 'cid', 'type' => 'integer'),
                _("Название курса") => array('field' => 'CourseTitle'),
                _("Семестр") => array('field' => 'level', 'type' => 'integer'),
                _("Количество обучаемых") => array('field' => 'students', 'type' => 'integer')
            ),
        'input' => true,
        'input_fields' => array(
            'trid' => array('name'=>_("Название специальности"), 'presentation' => 'select', 'relation' => '=')
        ),
        'enable_counter' => true
    );
}
}

$reports['TeachersLoadingReport'] =
    array(
        'level' => 2,
        'title' => _("Учебная нагрузка преподавателей (в часах)"),
        'type' => 2,
        'name' => 'TeachersLoadingReport',
        'input' => true,
        'input_fields' => array(
            'begin' => array('name'=>_("С"), 'presentation' => 'date', 'relation' => '>=', 'format' => _('ДД.ММ.ГГГГ')),
            'end' => array('name'=>_("По"), 'presentation' => 'date', 'relation' => '<=', 'format' => _('ДД.ММ.ГГГГ'))
        ),
        'enable_counter' => false,
        'sort_after_query' => true

    );

$reports['CoursePlanReport'] =
    array(
        'level' => 2,
        'title' => _("Расписание по курсу"),
        'type' => 2,
        'name' => 'CoursePlanReport',
        'fields' =>
            array(
                _("Дата") => array('field' => 'begin', 'type' => 'date'),
                _("Периодичность") => array('field' => 'per'),
                _("Название занятия") => array('field' => 'title'),
                _("Преподаватель") => array('field' => 'teacher'),
                _("Условие") => array('field' => 'cond'),
                _("Кол-во обучаемых") => array('field' => 'students','type'=>'integer'),
            ),
        'input' => true,
        'input_fields' => array(
            'CID' => array('name'=>_("Название курса"), 'presentation' => 'select', 'relation' => '='),
            'gid' => array('name'=>_("Название группы"), 'presentation' => 'select', 'relation' => '=', 'useless' => '-1'),
            'begin' => array('name' => _("С"), 'presentation' => 'date', 'format' => _('ДД.ММ.ГГГГ')),
            'end' => array('name' => _("По"), 'presentation' => 'date', 'format' => _('ДД.ММ.ГГГГ'))
        ),
        'enable_counter' => true,
        'sort_after_query' => true
    );

$reports[] =
    array(
        'level' => 0,
        'title' => _("Отчеты по контенту"),
        'type' => 3,
        'start_tag' => '<b>',
        'end_tag' => '</b>'
    );

$reports[] =
    array(
        'level' => 1,
        'title' => _("Сводные"),
        'type' => 3,
    );

$reports['CourseContentSummaryReport'] =
    array(
        'level' => 2,
        'title' => _("Курсы"),
        'type' => 3,
        'name' => 'CourseContentSummaryReport',
        'fields' =>
            array(
//                '№' => array('field' => 'CID', 'type' => 'integer'),
                _("Название") => array('field' => 'title'),
                _("Тип") => array('field' => 'type'),
                _("Количество материалов") => array('field' => 'modules', 'type' => 'integer'),
                _("Количество внешних програм") => array('field' => 'runs', 'type' => 'integer'),
                _("Количество заданий") => array('field' => 'tests', 'type' => 'integer')
            ),
        'sort_after_query' => true,
        'enable_counter' => true
    );

$reports['MaterialAttemptsReport'] =
    array(
        'level' => 2,
        'title' => _("Статистика обращений к блокам материалов"),
        'type' => 3,
        'name' => 'MaterialAttemptsReport',
        'fields' =>
            array(
                _("ФИО") => array('field' => 'fio'),
                _("Материал") => array('field' => 'material'),
                _("Количество обращений") => array('field' => 'attempts', 'type'=>'integer'),
            ),
        'input' => true,
        'input_fields' => array(
            'begin' => array('name'=>_("С"), 'presentation' => 'date', 'format'=>_("ДД.ММ.ГГГГ")),
            'end' => array('name'=>_("По"), 'presentation' => 'date', 'format'=>_("ДД.ММ.ГГГГ"))
        ),
        'enable_counter' => true,
        'sort_after_query' => true
    );

$reports['ScormStudySummaryReport'] =
    array(
        'level' => 2,
        'title' => _("Статистика изучения материалов").' (SCORM, AICC)',
        'type' => 3,
        'name' => 'ScormStudySummaryReport',
        'fields' =>
            array(
                _("Название уч. модуля") => array('field' => 'Title'),
                _("Кол-во запусков") => array('field' => 'runs', 'type' => 'integer'),
                _("Среднее время изучения") => array('field' => 'time'),
            ),
        'input' => true,
        'input_fields' => array(
            'CID' => array('name'=>_("Название курса"), 'presentation' => 'select', 'relation' => '=')
        ),
        'enable_counter' => true,
        'sort_after_query' => true
    );

$reports['TestsSummaryReport'] =
    array(
        'level' => 2,
        'title' => _("Статистика тестирования"),
        'type' => 3,
        'name' => 'TestsSummaryReport',
        'fields' =>
            array(
//                '№' => array('field' => 'tid', 'type' => 'integer'),
                _("Название теста") => array('field' => 'title'),
                _("Кол-во вопросов") => array('field' => 'questions', 'type' => 'integer'),
                _("Кол-во запусков") => array('field' => 'runs', 'type' => 'integer'),
                _("Среднее время прохождения") => array('field' => 'avgtime'),
                _("Средней процент выполнения") => array('field' => 'avgprocent'),
                _("Средняя оценка") => array('field' => 'avgbal')
            ),
        'input' => true,
        'input_fields' => array(
            'CID' => array('name'=>_("Название курса"), 'presentation' => 'select', 'relation' => '=')
        ),
        'sort_after_query' => true,
        'enable_counter' => true
    );

$reports['QuestionAnswersReport'] =
    array(
        'level' => 2,
        'title' => _("Статистика ответов на вопросы"),
        'type' => 3,
        'name' => 'QuestionAnswersReport',
        'fields' =>
            array(
                _("Текст вопроса") => array('field' => 'q'),
                _("Всего задано") => array('field' => 'count', 'type'=>'integer'),
                _("Верно ответили") => array('field' => 'true', 'type'=>'integer'),
                _("Неверно ответили") => array('field' => 'false', 'type'=>'integer'),
                _("Средний процент") => array('field' => 'procent', 'type'=>'integer')
            ),
        'input' => true,
        'input_fields' => array(
            'CID' => array('name'=>_("Название курса"), 'presentation' => 'select', 'relation' => '=')
        ),
        'enable_counter' => true,
        'sort_after_query' => true
    );

$reports[] =
    array(
        'level' => 1,
        'title' => _("Подробные"),
        'type' => 3
    );

$reports['QuestionAnswersDetailedReport'] =
    array(
        'level' => 2,
        'title' => _("Ответы на вопрос теста"),
        'type' => 3,
        'name' => 'QuestionAnswersDetailedReport',
        'fields' =>
            array(
                _("Текст ответа") => array('field' => 'text'),
                _("Верно") => array('field' => 'true'),
                _("Кол-во таких ответов") => array('field' => 'count', 'type' => 'integer')
            ),
        'input' => true,
        'input_fields' => array(
            'CID' => array('name'=>_("Название курса"), 'presentation' => 'select', 'relation' => '=', 'dependent' => true),
            'tid' => array('name'=>_("Название теста"), 'presentation' => 'select', 'relation' => '=', 'dependent' => true),
            'kod' => array('name'=>_("Текст вопроса"), 'presentation' => 'select', 'relation' => '=')
        ),
        'enable_counter' => true,
        'sort_after_query' => true
    );

$reports[] =
    array(
        'level' => 0,
        'title' => _("Административные отчеты"),
        'type' => 4,
        'start_tag' => '<b>',
        'end_tag' => '</b>'
    );

$reports[] =
    array(
        'level' => 1,
        'title' => _("Сводные"),
        'type' => 4
    );
/*
$reports['UsersSummaryReport'] =
    array(
        'level' => 2,
        'title' => _("Учетные записи"),
        'type' => 4,
        'name' => 'UsersSummaryReport',
        'fields' =>
            array(
                'ID' => array('field' => 'MID', 'type' => 'integer'),
                _("Логин") => array('field' => 'Login'),
                _("ФИО") => array('field' => 'FIO'),
                _("Кол-во входов") => array('field' => 'countlogin', 'type' => 'integer'),
                _("Последний вход") => array('field' => 'last'),
            ),
        'input' => true,
        'input_fields' => array(
            'role' => array('name'=>_("Роль"), 'presentation' => 'select', 'relation' => '=')
        ),
        'enable_counter' => true
//        'sort_after_query' => true
    );
*/
$reports['UserSessionsReport'] =
    array(
        'level' => 2,
        'title' => _("Пользовательские сессии"),
        'type' => 4,
        'name' => 'UserSessionsReport',
        'fields' =>
            array(
                _("Дата начала") => array('field' => 'start','type' => 'date'),
                _("Дата окончания") => array('field' => 'stop','type' => 'date'),
                _("Логин") => array('field' => 'Login'),
                _("IP-адрес") => array('field' => 'ip')
            ),
        'input' => true,
        'input_fields' => array(
            'start' => array('name'=>_("С"), 'presentation' => 'date', 'format'=>_('ДД.ММ.ГГГГ')),
            'stop' => array('name'=>_("По"), 'presentation' => 'date', 'format'=>_('ДД.ММ.ГГГГ'))
        ),
        'enable_counter' => true,
        'sort_after_query' => true
    );

$reports['InstalledModulesReport'] =
    array(
        'level' => 2,
        'title' => _("Установленные модули в системе"),
        'type' => 4,
        'name' => 'InstalledModulesReport',
        'fields' =>
            array(
                'ID' => array('field'=>'id'),
                _("Название") => array('field' => 'name'),
                _("Тип") => array('field' => 'type'),
                $GLOBALS['profiles_basic_aliases'][PROFILE_GUEST] => array('field' => 'profile_guest'),
                $GLOBALS['profiles_basic_aliases'][PROFILE_STUDENT] => array('field' => 'profile_student'),
                $GLOBALS['profiles_basic_aliases'][PROFILE_TEACHER] => array('field' => 'profile_teacher'),
                $GLOBALS['profiles_basic_aliases'][PROFILE_DEAN] => array('field' => 'profile_dean'),
                $GLOBALS['profiles_basic_aliases'][PROFILE_DEVELOPER] => array('field' => 'profile_developer'),
                $GLOBALS['profiles_basic_aliases'][PROFILE_MANAGER] => array('field' => 'profile_manager'),
                $GLOBALS['profiles_basic_aliases'][PROFILE_ADMIN] => array('field' => 'profile_admin')
            ),
        'input' => false,
        'enable_counter' => true,
        'sort_after_query' => true
    );

$reports['ExamSheetsReport'] = 
    array(
        'level' => 1,
        'title' => _("Экзаменационный лист"),
        'type' => 6,
        'name' => 'ExamSheetsReport', 
        'fields' => 
            array(
                _("Семестр")            => array('field' => 'term'),
                _("Учебный год")        => array('field' => 'year'),
                //_("Форма контроля")     => array('field' => 'checkup'),
                _("Факультет")          => array('field' => 'department'),
                _("Курс")               => array('field' => 'course'),
                _("Группа")             => array('field' => 'group'),
                _("Дисциплина")         => array('field' => 'discipline'),
                _("Количество часов")   => array('field' => 'hours'),
                _("Экзаменатор")        => array('field' => 'examiner'),
                _("ФИО")                => array('field' => 'fio'),
                _("Рецензия")           => array('field' => 'comments'),
                _("Оценка")             => array('field' => 'bal'),
                _("Дата сдачи")         => array('field' => 'time', 'type' => 'date')
            ),
        'input'        => true,
        'input_fields' => array(
                'MID'    => array('name'=>_("ФИО"), 'presentation' => 'filtered_select', 'relation' => '=', 'dependent' => true),
                'course' => array('name'=>_("Курс"), 'presentation' => 'select', 'relation' => '=')
        ),
        'sort_after_query' => true,
        'enable_counter'   => false
    );
    
$reports['StudentCardReport'] = 
    array(
        'level' => 1,
        'title' => _("Учебная карточка пользователя"),
        'type' => 6,
        'name' => 'StudentCardReport', 
        'fields' => 
            array(
                _("Факультет")                  => array('field' => 'department'),
                _("Специальность")              => array('field' => 'track'),                
                _("Фамилия")                    => array('field' => 'LastName'),
                _("Имя")                        => array('field' => 'FirstName'),
                _("Отчество")                   => array('field' => 'Patronymic'),
                _("Прошлая фамилия")            => array('field' => 'old_last_name'),                
                _("Пол")                        => array('field' => 'sex'),                
                _("День рождения")              => array('field' => 'dayB'),                
                _("Месяц рождения")             => array('field' => 'dayM'),                
                _("Год рождения")               => array('field' => 'dayY'),                
                _("Место рождения")             => array('field' => 'place_of_birth'),
                _("Гражданство")                => array('field' => 'citizenship'),
                _("Паспорт (серия)")            => array('field' => 'serial'),
                _("Паспорт (номер)")            => array('field' => 'number'),
                _("Паспорт (день выдачи)")      => array('field' => 'day'),
                _("Паспорт (месяц выдачи)")     => array('field' => 'month'),
                _("Паспорт (год выдачи)")       => array('field' => 'year'),                
                _("Паспорт (кем выдан)")        => array('field' => 'otdel_mvd'),
                _("Страна")                     => array('field' => 'country'),
                _("Регион")                     => array('field' => 'region'),
                _("Город")                      => array('field' => 'city'),
                _("Улица")                      => array('field' => 'street'),
                _("Номер дома")                 => array('field' => 'bldng'),
                _("Корпус")                     => array('field' => 'section'),
                _("Номер квартиры")             => array('field' => 'room'),
                _("Домаашний почтовый индекс")  => array('field' => 'index'),
                _("Доп. Страна")                => array('field' => 'country2'),
                _("Доп. Регион")                => array('field' => 'region2'),
                _("Доп. Город")                 => array('field' => 'city2'),
                _("Доп. Улица")                 => array('field' => 'street2'),
                _("Доп. Номер дома")            => array('field' => 'bldng2'),
                _("Доп. Корпус")                => array('field' => 'section2'),
                _("Доп. Номер квартиры")        => array('field' => 'room2'),
                _("Доп. почтовый индекс")       => array('field' => 'index2'),
                _("Домашний телефон")           => array('field' => 'PhoneNumber'),
                _("Сотовый телефон")            => array('field' => 'CellularNumber'),
                _("E-mail")                     => array('field' => 'email'),
                _("Номер ICQ")                  => array('field' => 'ICQNumber'),
                _("Факс")                       => array('field' => 'Fax'),
                _("Место работы")               => array('field' => 'work_place'),
                _("Адрес места работы")         => array('field' => 'work_adress'),
                _("Почтовый индекс")            => array('field' => 'work_index'),
                _("Рабочий телефон")            => array('field' => 'work_phone'),
                _("Должность")                  => array('field' => 'work_post'),                
                _("Семейное положение")         => array('field' => 'family_state'),
                _("Образование")                => array('field' => 'education_detail'),
                _("Диплом (серия)")             => array('field' => 'education_doc_serial'),
                _("Диплом (номер)")             => array('field' => 'education_doc_number'),
                _("Диплом (дата)")              => array('field' => 'education_end_y'),
                
                _("Результаты вступительных испытаний") => array('field' => 'introductory_tests'),
                _("Учебный план")                       => array('field' => 'curriculum'),
                //_("Факультативные дисциплины")          => array('field' => 'facultative'),
                //_("Практики")                           => array('field' => 'practice'),
                _("Гос. экзамены")                      => array('field' => 'examination'),
                _("Курс")                               => array('field' => 'course')
            ),
        'input'        => true,
        'input_fields' => array(
                'MID'    => array('name'=>_("ФИО"), 'presentation' => 'filtered_select', 'relation' => '=')                
        ),
        'sort_after_query' => true,
        'enable_counter'   => false
    );   
    
$reports['LevelUpNote'] = 
    array(
        'level' => 1,
        'title' => _("Записка о переводе"),
        'type' => 6,
        'name' => 'LevelUpNote', 
        'fields' => 
            array(                
                _("Факультет")          => array('field' => 'department'),                
                _("Текущая дата")       => array('field' => 'date'),                
                _("Список студентов")   => array('field' => 'students')                
            ),
        'input'        => true,
        'input_fields' => array(
                'MID'    => array('name'=>_("Слушатели"), 'presentation' => 'multi_select', 'relation' => '=' )                
        ),
        'sort_after_query' => true,
        'enable_counter'   => false
    );
$reports['ExpelNote'] = 
    array(
        'level' => 1,
        'title' => _("Записка об отчислении"),
        'type' => 6,
        'name' => 'ExpelNote', 
        'fields' => 
            array(                
                _("Факультет")          => array('field' => 'department'),                
                _("Текущая дата")       => array('field' => 'date'),                
                _("Список студентов")   => array('field' => 'students')                
            ),
        'input'        => true,
        'input_fields' => array(
                'track'  => array('name'=>_("Специальность"), 'presentation' => 'select', 'relation' => '=', 'dependent' => true),
                'MID'    => array('name'=>_("Слушатели"), 'presentation' => 'multi_select', 'relation' => '=')
        ),
        'sort_after_query' => true,
        'enable_counter'   => false
    );

$reports['CurriculumReport'] = 
    array(
        'level' => 1,
        'title' => _("План учебного процесса"),
        'type' => 6,
        'name' => 'CurriculumReport', 
        'fields' => 
            array(                
                _("Учебный план") => array('field' => 'curriculum')
            ),
        'input'        => true,
        'input_fields' => array(
                'track'  => array('name'=>_("Специальность"), 'presentation' => 'select', 'relation' => '=')
        ),
        'sort_after_query' => true,
        'enable_counter'   => false
    );
    
$reports['EnterorderReport'] = 
    array(
        'level' => 1,
        'title' => _("Приказ о зачислении"),
        'type' => 6,
        'name' => 'EnterorderReport', 
        'fields' => 
            array(                
                _("Список студентов") => array('field' => 'students'),
                _("Дата") => array('field' => 'date'),
                _("Специальность") => array('field' => 'track')
                ),
        'input'        => true,
        'input_fields' => array(
                'track'  => array('name'=>_("Специальности и направления"), 'presentation' => 'select', 'dependent' => true),
                'MID'    => array('name'=>_("Слушатели"), 'presentation' => 'multi_select', 'relation' => '=')
        ),
        'sort_after_query' => true,
        'enable_counter'   => false
    );    
    
$reports['LessonsListReport'] = 
    array(
        'level' => 1,
        'title' => _("Перечень занятий по дисциплине"),
        'type' => 6,
        'name' => 'LessonsListReport', 
        'fields' => 
            array(                
                _("Список занятий") => array('field' => 'lessonsList')
            ),
        'input'        => true,
        'input_fields' => array(
                'course'  => array('name'=>_("Курс"), 'presentation' => 'select', 'relation' => '=')
        ),
        'sort_after_query' => true,
        'enable_counter'   => false
    );
       
function parseInputData($inputData, $inputFields) {

    $ret = false;

    if (is_array($inputData) && count($inputData)
    && is_array($inputFields) && count($inputFields)) {
        foreach($inputFields as $k=>$v) {
            if (isset($inputData[$k])) {

                switch($v['type']) {

                    case 'integer':
                    $ret[$k] = (int) $inputData[$k];
                    break;

                    case 'double':
                    $ret[$k] = (double) $inputData[$k];
                    break;

                    case 'boolean':
                    $ret[$k] = (boolean) $inputData[$k];
                    break;

                    default:
                        if (is_array($inputData[$k])){
                            foreach ($inputData[$k] as $key=>$value){
                                $ret[$k][$key] = (string) trim(strip_tags($value));                                
                            }                            
                        }else {
                    $ret[$k] = (string) trim(strip_tags($inputData[$k]));
                        }
                    break;

                }

                if ($v['presentation'] == 'structure_select') {
                    $ret[$k.'_parent'] = (int) getField('structure_of_organ','owner_soid','soid',(int) $ret[$k]);
                }


            }


        }

    }

    return $ret;

}

function parseFilterData($filterData, $filterFields) {

    $ret = false;

    if (is_array($filterData) && count($filterData)
    && is_array($filterFields) && count($filterFields)) {

        foreach($filterFields as $v) {

            $field = 'filter_'.$v['field'];

            if (isset($filterData[$field]) && ($filterData[$field] != '')) {

                switch($v['type']) {
/*
                    case 'integer':
                    $ret[$v['field']] = (int) $filterData[$field];
                    break;

                    case 'double':
                    $ret[$v['field']] = (double) $filterData[$field];
                    break;

                    case 'boolean':
                    $ret[$v['field']] = (boolean) $filterData[$field];
                    break;
*/
                    default:
                    if (($filterData[$field][0] == '<') && (strchr($filterData[$field],'>') === false)) {
                        $ret[$v['field']] = (string) '<'.trim(strip_tags(substr($filterData[$field],1)));
                    } else {
                        $ret[$v['field']] = (string) trim(strip_tags($filterData[$field]));
                    }

                    break;

                }

            }

        }

    } else {

        if (is_array($filterData) && count($filterData)) {

            foreach($filterData as $k=>$v) {

                if (!empty($v) && (strstr($k,'filter_')!==false)) {

                    //$k = str_replace('_',' ',$k);
                    $ret[substr($k,7)] = (string) trim(strip_tags($v));

                }

            }

        }

    }

//    pr($ret);

    return $ret;

}

function getReportInputForm($reportName,$inputData=false) {

    global $reports;

    $ret = false;


    $reportFile = "lib/rep/reports/".$reportName.".class.php";

    $reportDataClassName = "C".$reportName;

    if (file_exists($reportFile)) {

        include_once($reportFile);

        if (is_array($reports[$reportName]['input_fields']))
        foreach($reports[$reportName]['input_fields'] as $k=>$v) {

            //if ($v['presentation']!='filtered_select') {
            $reportInputField = call_user_func(array($reportDataClassName,'getReportInputField'),$k,$inputData);
            //}

            $ret[$k] = $reportInputField;

        }

    }

    return $ret;

}

/*
function getReportInputForm($reportName,$inputData=false) {

    global $s, $reports;

    $html = '';


    $reportFile = "lib/rep/reports/".$reportName.".class.php";

    $reportDataClassName = "C".$reportName;

    if (file_exists($reportFile)) {

        include_once($reportFile);
        if (is_array($reports[$reportName]['input_fields']))
        foreach($reports[$reportName]['input_fields'] as $k=>$v) {
            $html .= "<tr><td>{$v['name']}: </td>";
            $reportInputFieldData = call_user_func(array($reportDataClassName,'getReportInputField'),$k,$inputData[$k]);
            if (isset($reportInputFieldData['html']))
            $html .= "<td>".$reportInputFieldData['html']."</td>";
            else
            $html .= "<td>".$reportInputFieldData."</td>";
            $html .= "</tr>";
            if (isset($reportInputFieldData['inputDataValues']))
            $s['reports']['current']['inputDataValues'][$k] = $reportInputFieldData['inputDataValues'];

        }

    }



    return $html;

}    */

function mask2pattern($mask)
{
    $mask = '*' . $mask . '*';
    if ((strstr($mask,'?')!==false) || (strstr($mask,'*')!==false)) {
    $mask = strtolower($mask);
    $u = '';
    for($i=0;$i<strlen($mask);$i++)
    {
        $char = $mask{$i};
        $part = '';
        if ($char == '?') {$part = '.';}
        elseif ($char == '*') {$part = '.*';}
        else {$part = $char;}
        $pattern .= $part;
    }
    } else $pattern = $mask;
    return '~^'.$pattern.'$~is';
}

function getReport() {

    global $s, $reports;

        $reportDataClassName = "C".$s['reports']['current']['name'];
        $reportFile = "lib/rep/reports/".$s['reports']['current']['name'].".class.php";

        if (file_exists($reportFile)) {

            include_once($reportFile);
            /**
            * Получение данных для отчета
            */
            $reportData = new $reportDataClassName(
                $reports[$s['reports']['current']['name']]['fields'],
                $reports[$s['reports']['current']['name']]['input_fields'],
                $s['reports']['current']['inputData'],
                $s['reports']['current']['filterData'],
                $s['reports']['current']['commonCalcFields'],
                $s['reports']['current']['sort'],
                $reports[$s['reports']['current']['name']]['sort_after_query']
                );

            $report = new CReport(
                $reports[$s['reports']['current']['name']]['enable_counter'],
                $reports[$s['reports']['current']['name']]['disable_sort'],
                $reports[$s['reports']['current']['name']]['disable_filter'],
                $reports[$s['reports']['current']['name']]['enable_group']
            );

            $report->setData($reportData->getReportData(),
                            $reportData->getHeaders(),
                            $reportData->getFields(),
                            $reportData->getFilterData(),
                            $reportData->getCommonCalcData(),
                            $reportData->getAdditionalData(),
                            $s['reports']['current']['sort'],
                            $reports[$s['reports']['current']['name']]['fields'],
                            $reports[$s['reports']['current']['name']]['plots']);

        }

        return $report;

}

function getSubjectArea($inputFields, $inputData, $inputDataValues=false) {

    $ret = false;

    if (is_array($inputFields) && count($inputFields)
    && is_array($inputData) && count($inputData)) {

        foreach($inputFields as $k=>$v) {

            if (isset($inputData[$k])) {

                if (isset($v['useless']) && ($inputData[$k]!=$v['useless'])) {

                    if (!is_array($inputData[$k]) && isset($inputDataValues[$k][$inputData[$k]])) $ret[$v['name']] = $inputDataValues[$k][$inputData[$k]];
                    else $ret[$v['name']] = $inputData[$k];

                }

                if (!isset($v['useless'])) {

                    if (!is_array($inputData[$k]) && isset($inputDataValues[$k][$inputData[$k]])) {


                        $ret[$v['name']] = $inputDataValues[$k][$inputData[$k]];

                    }
                    else {
                        if ($v['presentation'] == 'date') {
                            if ($inputData[$k]) $ret[$v['name']] = $inputData[$k];
                        }
                        else
                        $ret[$v['name']] = $inputData[$k];
                    }

                }

            }

        }

    }

    return $ret;

}

function getCommonDataFields($fields) {

    $ret = false;

    if (is_array($fields) && count($fields)) {

        foreach($fields as $k=>$v) {

            if (($v['type'] == 'integer') ||
            ($v['type'] == 'double')) {

                $ret[$v['field']] = $k;

            }
        }

    }

    return $ret;

}

function getReportFilterFunctions($repName) {
    $ret = array();
    if (trim($repName)) {
        $className = 'C'.$repName;
        if (class_exists($className)) {
            $class_methods = get_class_methods($className);
            if (is_array($class_methods) && count($class_methods)) {
                foreach($class_methods as $method) {
                    if (preg_match("/^getsajaxfunctions$/i",$method)) {
                        $ret = call_user_func(array($className, 'getSajaxFunctions'));
                    }
                }
            }
        }
    }
    return $ret;
}


?>