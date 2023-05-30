<?php
class HM_DataSheet
{
    private $_view = null;
    private $_id = null;
    private $_horizontalHeader = null;
    private $_verticalHeader = null;
    private $_horizontalActions = null;
    private $_verticalActions = null;
    private $_data = null;
    private $_saveUrl = null;
    private $_messages = null;

    public function __construct($options, $id)
    {
        $this->_id = $id;

        // todo: check options
        if (isset($options['verticalHeader'])) {

            if (!isset($options['verticalHeader']['name'])) {
                $options['verticalHeader']['name'] = 'rows';
            }

            $this->_verticalHeader = new HM_DataSheet_Header(
                (string)  $options['verticalHeader']['name'],
                (string)  $options['verticalHeader']['title'],
                (boolean) $options['verticalHeader']['checkboxes'],
                (array)   $options['verticalHeader']['fields']
            );
        }

        if (isset($options['horizontalHeader'])) {

            if (!isset($options['horizontalHeader']['name'])) {
                $options['horizontalHeader']['name'] = 'cols';
            }

            $this->_horizontalHeader = new HM_DataSheet_Header(
                (string)  $options['horizontalHeader']['name'],
                (string)  $options['horizontalHeader']['title'],
                (boolean) $options['horizontalHeader']['checkboxes'],
                (array)   $options['horizontalHeader']['fields']
            );
        }

        if (isset($options['data'])) {
            $this->_data = $options['data'];
        }

        if (isset($options['saveUrl'])) {
            $this->_saveUrl = (string) $options['saveUrl'];
        }

        $this->_messages = $this->getDefaultMessages();

        foreach(array_keys($this->getDefaultMessages()) as $alias) {
            if (isset($options['messages'][$alias])) {
                $this->_messages[$alias] = $options['messages'][$alias];
            }
        }
    }

    public function getDefaultMessages()
    {
        return array(
            'noVerticalActionSelected' => _('Не выбрано ни одного действия со строками'),
            'noVerticalSelected' => _('Не выбрано ни одной строки'),
            'noHorizontalActionSelected' => _('Не выбрано ни одного действия с колонками'),
            'noHorizontalSelected' => _('Не выбрано ни одной колонки'),
            'formError' => _('Ошибка формы'),
            'ok' => _('Закрыть'),
            'confirm' => _('Подтверждение'),
            'areUshure' => _('Вы уверены?'),
            'yes' => _('Да'),
            'no' => _('Нет')
        );
    }

    public function setMessage($alias, $message)
    {
        $this->_messages[$alias] = $message;
    }

    public function getMessage($alias)
    {
        return $this->_messages[$alias];
    }

    /**
     * @static
     * @param $class
     * @param array $options
     * @param string $id
     * @return HM_DataSheet
     */
    static public function factory($class, $options = array(), $id = 'datasheet')
    {
        if (strpos($class, '_') === false ) {
            $class = 'HM_DataSheet_Deploy_' . ucfirst(strtolower($class));
        }

        $sheet = new $class($options, $id);
        return $sheet;
    }

    public function setView(Zend_View_Interface $view = null)
    {
        $this->_view = $view;
        return $this;
    }


    /**
     * Retrieve view object
     *
     * If none registered, attempts to pull from ViewRenderer.
     *
     * @return Zend_View_Interface|null
     */
    public function getView()
    {
        if ( null === $this->_view ) {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $this->setView($viewRenderer->view);
        }

        return $this->_view;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setHorizontalHeader(HM_DataSheet_Header $header = null)
    {
        $this->_horizontalHeader = $header;
    }

    /**
     * @return HM_DataSheet_Header|null
     */
    public function getHorizontalHeader()
    {
        return $this->_horizontalHeader;
    }

    public function setVerticalHeader(HM_DataSheet_Header $header = null)
    {
        $this->_verticalHeader = $header;
    }

    /**
     * @return HM_DataSheet_Header|null
     */
    public function getVerticalHeader()
    {
        return $this->_verticalHeader;
    }

    public function setData($data)
    {
        $this->_data = $data;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function getValue($hId, $vId)
    {
        if (isset($this->_data[$hId][$vId])) {
            $field = $this->getHorizontalHeader()->getField($hId);
            $renderClass =  $field->getRender();
            return new $renderClass($this->_data[$hId][$vId], $field->getOptions(), $hId, $vId);
        }
        return null;
    }

    public function setSaveUrl($url)
    {
        $this->_saveUrl = $url;
    }

    public function getSaveUrl()
    {
        return $this->_saveUrl;
    }

    public function deploy()
    {
        if (null === $this->getHorizontalHeader()) {
            throw new HM_Exception(_('Horizontal header is not defined'));
        }

        if (null === $this->getVerticalHeader()) {
            throw new HM_Exception(_('Vertical header is not defined'));
        }

        if (null == $this->getSaveUrl()) {
            throw new HM_Exception(_('Save url is not defined'));
        }
    }

    public function setHorizontalActions(HM_DataSheet_Actions $actions)
    {
        $this->_horizontalActions = $actions;
    }

    public function getHorizontalActions()
    {
        return $this->_horizontalActions;
    }

    public function setVerticalActions(HM_DataSheet_Actions $actions)
    {
        $this->_verticalActions = $actions;
    }

    public function getVerticalActions()
    {
        return $this->_verticalActions;
    }

}