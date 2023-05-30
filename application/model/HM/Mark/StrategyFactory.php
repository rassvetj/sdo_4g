<?php
/**
 * Created by PhpStorm.
 * User: CuTHuK
 * Date: 18.03.14
 * Time: 13:33
 */

class HM_Mark_StrategyFactory extends HM_Service_Primitive {

    const MARK_WEIGHT = 0;
    const MARK_BRS = 1;




    static public function getType($type)
    {
        $types = self::getTypes();
        return isset($types[$type]) ? $types[$type] : '';
    }

    static public function getTypes()
    {
        return array(
            self::MARK_BRS => 'Brs',
            self::MARK_WEIGHT => 'Weight',
        );
    }

    public function getValues()
    {
        $values = array();
        $strategyServiceNamePrefix = "Mark";
        foreach (self::getTypes() as $key => $type){
            $strategyServiceName = $strategyServiceNamePrefix.ucfirst($type)."Strategy";
            $values[$key] = $this->getService($strategyServiceName)->getValue();
        }
        return $values;
    }

    public function getStrategy($type)
    {
        $strategyServiceNamePrefix = "Mark";
        $strategyServiceName = $strategyServiceNamePrefix.ucfirst($type)."Strategy";
        try {
            $strategy = $this->getService($strategyServiceName);
            return $strategy;
        } catch (InvalidArgumentException $e) {
            throw new HM_Mark_Exception_InvalidSearchStrategyException('Required mark strategy is not defined!', $e->getCode(), $e);
        }
    }

} 