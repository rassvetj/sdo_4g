<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category    ZendX
 * @package     ZendX_JQuery
 * @subpackage  View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license     http://framework.zend.com/license/new-bsd     New BSD License
 * @version     $Id: Slider.php 20165 2010-01-09 18:57:56Z bkarwin $
 */

/**
 * jQuery Time Slider View Helper
 *
 * @uses 	   Zend_Json
 * @package    ZendX_JQuery
 * @subpackage View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class HM_View_Helper_TimeSlider extends ZendX_JQuery_View_Helper_Slider
{
    /**
     * Create jQuery slider that updates its values into a hidden form input field.
     *
     * @link   http://docs.jquery.com/UI/Slider
     * @param  string $id
     * @param  string $value
     * @param  array  $params
     * @param  array  $attribs
     * @return string
     */
    public function timeSlider($id, $value = array(), array $params = array(), array $attribs = array())
    {
        if(!isset($attribs['id'])) {
            $attribs['id'] = $id;
        }
        
        $jqh = ZendX_JQuery_View_Helper_JQuery::getJQueryHandler();
        $params = $this->initializeStartingValues($value, $params);
        $params['range'] = true;
        $params['min'] = 0;
        $params['max'] = 1440;
        $params['step'] = 15;
        if($value && is_array($value)) {
            $params['values'] = $value;
            foreach($params['values'] as &$val) {
                $vs = explode(':', $val);
                $val = (int)$vs[0]*60 + (int)$vs[1];
            }
        }
        
        // Hidden Fields
        $hidden = "";
        $startValue = is_array($value) ? $value[0] : '';
        $hiddenAttribs = array('type' => 'hidden', 'id' => $attribs['id']."-start", 'name' => $attribs['id']."[]", 'value' => $startValue);
        $hidden .= '<input' . $this->_htmlAttribs($hiddenAttribs) . $this->getClosingBracket(). PHP_EOL;
        $endValue = is_array($value) ? $value[1] : '';
        $hiddenAttribs = array('type' => 'hidden', 'id' => $attribs['id']."-end", 'name' => $attribs['id']."[]", 'value' => $endValue);
        $hidden .= '<input' . $this->_htmlAttribs($hiddenAttribs) . $this->getClosingBracket(). PHP_EOL;
        
        // Build the Slide functionality of the Slider via javascript, updating hidden fields. aswell as hidden fields
        $sliderUpdateFn = 'function(e, ui) {'.PHP_EOL;
        $sliderUpdateFn .= "    $('#".$attribs['id']."-slider-res').text('"._('c')." '+getTime(ui.values[0])+' "._('по')." '+ getTime(ui.values[1]));";
        $sliderUpdateFn .= "    $('#".$attribs['id']."-start').val(getTime(ui.values[0]));";
        $sliderUpdateFn .= "    $('#".$attribs['id']."-end').val(getTime(ui.values[1]));";
        $sliderUpdateFn .= "}".PHP_EOL;
        $params['slide'] = new Zend_Json_Expr($sliderUpdateFn);
        
        $attribs['id'] .= "-slider";
        
        if(count($params) > 0) {
            $params = ZendX_JQuery::encodeJson($params);
        } else {
            $params = '{}';
        }

        $js = "function getTime(value) {
                var hours = Math.floor(value / 60);
                var minutes = value - (hours * 60);
                if(hours == 24 && minutes == 0) {
                    hours = 23;
                    minutes = 59;
                }
                if(minutes.length == 1) minutes = '0' + minutes;
                if(minutes == 0) minutes = '00';
        
                return hours+':'+minutes;
            };".PHP_EOL;
        
        $js .= sprintf('%s("#%s").slider(%s);', $jqh, $attribs['id'], $params);
        $this->jquery->addOnLoad($js);

        $defVal = is_array($value) ? _('c').' '.$value[0].' '._('по').' '.$value[1] : '';
        $html = '<div style="margin-bottom: 8px;color: black;" id="'.$attribs['id'].'-res">'.$defVal.'</div>';
        $html .= '<div' . $this->_htmlAttribs($attribs) . '>';
        for($i = 0; $i < $handleCount; $i++) {
            $html .= '<div class="ui-slider-handle"></div>';
        }
        $html .= '</div>';

        return $hidden.$html;
    }
}