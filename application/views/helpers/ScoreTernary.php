<?php
// DEPRECATED!
// use score()
class HM_View_Helper_ScoreTernary extends HM_View_Helper_Abstract
{
    public function scoreTernary($score, $userId = null, $lessonId= null)
    {
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/content-modules/score.css'));
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/application/marksheet/index/index/scoreList.js'));
        $this->view->score = $score;
        $this->view->userId = $userId;
        $this->view->lessonId = $lessonId;
        return $this->view->render('scoreTernary.tpl');
    }
}
