<?php
class HM_Report_Config
{
    private $_config = null;

    public function __construct($filename = null)
    {
        if (null === $filename) {
            $filename = APPLICATION_PATH . '/settings/report.yml';
        }
        $this->_config = $this->_convertEncoding(sfYaml::load($filename));

        if (!is_array($this->_config) || !count($this->_config)) {
            throw new HM_Exception(sprintf(_('Invalid format of config file: %s'), $filename));
        }

    }

    private function _convertEncoding($config)
    {
        if (is_array($config) && count($config)) {
            foreach($config as $index => $item) {
                if (is_array($item) && count($item)) {
                    $config[$index] = $this->_convertEncoding($item);
                }

                if (is_string($item)) {
                    $config[$index] = iconv('UTF-8', Zend_Registry::get('config')->charset, _($item));
                }

            }
        }
        return $config;
    }

    public function getDomains()
    {
        $domains = array();
        foreach($this->_config['domains'] as $key => $domain) {
            $domains[$key] = $domain['title'];
        }
        
        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_REPORT_DOMAINS);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $domains);
        $domains = $event->getReturnValue();
        
        return $domains;
    }

    public function getPath($domain, $from, $to)
    {
        if (isset($this->_config['domains'][$domain]['paths'][$from][$to])) {
            return explode(',', $this->_config['domains'][$domain]['paths'][$from][$to]);
        }
        return false;
    }

    public function getDomain($name)
    {
        return _($this->_config['domains'][$name]);
    }

    public function getTable($domain, $table)
    {
        if (!($this->_config['domains'][$domain]['tables'][$table] instanceof HM_Report_Table)) {
            $this->_config['domains'][$domain]['tables'][$table]['name'] = $table;
            $this->_config['domains'][$domain]['tables'][$table] = new HM_Report_Table($this->_config['domains'][$domain]['tables'][$table]);
        }
        return $this->_config['domains'][$domain]['tables'][$table];
    }

    public function getTables($domain)
    {
        $tables = array();
        foreach($this->_config['domains'][$domain]['tables'] as $tableName => $table)
        {
            $tables[$tableName] = $this->getTable($domain, $tableName);
        }
        return $tables;
    }

    public function getField($domain, $category, $field)
    {
        if (!($this->_config['domains'][$domain]['categories'][$category]['fields'][$field] instanceof HM_Report_Table_Field)) {
            $this->_config['domains'][$domain]['categories'][$category]['fields'][$field]['name'] = _($field);
            $this->_config['domains'][$domain]['categories'][$category]['fields'][$field] = new HM_Report_Table_Field(
                $this->_config['domains'][$domain]['categories'][$category]['fields'][$field]
            );
        }

        return _($this->_config['domains'][$domain]['categories'][$category]['fields'][$field]);
    }


}