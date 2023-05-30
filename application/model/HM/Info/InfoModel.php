<?php
class HM_Info_InfoModel extends HM_Model_Abstract
{
    //статусы видимоси инфо-новостей
    const VISIBLE_ON  = 'Опубликован';
    const VISIBLE_OFF = 'Не опубликован';
    // максимальное количество слов текста, отображаемых в гриде
    const GRID_MESSAGE_MAX_WORDS = 30;

	/**
     * Меняет видимость инфо-новостей на противоположную
     */
    public function invertVisible( $arID = null)
    {
       $this->show = abs($this->show - 1);
    }

    /* (non-PHPdoc)
     * @see HM_Model_Abstract::getServiceName()
     */
    public function getServiceName()
    {
        return "Info";
    }
}