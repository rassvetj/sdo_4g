<?php
// DEPRECATED!
// use score()
class HM_View_Helper_ScoreBinary extends HM_View_Helper_Abstract
{
    // @todo: сделать элемент активным (пока только показывает оценку)
    public function scoreBinary($score, $userId = null, $lessonId= null)
    {
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/content-modules/score.css'));
        //$this->view->headScript()->appendFile($this->view->serverUrl('/js/application/marksheet/index/index/scoreList.js'));
        $this->view->score = $score;
        $this->view->userId = $userId;
        $this->view->lessonId = $lessonId;
        return $this->view->render('scoreBinary.tpl');
    }
}
