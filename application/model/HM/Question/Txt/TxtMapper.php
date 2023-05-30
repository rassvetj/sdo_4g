<?php
class HM_Question_Txt_TxtMapper extends HM_Mapper_Abstract
{
    protected function _createModel($rows, &$dependences = array())
    {
        $collectionClass = $this->getCollectionClass();
        $models = new $collectionClass(array(), $this->getModelClass());
        if (count($rows) < 1) {
            return $models;
        }

        if (count($rows) > 0) {
            foreach($rows as $row) {
                $row['qdata'] = $row['title'];
                $row['adata'] = '';

                $counter = 1;
                foreach($row['answers'] as $answer) {
                    $row['qdata'].= HM_Question_QuestionModel::SEPARATOR.$counter.HM_Question_QuestionModel::SEPARATOR.$answer['text'];
                    
                    if($row['qtype'] == HM_Question_QuestionModel::TYPE_ONE){
                        if($answer['true'] == 1){
                            $row['adata'] = (string) $counter;
                        }
                    }else{
                        if ($counter > 1) {
                            $row['adata'] .= HM_Question_QuestionModel::SEPARATOR;
                        }
                        $row['adata'] .= (int) $answer['true'];
                    }
                    $counter++;
                }
                $models[count($models)] = $row;
            }

            //$models->setDependences($dependences);
        }

        return $models;

    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $rows = $this->getAdapter()->fetchAll($where, $order, $count, $offset);
        return $this->_createModel($rows);
    }

}