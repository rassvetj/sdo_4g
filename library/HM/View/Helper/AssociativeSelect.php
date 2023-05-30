<?php
class HM_View_Helper_AssociativeSelect extends Zend_View_Helper_FormElement
{
    /**
     * @param $name
     * @param array $value - массив значения ассоциации "ключ=>значение" (точнее "ключ $options['keys'] => ключ $options['values']")
     * @param array $options
     *              $options['keys']   - массив "ключ=>название" с ключами ассоциации (первый столбец)
     *              $options['values'] - массив "ключ=>название" со значениями ассоциации (второй столбец)
     *              $options['1to1']   - если true, то выбранное значение $options['values'] удаляется из дальнейшего выбора (по аналогии с ключами)
     * @return string
     */
    public function associativeSelect($name, $value = array(), $options = array())
    {
        if (!isset($options['keys']) || !isset($options['values'])) {
            throw new Zend_Exception("Not specify 'keys' or 'values' options");
        }

        $result = '';
        $id = $this->view->id('as');
        if ( is_array($value) && count($value) ) {
            foreach ($value as $key => $value) {
                $result .= '<div class="assossiative-select-row">';
                $result .= $this->view->formHidden(str_replace('[]', "[$key]", $name), $value)
                         . $this->view->formSelect('a_select_key',
                                                   $key,
                                                   null,
                                                   (array('' => _('выберите значение')) + $options['keys']))
                         . $this->view->formSelect('a_select_value',
                                                   $value,
                                                   null,
                                                   (array('' => _('выберите значение')) + $options['values']));
                $result .= '</div>';
            }
        }

        $result .= '<div class="assossiative-select-row">';
        $result .= $this->view->formHidden($name, '', array('disabled' => 'disabled'));
        $result .= $this->view->formSelect('a_select_key', null,  null, (array('' => _('выберите значение')) + $options['keys']))
                . $this->view->formSelect('a_select_value', null, null, (array('' => _('выберите значение')) + $options['values']));
        $result .= '</div>';

        $this->view->id = $id;
        $this->view->name = $name;
        $this->view->inlineScript()->captureStart();
?>    
(function() {
    
    function updateControlValues() {
        var $all = $(<?php echo Zend_Json::encode('#'.$id.' select[name="a_select_key"]') ?>);
        var values = _.compact($all.map(function () {
            return $(this).val();
        }));
        $all.each(function () {
            var options = this.options
                , $options = $(this).find('option')
                , value = $(this).val()
                , $row = $(this).closest('.assossiative-select-row');

            for (var i = 0; i < options.length; ++i) {
                if (value != options[i].value && _.indexOf(values, options[i].value) != -1) {
                    $options.eq(i).prop('disabled', true);
                } else {
                    $options.eq(i).prop('disabled', false);
                }
            }
            if (value) {
                $row.find('input')
                    .prop('disabled', false)
                    .attr('name', <?php echo Zend_Json::encode($name); ?>.replace(/\[\]/, '['+ $(this).val() +']'))
            .val($row.find('select[name="a_select_value"]').val());
            } else {
                $row.find('input').prop('disabled', true);
            }
        })
		
		<? # хак для активации кнопки "Сохранить"?>
		var selected_variants = $(<?php echo Zend_Json::encode('#'.$id.' select[name="a_select_value"] :selected') ?>).not('[value=""]');
		
		if(parseInt('<?=count($options['keys'])?>') == selected_variants.length){
			$('#submit').removeAttr('disabled');
		} else {
			$('#submit').prop('disabled', true);		
		}
		
		
		//console.log('<?=count($options['keys'])?>', $all.length);
    }
    $(document.body).delegate(<?php echo Zend_Json::encode('#'.$id.' select[name="a_select_key"]') ?>, 'change', function () {
        var $this = $(this)
            , $all = $(<?php echo Zend_Json::encode('#'.$id.' select[name="a_select_key"]') ?>)
            , values
            , $row = $this.closest('.assossiative-select-row')
            , $empty = $all.filter(function () { return !$(this).val() })

        if ($empty.length == 0) {
            $row.clone().appendTo($row.parent())
                .find('select, input').val('').end()
                .find('input').prop('disabled', true);
        } else {
            $empty.not(':last')
                .closest('.assossiative-select-row').remove();
        }

        updateControlValues();

    });
    $(document.body).delegate(<?php echo Zend_Json::encode('#'.$id.' select[name="a_select_value"]') ?>, 'change', function () {

        updateControlValues();

    });
	updateControlValues();
    
})();
<?php
        $this->view->inlineScript()->captureEnd();
        /*$this->view->render('associativeSelect.tpl');*/
        return "<div id=\"$id\">".$result."</div>";
    }
}
