<?php
class HM_View_Helper_Footnote extends Zend_View_Helper_Placeholder_Container_Standalone
{
    CONST SYMBOL = '*';

    protected $_regKey = 'HM_View_Helper_Footnote';
    protected $_index;

    public function footnote($text = null, $number = null)
    {
        $text = (string) $text;
        if (($text !== '') && !isset($this->_index[$number])) {
            $this->append('<p>' . self::marker($number) . ' <span>' . $text . '</span></p>');
            $this->_index[$number] = true;
        }
        return $this;
    }

    public function toString()
    {
        $items = array();
        foreach ($this as $item) {
            $items[] = $item;
        }
        return count($items) ? '<div class="footnotes"><hr>' . implode('<br>', $items) . '</div>' : '';
    }

    static public function marker($number)
    {
        return str_pad('', $number, self::SYMBOL);
    }
}
