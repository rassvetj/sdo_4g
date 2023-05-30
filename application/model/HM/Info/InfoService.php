<?php
class HM_Info_InfoService extends HM_Service_Abstract
{

    /**
     * Получаем заготовку грида, настроиваем под инфо-новости, возвращаем
     * @param Bvb_Grid $grid
     * @return Bvb_Grid
     */
    public function configureGrid( $grid )
    {
        if ( ! $grid instanceof Bvb_Grid) return false;
        
        // получаем вьюху для использования хелпера отображения иконок
        $view = $grid->getView();
		
        // настраиваем селект
        $select = $this->getSelect()->from(array('n' =>'news2'),array('nID','Title','Title_translation','show','used' => 'nID'));
        if (null != $select) {
            $grid->setSource(new Bvb_Grid_Source_Zend_Select($select));
        }
        
        // настраиваем поля
        $arFields = array('nID' => array('hidden' => true),
        				  'type' => array('hidden' => true),
                		  'Title' => array('title' => _('Название'),
											'callback' => array('function' => array($this,'updateName'),
                                          					  'params'   => array('{{Title}}', '{{Title_translation}}'))),
						 'Title_translation' => array('hidden' => true),
        				  'show' => array('title' => _('Статус'),
         								   'style' => 'width: 150px; ',
                        				  'callback' => array('function' => array($this,'visibleDecorator'),
                                          					  'params'   => array('{{show}}'))),
                          'used' => array('title' => _('Используется на страницах'),
                        				  'callback' => array('function' => array($this,'usedDecorator'),
                                          					  'params'   => array('{{used}}'))));
        foreach($arFields as $column => $options) {
            $grid->updateColumn($column, $options);
        }
        
        // настраиваем фильтры
        $arFilters = array('Title' => null);
        $gridFilters = new Bvb_Grid_Filters();
        foreach($arFilters as $field => $options) {
            $gridFilters->addFilter($field, $options);
        }
        $grid->addFilters($gridFilters);
        
        // добавляем действия
        $grid->addAction(array('module' => 'info',
            					'controller' => 'list',
            					'action' => 'visrevers'),
                        array('nID'),
                        '[replace_mark]');
                                
        $grid->addAction(array('module' => 'info',
            				   'controller' => 'list',
  							   'action' => 'edit'),
                        array('nID'),
                        $view->icon('edit',_('Редактировать')));

        $grid->addAction(array('module' => 'info',
            					'controller' => 'list',
            					'action' => 'delete'),
                        array('nID'=>'id'),
                        $view->icon('delete',_('Удалить')));

        $grid->addMassAction(array('module' => 'info',
                                    'controller' => 'list',
                                    'action' => 'visrevers'),
                            _('Инвертировать статус'),
                            _('Вы уверены?'));
                        
        $grid->addMassAction(array('module' => 'info',
                                    'controller' => 'list',
                                    'action' => 'delete-by'),
                            _('Удалить'),
                            _('Вы уверены?'));
                    
                            
        //переопределяем дефолтную эскейп-функцию, дабы не портило хтмл                     
        $grid->setDefaultEscapeFunction(array($this,'cellTextEscape'));
        
        $grid->setActionsCallback(array('function' => array($this,'updateActions'),
                      					'params'   => array('{{show}}')));  
            
        return $grid;
    }
    
    /**
     * Подготавливает текст для отображения в ячейке
     * @param string $text
     * @return string
     */
    public function cellTextEscape($text)
    {
        // подготавливаем строку
        $text = trim(strip_tags($text));
        preg_replace('/ +/',' ' , $text);
        $arText = explode(' ', $text);
        //ретурним обрубок или полностью в зависимости от количества слов
        return ( count($arText) >= HM_Info_InfoModel::GRID_MESSAGE_MAX_WORDS )? 
                            implode(' ', (array_slice($arText, 0, HM_Info_InfoModel::GRID_MESSAGE_MAX_WORDS))).'...' : 
                            $text;
    }
    
    /**
     * Отображение строковых представлений статусов 
     * вместо числовых аналогов в столбце "Видимость"
     * @param int $show
     * @return string
     */
    public function visibleDecorator($show)
    {
        return $show? _(HM_Info_InfoModel::VISIBLE_ON) : _(HM_Info_InfoModel::VISIBLE_OFF);
    }
    public function updateName($name, $name_translation=''){
	$request = Zend_Controller_Front::getInstance()->getRequest();
	$lang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
	if ($lang=='eng' && $name_translation!='')
		return $name_translation;
	else
		return $name;
	}
	 /**
     * Функция выставляет заголовок действия "Скрыть-показать" 
     * в зависимости от текущего статуса видимости 
     * @param string|int $show
     * @param unknown_type $actions
     * @return string
     */
    public function updateActions($show, $actions) 
    {
        
            $replace_text = ( $show == strval(intval($show)) )? array(_('Опубликовать'), _('Скрыть')) : 
                                                                array(HM_Info_InfoModel::VISIBLE_OFF => _('Опубликовать'),
                                                                      HM_Info_InfoModel::VISIBLE_ON => _('Скрыть'));
            return str_replace('[replace_mark]', $replace_text[$show], $actions);
    }
    
    public function usedDecorator($nID)
    {
        $arUsers = array();
        $arRoles = HM_Role_RoleModelAbstract::getBasicRoles(TRUE);
        $iBlicks = $this->getService('Infoblock')->fetchAll(array('block=?'=>'news','param_id=?'=>$nID));
        
        if ( count($iBlicks) ) {
            foreach( $iBlicks as $block ) {
                if ( $block->user_id ){
                    $user = $this->getService('User')->getOne($this->getService('User')->find($block->user_id));
                    if ($user) {
                        $arUsers[] = _('Пользователь') . ' ' . $user->getName();
                    }
                    
                } else {
                    $arUsers[] = _('Роль') . ' ' . $arRoles[$block->role];
                } 
            }
        }
        
        $result = ( count($arUsers) > 1 ) ? array('<p class="total">' . _('Всего') . ' ' . (count($arUsers)) . '</p>') : array();
        foreach($arUsers as $value){
            $result[] = "<p>{$value}</p>";
        }
        if($result)
            return implode($result);
        else
            return _('Нет');

    }
    
    public function replacePlaceholders($content) {
        $userService = $this->getService('User');
        $user = $userService->getCurrentUser();
        
        $replacements = array(
            'USER_ID'    => $user->mid_external,
            'USER_FIO'   => $user->LastName.' '.$user->FirstName.' '.$user->Patronymic,
            'USER_EMAIL' => $user->EMail
        );

        foreach($replacements as $key => $value)
        {
            $content = str_replace('['.$key.']', $value, $content);
        }


        return $content;
    }
    
    
}