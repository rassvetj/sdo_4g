<?php
require_once APPLICATION_PATH .  '/views/helpers/Score.php';

class HM_View_Helper_MarkSheetTable extends HM_View_Helper_Abstract
{
    public function markSheetTable($persons, $schedules, $scores, $additional, $mode = 'page', $subjectId = null)
    {

        $this->view->persons   = $persons;
        $this->view->schedules = $schedules;
        $this->view->scores    = $scores;
        $this->view->additional    = $additional;
        $this->view->subject = Zend_Registry::get('serviceContainer')->getService('Subject')->getOne(
            Zend_Registry::get('serviceContainer')->getService('Subject')->find($subjectId)
        );

        switch ($mode){
        	case 'page': return $this->view->render('marksheettable.tpl'); break;
        	case 'print': return $this->view->render('marksheettable-print.tpl'); break;
        	case 'export': return $this->view->render('marksheettable-export.tpl'); break;
        	case 'export-mod': return $this->view->render('marksheettable-export-mod.tpl'); break;
        	case 'export-vedomost-pdf': return $this->view->render('marksheettable-export-vedomost-pdf.tpl'); break;
        	case 'export-vedomost-excel': return $this->view->render('marksheettable-export-vedomost-excel.tpl'); break;
        	case 'export-vedomost-print': return $this->view->render('marksheettable-export-vedomost-print.tpl'); break;
        }
    }
}