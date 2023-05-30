<?php
class HM_Subject_Csv_CsvMapper extends HM_Mapper_Abstract
{
    protected function _createModel($rows, &$dependences = array())
    {
        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());

        if (count($rows) > 0) {
            $dependences = array();
            foreach($rows as $index => $row) {
                $model = array();
                foreach($row as $key => $val){
//                    if($val != "" ){
                        $model[$key] = $val;
//                    }
                }
                
                $this->filterInt($model['external_id']);
                $this->filterInt($model['learning_subject_id_external']);
                $this->filterInt($model['programm_id_external']);
                $this->filterInt($model['isDO']);
                $this->filterInt($model['is_practice']);
				if($model['isDO'] != HM_Subject_SubjectModel::FACULTY_DO){
					$model['isDO'] = HM_Subject_SubjectModel::FACULTY_OTHER;
				}
                
                $model['exam_type'] = trim($model['exam_type']);
                if(($model['exam_type'] == 'Приём экзаменов') || ($model['exam_type'] == 'Экзамен')){   
					$model['exam_type'] = HM_Subject_SubjectModel::EXAM_TYPE_EXAM;
                } else if(($model['exam_type'] == 'Приём зачётов') || ($model['exam_type'] == 'Зачет')){ 
					$model['exam_type'] = HM_Subject_SubjectModel::EXAM_TYPE_TEST;
                } else if(
						($model['exam_type'] == 'Приём дифференцированных зачетов')
					||  ($model['exam_type'] == 'Прием дифиренцированных зачетов по учебной практике')
					||  ($model['exam_type'] == 'Дифференцировнный зачет')
					||  ( strripos($model['exam_type'], 'Прием дифиренцированных зачетов') !== false )

					||  ($model['exam_type'] == 'Прием дифиренцированных зачетов по производственной практике')
					||  ($model['exam_type'] == 'Прием дифференцированных зачетов по преддипломной практике')
					||  ($model['exam_type'] == 'Прием дифференцированных зачетов по производственной практике')
					||  ($model['exam_type'] == 'Прием дифференцированных зачетов по учебной практике')
				){  
					$model['exam_type'] = HM_Subject_SubjectModel::EXAM_TYPE_TEST_MARK;
				} else if($model['exam_type'] == 'Контроль самостоятельной работы'){
					$model['exam_type'] = HM_Subject_SubjectModel::EXAM_TYPE_INDEPENDENT_WORK;
				
				} else if($model['exam_type'] == 'ГИА'){
					$model['exam_type'] = HM_Subject_SubjectModel::EXAM_TYPE_GIA;
				
				} else {                    
					$model['exam_type'] = HM_Subject_SubjectModel::EXAM_TYPE_NONE;
                }
                
				
				$this->filterInt($model['learn']);
				$model['zet'] = $model['learn'];
				
				$date = DateTime::createFromFormat('d.m.Y', $model['begin_learning']);
				if ($date){
					$model['begin_learning'] = $date->format('Y-m-d');
				} else {
					$model['begin_learning'] = NULL;
				}
				
				$model['group_external_id'] = trim($model['group_external_id']);
				$this->filterInt($model['language_code']);
				$this->filterInt($model['module_code']);
				
                $format = 'Y-m-d';
                $model['begin'] = date($format, strtotime($model['begin']));
                $model['end'] = date($format, strtotime($model['end']));
                $model['period'] = HM_Subject_SubjectModel::PERIOD_DATES;
                $model['period_restriction_type'] = HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT;                
                
				$model['module_name']		= trim($model['module_name']);
				$model['practice_begin']	= strtotime($model['practice_begin']) > 0 ? date($format, strtotime($model['practice_begin'])) : false;
				$model['practice_end']		= strtotime($model['practice_end'])   > 0 ? date($format, strtotime($model['practice_end']))   : false;
				
                
                $models[count($models)] = $model;
                unset($rows[$index]);
            }
            
            $models->setDependences($dependences);
        }

        return $models;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getAdapter()->fetchAll($where, $order, $count, $offset);
        return $this->_createModel($rows);
    }

}