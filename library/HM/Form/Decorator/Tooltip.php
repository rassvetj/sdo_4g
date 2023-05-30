<?php
/** Zend_Form_Decorator_Abstract */
require_once 'Zend/Form/Decorator/Abstract.php';

class HM_Form_Decorator_Tooltip extends Zend_Form_Decorator_Abstract
{
    /**
     * Whether or not to escape the description
     * @var bool
     */
    protected $_escape;

    /**
     * Default placement: append
     * @var string
     */
    protected $_placement = 'PREPEND';

    /**
     * HTML tag with which to surround description
     * @var string
     */
    protected $_tag;

    /**
     * Set HTML tag with which to surround description
     *
     * @param  string $tag
     * @return Zend_Form_Decorator_Description
     */
    public function setTag($tag)
    {
        $this->_tag = (string) $tag;
        return $this;
    }

    /**
     * Get HTML tag, if any, with which to surround description
     *
     * @return string
     */
    public function getTag()
    {
        if (null === $this->_tag) {
            $tag = $this->getOption('tag');
            if (null !== $tag) {
                $this->removeOption('tag');
            } else {
                $tag = 'p';
            }

            $this->setTag($tag);
            return $tag;
        }

        return $this->_tag;
    }

    /**
     * Get class with which to define description
     *
     * Defaults to 'hint'
     *
     * @return string
     */
    public function getClass()
    {
        $class = $this->getOption('class');
        if (null === $class) {
            $class = 'hint';
            $this->setOption('class', $class);
        }

        return $class;
    }

    /**
     * Set whether or not to escape description
     *
     * @param  bool $flag
     * @return Zend_Form_Decorator_Description
     */
    public function setEscape($flag)
    {
        $this->_escape = (bool) $flag;
        return $this;
    }

    /**
     * Get escape flag
     *
     * @return true
     */
    public function getEscape()
    {
        if (null === $this->_escape) {
            if (null !== ($escape = $this->getOption('escape'))) {
                $this->setEscape($escape);
                $this->removeOption('escape');
            } else {
                $this->setEscape(true);
            }
        }

        return $this->_escape;
    }

    /**
     * Render a description
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $description = $element->getDescription();
        $description = trim($description);

        if (!empty($description) && (null !== ($translator = $element->getTranslator()))) {
            $description = $translator->translate($description);
        }

        if (empty($description)) {
            return $content;
        }

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $tag       = $this->getTag();
        $class     = $this->getClass();
        $escape    = $this->getEscape();

        $options   = $this->getOptions();

        if ($escape) {
            $description = $view->escape($description);
        }

        if (!empty($tag)) {
            require_once 'Zend/Form/Decorator/HtmlTag.php';
            $options['tag'] = $tag;
            $decorator = new Zend_Form_Decorator_HtmlTag($options);
            $description = $decorator->render($description);
        }
        
        if ($element instanceof Zend_Form_Element_Text || 
            $element instanceof Zend_Form_Element_Password ||
            $element instanceof Zend_Form_Element_Textarea) {
          // если компоненты определенного типа, то сохраняем информацию для создания всплывающих подсказок
          $id = $element->getId();
          
          $view->inlineScript()->appendScript("
              window.hm = window.hm || {};
              window.hm.tooltips = window.hm.tooltips || [];
              
              window.hm.tooltips.push({
                id: '$id',
                description: '$description'
              });
          ");
        }

        $js = "
            yepnope({
                test: Modernizr.canvas,
                nope: ['/js/lib/jquery/excanvas.compiled.js'],
                complete: function () {
                    yepnope({
                        test: $.fn.bt,
                        nope: [
                            '/css/jquery-ui/jquery.ui.tooltip.css',
                            '/js/lib/jquery/jquery.hoverIntent.minified.js',
                            '/js/lib/jquery/jquery.ui.tooltip.js'
                        ],
                        complete: function () {
                            _.delay(function () {
                                jQuery(function ($) {
                                    $('.tooltip').bt({killTitle: false});
                                    
                                    // создаём дополнительные тултипы по фокусу
                                    if (window.hm && window.hm.tooltips) {
                                      var tooltips = window.hm.tooltips;
                                      for (var i = 0, ln = tooltips.length; i < ln; i++) {
                                        $('#' + tooltips[i].id).bt(tooltips[i].description, {
                                          positions: ['right', 'left'],
                                          trigger: ['focus', 'blur'],
                                          spikeLength: 7,
                                          overlap: -5,
                                          centerPointY: 0.3
                                        });
                                      }
                                    }
                                });
                            }, 100);
                        }
                    });
                }
            });

        ";
        
        
        $view->inlineScript(Zend_View_Helper_HeadScript::SCRIPT)->offsetSetScript("tooltip_decorator", $js);

        $description = '
        <div class="tooltip-description" style="display: none;">'.$description.'</div>
        <span class="tooltip"></span><span class="label_position">
        ';

        switch ($placement) {
            case self::PREPEND:
                return $description . $separator . $content . '</span>';
            case self::APPEND:
            default:
                return $content . $separator . $description . '</span>';
        }
    }

}
