<?php
class HM_Option_OptionService extends HM_Service_Abstract
{
    private $_options = array();

    public function getOption($name)
    {
        if (!isset($this->_options[$name])) {
            $this->_options[$name] = $this->getOne($this->fetchAll($this->quoteInto('name LIKE ?', $name)));
        }
        return $this->_options[$name]->value;
    }
    
    public function setOption($name, $value){
        
        $this->_options[$name] = $value;
        $option = $this->getOne($this->fetchAll($this->quoteInto('name LIKE ?', $name)));

        if ($option) {

            $option->name = $name;
            $option->value = $value;
            
            $this->update($option->getValues());
        } else {
            $this->insert(array(
                'name' => $name,
            	'value' => $value)
            );
        }
    }
    
    public function getOptions($scope)
    {
        // default values
        $options = self::getDefaultOptions($scope);
        
        $res = $this->fetchAll(array('name IN (?)' => array_keys($options)));
        foreach($res as $option){
            $options[$option->name] = $option->value;
        }
        return $options;
    }
    
    public function getDefaultOptions($scope)
    {
        switch ($scope) {
            case HM_Option_OptionModel::SCOPE_PASSWORDS:
                return array(
                	'passwordMinLength'       => 7,
                	'passwordMinNoneRepeated' => 0,
                    'passwordCheckDifficult'  => 0,
                	'passwordMaxPeriod'       => 0,
                	'passwordMinPeriod'       => 0, 
                	'passwordRestriction'     => 0,
                    'passwordMaxFailedTry'    => 3,
                	'passwordFailedActions'   => 0
                );
            break;
            case HM_Option_OptionModel::SCOPE_CONTRACT:
                return array(
//                	'regAllow' => 1,
                        'regDeny' => 0,
                	'regRequireAgreement' => 1,
                	'regUseCaptcha' => 0,
                	'regValidateEmail' => 0,
                	'regAutoBlock' => 0,
                	'contractOfferText' => '',
                	'contractPersonalDataText' => '',
                );
            break;
            default:
                return array();
            break;
        }        
    }
    
    public function setOptions($options)
    {
        foreach($options as $key => $value) {

            $count = $this->countAll($this->getSelect()->getAdapter()->quoteInto('name = ?', $key));
            if($count > 0){
                $this->updateWhere(array('value' => $value), array('name = ?' => $key));
            }else{
                $this->insert(
                    array(
                    	'name' => $key,
                        'value' => $value
                    )
                );
            }
        }
        return true;
    }
    
    public function getDefaultCurrency()
    {
        // default values
        $result = 'RUB';
        
        $res = $this->fetchAll(
            array('name = ?' => 'default_currency' )
        );
        
        foreach($res as $option){
            $result = $option->value;
        }
        return $result;
    }
}