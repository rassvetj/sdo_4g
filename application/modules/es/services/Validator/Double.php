<?php

/**
 * Description of Double validator
 *
 * @author slava
 */
class Es_Service_Validator_Double implements Es_Service_Validator_ValidatorBehavior {
    
    public function getValidatorCallback($doubleValue = null, $exceptionMessage = null) {
        return function($ev) use ($doubleValue, $exceptionMessage) {
            $params = ($ev->getParameters() === null)?array():$ev->getParameters();
            if (array_key_exists('doubleValue', $params)) {
                $doubleValue = $params['doubleValue'];
            }
            if (array_key_exists('exceptionMessage', $params)) {
                $exceptionMessage = $params['exceptionMessage'];
            }
            if (is_null($exceptionMessage)) {
                throw new Es_Exception_Runtime('Exception message does not define');
            }
            if (!is_double($doubleValue)) {
                throw new Es_Exception_InvalidArgument($exceptionMessage);
            }
        };
    }
    
}

?>
