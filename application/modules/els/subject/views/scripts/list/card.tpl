<img src="<?php echo $this->baseUrl($this->subject->getIcon());?>" alt="<?php echo $this->escape($subjectName)?>" align="left" style="margin-right: 20px;"/>
<?php
if ($this->subject->period == HM_Subject_SubjectModel::PERIOD_FREE) {
	$period = array('getPeriod()'  => _('Ограничение времени обучения'));
} else {
	if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),  HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
		#$period = array('getBeginForStudent()'  => _('Дата начала обучения'), 'getEndForStudent()' => _('Дата окончания обучения, не позднее'));
		$period = array('getBeginForStudent()'  => _('Дата начала обучения'));
	} else {
		if ($this->subject->period == HM_Subject_SubjectModel::PERIOD_FIXED) {
			$period = array('getLongtime()'  => _('Ограничение времени обучения'));
		} else { // PERIOD_DATES
			$period = array('getBegin()'  => _('Дата начала'), 'getEnd()'    => _('Дата окончания'));
		}
	}
}
?>
<?php
switch ($this->subject->state) {
	case HM_Subject_SubjectModel::STATE_PENDING:
		$tooltip = _('Обучение по курсу не начато. Никто из слушателей не имеет доступа к материалам курса.');
		break;
	case HM_Subject_SubjectModel::STATE_ACTUAL:
		$tooltip = _('Идёт обучение по курсу, материалы курса открыты для слушателей.');
		break;
	case HM_Subject_SubjectModel::STATE_CLOSED:
		$tooltip = _('Обучение по курсу закончено. Все слушатели переведены в прошедшие обученеи, никто из них не имеет доступа к материалам курса.');
		break;
}
?>
<?php
    echo $this->card(
    $this->subject,
    array_merge(
        $period,
        array(
            $this->provider        => _('Провайдер обучения'),
            $this->room        => _('Место проведения'),
            'getType()'  => _('Тип'),
            ),
        $this->subject->price? array('getPriceWithCurrency()' => _('Стоимость')) : array(),
        #Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:list:calendar') ? array('getColorField()' => _('Цвет в календаре')) : array(),
        $this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL ? array(
                'getStateSwitcher()'  => array(
                    'title' => _('Статус обучения'),
                    'tooltip' => $tooltip,
                ),
        ) : array(),
        $this->graduated ? array('getGraduatedMsg()' => _('Статус обучения')) : array(),
		array('subject_exam_type' => _('Форма контроля')),
		array('groups' => _('Группы'))
    ),
    array(
        'title' => _('Карточка учебного курса'),
        )
    );
?>
