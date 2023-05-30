<?php
class HM_View_Helper_Workflow extends HM_View_Helper_Abstract
{

	public function workflow($model)
	{
        $this->view->model = $model;
        $state = $model->getProcess()->getCurrentState();

        $this->view->statesTypes = Zend_Registry::get('serviceContainer')->getService('State')->getStatesTypes($model->getProcess()->getType());
		return $this->view->render('workflow.tpl');
	}

    /**
     * Создает список действий в хелпере из массива.
     * нужен для рекурсивной обработки массива и возможности создания нескольких списков действий.
     * Интерфейс для getItemRecursive
     * @author Artem Smirnov <tonakai.personal@gmail.com>
     * @date 24.01.2013
     * @param array  $listOfStates
     * @param string $listDecorator
     * @param string $itemDecorator
     *
     * @return string
     */
    public function renderStatesList($listOfStates,$listDecorator = "<ol>{{item}}</ol>",$itemDecorator = "<li>{{item}}</li>")
    {
        return $this->getItemRecursive($listOfStates,$listDecorator,$itemDecorator);
    }

    /**
     * Создает список действий в хелпере из массива.
     * нужен для рекурсивной обработки массива и возможности создания нескольких списков действий.
     * @author Artem Smirnov <tonakai.personal@gmail.com>
     * @date 24.01.2013
     * @param $list
     * @param $listDecorator
     * @param $itemDecorator
     *
     * @return mixed
     */
    private function getItemRecursive($list,$listDecorator,$itemDecorator)
    {
        $result = "";
        if(is_array($list))
        {
            foreach($list as $item){
                if(is_array($item))
                {
                    $result .= $this->getItemRecursive($item,$listDecorator,$itemDecorator);
                }
                else
                {
                    $itemRendered = $item->render();
                    if(!empty($itemRendered))
                    {
                        if($item->isDecorated())
                        {
                            $result .= str_replace("{{item}}",$itemRendered,$itemDecorator);
                        }
                        else
                        {
                            $result .= $itemRendered;
                        }
                    }
                }
            }
            return str_replace("{{item}}",$result,$listDecorator);
        }
        return "";
    }
}