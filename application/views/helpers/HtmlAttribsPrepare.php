<?php
class HM_View_Helper_HtmlAttribsPrepare extends Zend_View_Helper_Abstract
{
    /**
     * @param array $attribs Атрибуты
     * @param array $addition дополнительные атрибуты
     * @param array $exclude Атрибуты которые нужно исключить
     * @param array $totalReplace  Атрибуты которые нужно полностью заменить
     * @return string
     */
    public function htmlAttribsPrepare($attribs, $addition = array(), $exclude = array(), $totalReplace = array())
    {

        if(!empty($addition) && is_array($addition)){
            foreach($addition as $key=>$value){
                if(isset($attribs[$key])){
                    if(is_array($value)){
                        if(is_array($attribs[$key])){
                            foreach($value as $val){
                                $attribs[$key][] = $val;
                            }
                        }else{
                            $attribs[$key] = array($attribs[$key]);
                            foreach($value as $val){
                                $attribs[$key][] = $val;
                            }
                        }
                    }else{
                        if(is_array($attribs[$key])){
                            $attribs[$key][] = $value;
                        }else{
                            $attribs[$key] = array($attribs[$key]);
                            $attribs[$key][] = $value;
                        }
                    }
                }else{
                    $attribs[$key] = $value;
                }
            }
        }


        if(!empty($exclude) && is_array($exclude)){
            foreach($exclude as $key=>$value){

                if(is_array($value)){
                    foreach($value as $val){
                        if($attribs[$key] == $val){

                            unset($attribs[$key]);

                        }

                        if($kk = array_search($val, $attribs[$key])){
                            unset($attribs[$key][$kk]);
                        }
                    }
                }else{
                    if($attribs[$key] == $value){
                        unset($attribs[$key]);
                    }

                    if(is_array($attribs[$key]) && $kk = array_search($value, $attribs[$key])){
                        unset($attribs[$key][$kk]);
                    }
                }
            }
        }


        if(!empty($totalReplace) && is_array($totalReplace)){
            foreach($totalReplace as $key=>$value){
                $attribs[$key] = $value;
            }


        }

        return $attribs;
    }

}