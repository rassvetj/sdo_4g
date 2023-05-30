<?php
class HM_Filter_StringTrim extends Zend_Filter_StringTrim
{
    protected function _unicodeTrim($value, $charlist = '\\\\s')
    {

        //if (trim($value) == 'Р') return $value;
        // на виндовых верваках preg_replace строки начинающейся или заканчивающейся  символом "Р" вешает скрипт
        if (strchr($value,'Р')) {
            return $value;
        }

        $chars = preg_replace(
            array( '/[\^\-\]\\\]/S', '/\\\{4}/S', '/\//'),
            array( '\\\\\\0', '\\', '\/' ),
            $charlist
        );

        $pattern = '^[' . $chars . ']*|[' . $chars . ']*$';
        $result = preg_replace("/$pattern/sSDu", '', $value);

        if (null === $result) {
            $result = $this->_slowUnicodeTrim($value, $chars);
        }

        return $result;
    }

    protected function _slowUnicodeTrim($value, $chars) {
        $utfChars = $this->_splitUtf8($value);
        $pattern = '/^[' . $chars . ']$/usSD';

        while ($utfChars && preg_match($pattern, $utfChars[0])) {
            array_shift($utfChars);
        }

        while ($utfChars && preg_match($pattern, $utfChars[count($utfChars) - 1])) {
            array_pop($utfChars);
        }

        return implode('', $utfChars);
    }

    protected function _splitUtf8($value)
    {
        $utfChars = str_split(iconv('UTF-8', 'UTF-32BE', $value), 4);
        array_walk($utfChars, create_function('&$char', '$char = iconv("UTF-32BE", "UTF-8", $char);'));
        return $utfChars;
    }

}