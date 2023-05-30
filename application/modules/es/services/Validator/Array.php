<?php

/**
 * Description of Array
 *
 * @author slava
 */
class Es_Service_Validator_Array implements Es_Service_Validator_ValidatorBehavior {
    
    public function getValidatorCallback($array = null, $exceptionMessage = null) {
        return function($ev) use ($array, $exceptionMessage) {
            $params = ($ev->getParameters() === null)?array():$ev->getParameters();
            if (array_key_exists('arrayValue', $params)) {
                $array = $params['arrayValue'];
            }
            if (array_key_exists('exceptionMessage', $params)) {
                $exceptionMessage = $params['exceptionMessage'];
            }
            if (is_null($exceptionMessage)) {
                throw new Es_Exception_Runtime('Exception message does not define');
            }
            if (!is_array($array)) {
                throw new Es_Exception_InvalidArgument($exceptionMessage);
            }
        };
    }
    
}
?>
