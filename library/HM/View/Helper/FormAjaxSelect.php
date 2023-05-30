<?php
class HM_View_Helper_FormAjaxSelect extends Zend_View_Helper_FormSelect
{
    public function formAjaxSelect($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        if (isset($attribs['requestUrl'])) {
            $attribs['disabled'] = true;
            $options = array(_('Загрузка...'));
            $xhtml = "
                <script>
                get".ucfirst($name)."Json = function(url) {
                $('#".$name."').html('<option value=\"0\"> "._('Загрузка...')."</option>');
                $.get(
                    url,
                    '',
                    function(result) {
                        if (result.type != 'error') {

                            var options = '';
                            $(result).each(function() {
                                if (String('$value') == String($(this).attr('id'))) {
                                    options += '<option selected value=\"' + $(this).attr('id') + '\">' + $(this).attr('title') + '</option>';
                                } else {
                                    options += '<option value=\"' + $(this).attr('id') + '\">' + $(this).attr('title') + '</option>';
                                }
                            });
                            $('#".$name."').html(options);
                            $('#".$name."').attr('disabled', false);
                            if (String('$value') != '0') {
                                $('#".$name."').change();
                            }
                        }
                    },
                    'json'
                );
                };
            ";
            if (!isset($attribs['fetchJsonAfterCreate'])) {
                $attribs['fetchJsonAfterCreate'] = true;
            }

            if ($attribs['fetchJsonAfterCreate']) {
                $xhtml .= "get".ucfirst($name)."Json('".$attribs['requestUrl']."');";
            }

            $xhtml .= "
                </script>
            ";
            unset($attribs['requestUrl']);
            unset($attribs['fetchJsonAfterCreate']);
        }
        $xhtml .= parent::formSelect($name, $value, $attribs, $options, $listsep);
        return $xhtml;
    }
}