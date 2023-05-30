<?php
require_once 'Zend/Filter/Interface.php';
require_once 'HTMLPurifier/HTMLPurifier.auto.php';

class HM_Filter_HtmlSanitize implements Zend_Filter_Interface
{
    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value
     *
     * @param  string $value
     * @return string
     */
    public function filter($value, $bAllowRichHTML=false)
    {
        $config = HTMLPurifier_Config::createDefault();

        if(isset($bAllowRichHTML) && $bAllowRichHTML) { //для прохода данных от HTML-редактора с видео
            $config->set('HTML.SafeObject', 1);
            $config->set('HTML.SafeEmbed', 1);
        }

        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        //$config->set('AutoFormat.RemoveSpansWithoutAttributes', true);
        //$config->set('CSS.ForbiddenProperties', array('behavior' => true, 'filter' => true, '-ms-filter' => true));
        $config->set('Core.RemoveProcessingInstructions', true);
        //$config->set('Filter.Custom', array());
        //$config->set('Filter.ExtractStyleBlocks', true);
        $config->set('Cache.SerializerPath', APPLICATION_PATH . "/../data/htmlpurifier");
        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'target', new HTMLPurifier_AttrDef_Enum(
                array('_blank','_self','_target','_top')
            ));
        // Core.HiddenElements array ( 'script' => true, 'style' => true, )
        $purifier = new HTMLPurifier($config);
//die();
        return $purifier->purify($value);;
    }
}
