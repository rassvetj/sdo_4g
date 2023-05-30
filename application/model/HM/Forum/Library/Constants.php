<?php

interface HM_Forum_Library_Constants{
    
    // Параметры передаваемые в запросе
    const PARAM_FORUM_ID   = 'forum_id';   // ID форума
    const PARAM_SECTION_ID = 'section_id'; // ID раздела/темы
    const PARAM_MESSAGE_ID = 'message'; // ID сообщения
    
    const ROUTE_FORUM   = 'forum';
    const ROUTE_SUBJECT = 'forum_subject';
    const ROUTE_DEFAULT = 'default';
    
    // Сообщения об ошибках
    const ERR_APP_ERROR  = 'Ошибка приложения';
    
    const ERR_MSG_FORUM_NM   = 'Ошибка в запросе: не передан номер форума';
    const ERR_MSG_NOSUBJECT  = 'Ошибка в запросе: запрошенный вами учебный курс не найден';
    const ERR_MSG_NOLESSON   = 'Ошибка в запросе: запрошенное вами занятие не найдено';
    
    const ERR_MSG_NOFORUM    = 'Запрошенный форум не существует';
    const ERR_MSG_NOSECTION  = 'Запрошенный раздел не существует';
    const ERR_MSG_FORUMNOSECTIONS = 'Данный форум не может иметь подразделов';
    const ERR_MSG_NOUSER     = 'Нет такого пользователя';
    const ERR_MSG_DEFAULTFORUM = 'Ошибка при создании форума по умолчанию';
    
    const ERR_CODE_NOFORUM = 1;
    const ERR_CODE_NOSECTION = 2;
    const ERR_CODE_FORUMNOSECTIONS = 3;
    const ERR_CODE_NOUSER = 4;
    const ERR_CODE_DEFAULTFORUM = 5;
    
}