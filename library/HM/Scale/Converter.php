<?php
class HM_Scale_Converter
{
    private static $_converter = null;
    private static $_scales = array();

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$_converter === null) {
            self::init();
        }

        return self::$_converter;
    }

    public static function setInstance(HM_Scale_Converter $converter)
    {
        if (self::$_converter !== null) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('Scale value convertor is already initialized');
        }

        self::$_converter = $converter;
        self::setScales();
    }

    protected static function init()
    {
        self::setInstance(new HM_Scale_Converter());
    }

    protected static function setScales()
    {
        $scales = Zend_Registry::get('serviceContainer')->getService('Scale')->fetchAllDependenceJoinInner('ScaleValue', 'ScaleValue.value');
        foreach ($scales as $scale) {
            self::$_scales[$scale->scale_id] = $scale->scaleValues->getList('value', 'value_id');
        }
    }

    public function value2id($value, $scaleId)
    {
        if (isset(self::$_scales[$scaleId])) {
            if (isset(self::$_scales[$scaleId][$value])) {
                return self::$_scales[$scaleId][$value];
            }
        }
        return 0;
        //throw new HM_Exception(_('Scale value not found'));
    }

    public function id2value($id, $scaleId)
    {
        if (isset(self::$_scales[$scaleId])) {
            if (false !== ($value = array_search($id, self::$_scales[$scaleId]))) {
                return $value;
            }
        }
        return 0;
        //throw new HM_Exception(_('Scale value not found'));
    }

}