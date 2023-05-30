<?php
class HM_Crontask_CrontaskService extends HM_Service_Abstract
{
    private $_taskList = array();

    /**
     * Добавляет задание в список выполнения.
     * @param HM_Crontask_Task_Interface $task
     * @return HM_Crontask_CrontaskService
     */
    public function addTask($task)
    {
        if($task instanceof HM_Crontask_Task_Interface) {
            $this->_taskList[] = $task;
        }
        return $this;
    }

    /**
     * Инит заданий
     */
    public function init()
    {
        // перевод в прошедшие обучение раз в 4 часа      
        $this->addTask( new HM_Crontask_Task_Graduate(4*60));
		$this->addTask( new HM_Crontask_Task_Feedback(24*60));

        return $this;
    }

    /**
     * Выполнение списка заданий
     */
    public function run()
    {
        $runnedTasks = $this->fetchAll()->getList('crontask_id','crontask_runtime');
        foreach ($this->_taskList as $task) {
            //если задание уже выполнялось и интервал еще не вышел, пропускаем
            if( array_key_exists($task->getTaskId(),$runnedTasks) &&
                ((time() - $runnedTasks[$task->getTaskId()]) < $task->getInterval(true)) ) continue;

            //запуск задания
            $task->run();

            //обновление записей в БД
            $data = array('crontask_id'      => $task->getTaskId(),
                          'crontask_runtime' => time());

            if (array_key_exists($task->getTaskId(),$runnedTasks)) {
                $this->update($data);
            } else {
                $this->insert($data);
            }
        }
    }
}