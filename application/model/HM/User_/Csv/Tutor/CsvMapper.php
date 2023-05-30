<?php
class HM_User_Csv_Tutor_CsvMapper extends HM_User_Csv_CsvMapper
{
    protected function modifyModel(&$model){
        $model['role_1c'] = HM_User_UserModel::ROLE_1C_TUTOR;
        
//        $this->filterInt($model['mid_external']);
        
        parent::modifyModel($model);
    }

}