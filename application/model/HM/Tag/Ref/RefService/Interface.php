<?php
interface HM_Tag_Ref_RefService_Interface
{
    /**
     * Возвращает массив с информацией о модуле, контроллере, экшене и элементе 
     * для построения ссылки на его в результатах поиска по тегам
     * @param int $itemId
     * @return array
     */
    public function getItemViewAction($itemId);
    
    /**
     * Возвращает заголовок элемента по его ИД
     * @param int $itemId
     * @return string
     */
    public function getItemTitle($itemId);
    
    /**
     * Возвращает описание элемента по его ИД
     * @param int $itemId
     * @return string
     */
    public function getItemDescription($itemId);
    
    /**
     * Добавялет к селекту грида фильтрацию по тегу и возвращает обновленный селект
     * @param string $search
     * @param Zend_Db_Select $select
     * @return Zend_Db_Select
     */
    public function getFilterSelect($search, Zend_Db_Select $select);
    
    /**
     * Возвращает массив с информацией о иконке
     * @return array 
     */
    public function getIcon();
}