<?php

class HM_Resource_Adapter_Txt extends HM_Resource_Adapter_Abstract
{
    public function readFile()
    {
        $content = file_get_contents($this->_file);
        if (strlen($content)) {
            $encodings = array('UTF-8', 'Windows-1251');
            foreach($encodings as $encoding) {
                if ($content == iconv($encoding, $encoding, $content)) {
                    echo iconv($encoding, 'UTF-8', $content);
                    return true;
                }
            }
        }
    }
}