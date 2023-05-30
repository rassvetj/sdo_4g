<?php

class SharePointXml {
    static function removeNS($str) {
        return preg_replace(
            array(
                '/<[a-z]+?:([a-z]+?)/is', 
                '/[a-z]+?:([a-z]+?)="/is', 
                '/="[a-z]+?:/is', 
                '/<\/[a-z]+?:([a-z]+?)>/is'
            ), 
            array(
                "<\$1", 
                "\$1=\"", 
                "=\"", 
                "</\$1>"
            ), 
            $str);
    }
    
    static function convertStringFrom($str) {
        return iconv(SHAREPOINT_IN_CHARSET, SHAREPOINT_OUT_CHARSET, $str);
    }
    
    static function convertStringTo($str) {
        return iconv(SHAREPOINT_OUT_CHARSET, SHAREPOINT_IN_CHARSET, $str);        
    }
}

?>