<?php
class HM_User_Csv_Student_CsvMapper extends HM_User_Csv_CsvMapper
{
    protected function modifyModel(&$model){
        $model['role_1c'] = HM_User_UserModel::ROLE_1C_STUDENT;
        if(trim($model['Gender']) == 'Мужской'){
            $model['Gender'] = 1;
        } else if(trim($model['Gender']) == 'Женский'){
            $model['Gender'] = 2;
        }
        
        $this->filterInt($model['mid_external']);
        $this->filterInt($model['BirthDate']);
        $this->filterInt($model['group_id_external']);
        
        parent::modifyModel($model);
    }
    
}