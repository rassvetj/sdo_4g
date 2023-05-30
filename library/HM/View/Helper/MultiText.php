<?php
class HM_View_Helper_MultiText extends Zend_View_Helper_FormElement
{
    /**
     * @param $name
     * @param array $values - массив значений
     * @return string
     */
    public function multiText($name, $value = array(), $options = array())
    {
        $i = 0;
        $result = '';
        $id = $this->view->id('mt');
        if ( is_array($value) && count($value) ) {
            foreach ($value as $key => $val) {
                $result .= '<div class="multitext-row">';
                // @todo: куде делся декоратор label'ов?
                $result .= $this->view->formText($name, $val, array('Label' => sprintf($options['SubLabel'], ++$i)));
                $result .= '</div>';
            }
        }

        $result .= '<div class="multitext-row">';
        $result .= $this->view->formText($name, '', array('Label' => sprintf($options['SubLabel'], ++$i)));
        $result .= '</div>';

        $this->view->inlineScript()->captureStart();
?>
$(document.body).delegate(<?php echo Zend_Json::encode('#'.$id.' input[type="text"]') ?>, 'keypress', function () {
    var $this = $(this)
      , $all = $(<?php echo Zend_Json::encode('#'.$id.' input[type="text"]') ?>)
      , values
      , $row = $this.closest('.multitext-row')
      , $empty = $all.filter(function () { return !$(this).val() })

    if ($empty.length == 0) {
        $row.clone().appendTo($row.parent())
            .find('input').val('').end();
    } else {
        $empty.not(':last')
            .closest('.multitext-row').remove();
    }
});
<?php
        $this->view->inlineScript()->captureEnd();
        /*$this->view->render('associativeSelect.tpl');*/
        return "<div id=\"$id\">".$result."</div>";
    }
}
