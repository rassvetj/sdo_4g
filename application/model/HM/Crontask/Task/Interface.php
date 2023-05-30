<?php
interface HM_Crontask_Task_Interface
{
    /**
     * @abstract
     * Выполнение задания
     */
    public function run();

    /**
     * @abstract
     * Получение идентификатора задания.
     * @return string;
     * @todo реализовать редактирование заданий через GUI с сервисом и моделью
     */
    public function getTaskId();

}
