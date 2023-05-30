<?php

/**
 * Description of NotNull
 *
 * @author slava
 */
class Es_Service_Validator_NotNull implements Es_Service_Validator_ValidatorBehavior {
    
    public function getValidatorCallback($notNullParam = null, $exceptionMessage = null) {
        return function ($ev) use ($notNullParam, $exceptionMessage) {
            $params = ($ev->getParameters() === null)?array():$ev->getParameters();
            if (array_key_exists('notNullParam', $params)) {
                $notNullParam = $params['notNullParam'];
            }
            if (array_key_exists('exceptionMessage', $params)) {
                $exceptionMessage = $params['exceptionMessage'];
            }
            if (is_null($exceptionMessage)) {
                throw new Es_Exception_Runtime('Exception message does not define');
            }
            if (is_null($notNullParam) || null === $notNullParam) {
                throw new Es_Exception_InvalidArgument($exceptionMessage);
            }
        };
    }
    
}

?>
