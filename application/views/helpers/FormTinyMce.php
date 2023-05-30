<?php
class HM_View_Helper_FormTinyMce extends Zend_View_Helper_FormElement
{
        /**
     * The default number of rows for a textarea.
     *
     * @access public
     *
     * @var int
     */
    public $rows = 24;

    /**
     * The default number of columns for a textarea.
     *
     * @access public
     *
     * @var int
     */
    public $cols = 80;
   
     /**
     * Set view and enable jQuery Core and UI libraries
     *
     * @param  Zend_View_Interface $view
     * @return ZendX_JQuery_View_Helper_Widget
     */
    public function setView(Zend_View_Interface $view)
    {
        parent::setView($view);
        $this->tinyMce = $this->view->TinyMce();
        $this->tinyMce->enable();
        return $this;
    }
       
    public function formTinyMce($name, $value = null, $attribs = null, $options = null)
    {
        $this->view->TinyMce()->enable();
               
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        // is it disabled?
        $disabled = '';
        if ($disable) {
            // disabled.
            $disabled = ' disabled="disabled"';
        }

        // Make sure that there are 'rows' and 'cols' values
        // as required by the spec.  noted by Orjan Persson.
        if (empty($attribs['rows'])) {
            $attribs['rows'] = (int) $this->rows;
        }
        if (empty($attribs['cols'])) {
            $attribs['cols'] = (int) $this->cols;
        }
        if (isset($attribs['editorOptions'])) {
                $attribs['editorOptions']['mode'] = 'specific_textareas';
               
                if (!isset($attribs['editorOptions']['theme'])) $attribs['editorOptions']['theme'] = "simple";
               
                $this->tinyMce->setEditorOptions($attribs['editorOptions']);
                       
                if (!isset($attribs['class'])) {
                        $attribs['class'] =  $attribs['editorOptions']['editor_selector'];     
                } else {
                        $attribs['class'] .= ' ' . $attribs['editorOptions']['editor_selector'];
                }
               
               
                unset($attribs['editorOptions']);
        }
                       
        if (!isset($attribs['class'])) {
                $attribs['class'] =  'tinyMceEditor' ; 
        }
       

        // build the element
        $xhtml = '<textarea name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"';
               
       
                       
        $xhtml .=  $disabled
                . $this->_htmlAttribs($attribs) . '>'
                . $this->view->escape($value) . '</textarea>';

        return $xhtml;
    }
}