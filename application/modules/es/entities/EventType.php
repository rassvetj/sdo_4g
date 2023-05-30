<?php

class Es_Entity_EventType extends Es_Entity_AbstractEventType
{
    
    public function getTypeDescriptions() { 
        return array(
            'forumAddMessage' => _('Добавление сообщения в форум на уровне портала'),
            'blogAddMessage' => _('Добавление сообщения в блог на уровне портала'),
            'wikiAddPage' => _('Добавление страницы Wiki на уровне портала'),
            'wikiModifyPage' => _('Изменение страницы Wiki на уровне портала'),
            'forumInternalAddMessage' => _('Добавление сообщения в форум на уровне  курса'),
            'blogInternalAddMessage' => _('Добавление сообщения в блог на уровне курса'),
            'wikiInternalAddPage' => _('Добавление страницы Wiki на уровне курса'),
            'wikiInternalModifyPage' => _('Изменение страницы Wiki на уровне курса'),
            'courseAddMaterial' => _('Добавление материала в курс'),
            'courseAttachLesson' => _('Назначение занятия студенту'),
            'courseScoreTriggered' => _('Выставление оценки за курс'),
            'courseTaskAction' => _('Выполнение задания студентом'),
            'commentAdd' => _('Добавление комментария к чему-либо на уровне портала'),
            'commentInternalAdd' => _('Добавление комментария к чему-либо на уровне курса'),
            'courseTaskScoreTriggered' => _('Выставление оценки за занятие'),
            'personalMessageSend' => _('Получение персональных сообщений'),
			'motivationMessage' => _('Отправка мотивированного заключения'),
			'courseAddMessage' 	=> _('Добавление сообщения в переписке курса'),
        );
    }
    
    public function getLocatedName() {
        $types = $this->getTypeDescriptions();
        if (!array_key_exists($this->getName(), $types)) {
            throw new Es_Exception_InvalidArgument('Requested event type name \''.$this->getName().'\' has no translation');
        }
        return $types[$this->getName()];
    }
	
	public static function getTypeDescriptionsShort(){
		$aclService  = Zend_Registry::get('serviceContainer')->getService('Acl');
		$userService = Zend_Registry::get('serviceContainer')->getService('User');
        $userRole 	 = $userService->getCurrentUserRole();
		
		$list = array(
			'forumAddMessage'			=> _('Сообщение на форуме на уровне портала'),
			'blogAddMessage' 			=> _('Сообщение в блоге на уровне портала'),
			'wikiAddPage' 				=> _('Добавлена страница Wiki на уровне портала'),
			'wikiModifyPage' 			=> _('Изменена страница Wiki на уровне портала'),
			'forumInternalAddMessage' 	=> _('Сообщение на форуме на уровне  курса'),
			'blogInternalAddMessage' 	=> _('Сообщение в блоге на уровне курса'),
			'wikiInternalAddPage' 		=> _('Добавлена страница Wiki на уровне курса'),
			'wikiInternalModifyPage' 	=> _('Изменена страница Wiki на уровне курса'),
			'personalMessageSend' 		=> _('Персональное сообщение'),
			'courseAddMessage' 			=> _('Собщение в переписке курса'),
		);
		
		if ($aclService->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_TEACHER) || $aclService->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_TUTOR)) {
			$list['courseTaskAction']  = _('Выполнено задание студентом');
			$list['motivationMessage'] = _('Мотивированное заключение');
		} elseif ($aclService->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_STUDENT)) {
			$list['courseAddMaterial']  		= _('Добавлен материал в курсе');
			$list['courseAttachLesson']			= _('Назначено занятие');
			$list['courseScoreTriggered']		= _('Выставлена оценка за курс');
			$list['commentAdd']  				= _('Добавлен комментарий на уровне портала');
			$list['commentInternalAdd']  		= _('Добавлен комментарий на уровне курса');
			$list['courseTaskScoreTriggered']	= _('Выставлена оценка за занятие');
			$list['motivationMessage']  		= _('Мотивированное заключение');			
		} 		
		return $list;
	}
    
}
