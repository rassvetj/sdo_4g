<?php
require_once 'HM/Exception.php';

class HM_Date_Exception extends HM_Exception
{
    protected $operand = null;

    public function __construct($message, $code = 0, $e = null, $op = null)
    {
        $this->operand = $op;
        parent::__construct($message, $code, $e);
    }

    public function getOperand()
    {
        return $this->operand;
    }
}
