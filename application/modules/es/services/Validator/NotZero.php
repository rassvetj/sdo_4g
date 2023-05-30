<?php
/**
 * Description of NotZero
 *
 * @author slava
 */
class Es_Service_Validator_NotZero implements Es_Service_Validator_ValidatorBehavior {
    
    public function getValidatorCallback($notZeroParam = null, $exceptionMessage = null) {
        return function($ev) use ($notZeroParam, $exceptionMessage) {
            $params = ($ev->getParameters() === null)?array():$ev->getParameters();
            if (array_key_exists('notZeroParam', $params)) {
                $notZeroParam = $params['notZeroParam'];
            }
            if (array_key_exists('exceptionMessage', $params)) {
                $exceptionMessage = $params['exceptionMessage'];
            }
            if (is_null($exceptionMessage)) {
                throw new Es_Exception_Runtime('Exception message does not define');
            }
            if ($notZeroParam === 0) {
                throw new Es_Exception_InvalidArgument($exceptionMessage);
            }
        };
    }
    
}

?>
