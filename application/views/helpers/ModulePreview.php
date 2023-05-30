<?php
class HM_View_Helper_ModulePreview extends HM_View_Helper_Abstract
{
    public function modulePreview($item)
    {
//         $currentResult = Null;
//         foreach($results as $result){
//             if($result->ModID == $item->oid){
//                 $currentResult = $result;
//             }
//         }
        $currentResult = $item->result;
        if($currentResult == Null){
            $currentResult = new stdClass();
            $currentResult->status = 'not_attempted';
            $currentResult->scoremax = 100;
        }
        $currentResult->percentProgress = ($currentResult->scoremax - $currentResult->scoremin) ? ceil ($currentResult->score / ($currentResult->scoremax - $currentResult->scoremin) * 100) : 0;

        if($currentResult->status == HM_Scorm_Track_Data_DataModel::STATUS_COMPLETED && $currentResult->scoremax == 0 && $currentResult->score == 0){
            $currentResult->percentProgress = 100;
        }



        $this->view->classes = $this->getStatus2Class();

        $this->view->item = $item;
        $this->view->result = $currentResult;
        return $this->view->render('modulePreview.tpl');

    }

    public function getStatus2Class(){
        return array(
            HM_Scorm_Track_Data_DataModel::STATUS_NOT_ATTEMPTED => '',
            HM_Scorm_Track_Data_DataModel::STATUS_INCOMPLETE => 'cur',
            HM_Scorm_Track_Data_DataModel::STATUS_FAILED => 'no',
            HM_Scorm_Track_Data_DataModel::STATUS_COMPLETED => 'ok',
            HM_Scorm_Track_Data_DataModel::STATUS_PASSED => 'ok',
        );

    }



}