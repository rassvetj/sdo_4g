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
		
		if($model['BirthDate'] < 1900){  //диапазон периода дат в mssql идет с 1900 года. все, что меньше - выбросит исключение с ошибкой БД
			$model['BirthDate'] = '';
		}
		
		$ts = strtotime($model['begin_learning']);
		if($ts <= 0){
			$model['begin_learning'] = '';
		} else {
			$model['begin_learning'] = date('Y-m-d', $ts);
		}
		
		
        $model['need_edit'] = 0;
		
        $this->filterInt($model['group_id_external']);
		
		$model['organization'] = trim($model['organization']);
		$model['Skype'] 	   = trim($model['Skype']);
        
        parent::modifyModel($model);
    }
    
}