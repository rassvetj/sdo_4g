<?php

/**
 * Description of Integer
 *
 * @author slava
 */
class Es_Service_Validator_Integer implements Es_Service_Validator_ValidatorBehavior {
    
    public function getValidatorCallback($intValue = null, $exceptionMessage = null) {
        return function($ev) use ($intValue, $exceptionMessage) {
            $params = ($ev->getParameters() === null)?array():$ev->getParameters();
            if (array_key_exists('intValue', $params)) {
                $intValue = $params['intValue'];
            }
            if (array_key_exists('exceptionMessage', $params)) {
                $exceptionMessage = $params['exceptionMessage'];
            }
            if (is_null($exceptionMessage)) {
                throw new Es_Exception_Runtime('Exception message does not define');
            }
            if (!is_int($intValue)) {
                throw new Es_Exception_InvalidArgument($exceptionMessage);
            }
        };
    }
    
}

?>
