<?php 
/**
 * TinyMce View Helper. Transports all TinyMce stack and render information across all views.
 *
 * @category   Zucchi
 * @package    Zucchi_Wysiwyg
 * @subpackage Element
 * @copyright  Copyright (c) 2009 Zucchi.co.uk
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @todo: implement custom cleanup to filter cut and paste MS code
 * @todo: implement noneditable regions
 * @todo: build widget builder
 * @todo: add custom event to load widget builder plugin
 * @todo: improve to allow accurate use with screen readers
 * @todo: create ajax save
 * @todo: create validation to prevent duplicate init routines being printed to screen
 */
class HM_View_Helper_TinyMce_Container
{

    /**
     * Indicates wheater the jQuery View Helper is enabled.
     *
     * @var Boolean
     */
    protected $_enabled = false;

    /**
     * View is rendered in XHTML or not.
     *
     * @var Boolean
     */
    protected $_isXhtml = false;
   
    /**
     * path to tinymce files
     * @var String
     */
    protected $_localPath = '/lib/tinymce/';
   
    /**
     * use tinymce PHP compressor
     * @var Boolean
     */
    protected $_useCompressor = true;

    /**
     * active tinymce plugins
     * @var Array
     */
    protected $_defaultPlugins = array('style', 'layer', 'table', 'save',
                                       'advhr', 'advimage', 'advlink', 'emotions',
                                       'iespell', 'insertdatetime', 'preview', 'media',
                                       'searchreplace', 'print', 'contextmenu', 'paste',
                                       'directionality', 'fullscreen', 'noneditable', 'visualchars',
                                       'nonbreaking', 'xhtmlxtras', 'images');

    protected $_registeredPlugins = array();
   
    /**
     * available themes
     * @var Array
     */
    protected $_themes = array('simple', 'advanced');
       
    /**
    * available languages
    * @var Array
    */
    protected $_languages = array('ru', 'en');
       
    /**
    * default values
    * @var Array
    */
    protected $_defaultEditor = array('mode'     => 'specific_textareas',
                                      'theme'    => "simple",
                                      'selector' => "tinyMceEditor",
                                      'editor_selector' => "tinyMceEditor",    
                                      'language' => 'ru',
                                      'css'      => ""                                     
    );

    /**
     * container for multiple editor configurations
     * @var Mixed
     */                                                                      
    protected $_editors = false;
       


       
    /**
     * View Instance
     *
     * @var Zend_View_Interface
     */
    public $view = null;

   
    /**
     * build additional plugin data
     * @return null
     */
    public function __construct()
    {
       
    }
   
    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return void
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    /**
     * Enable jQuery
     *
     * @return Zucchi_Wysiwyg_View_Helper_TinyMce_Container
     */
    public function enable()
    {
        $this->_enabled = true;
        return $this;
    }

    /**
     * Disable TinyMce
     *
     * @return Zucchi_Wysiwyg_View_Helper_TinyMce_Container
     */
    public function disable()
    {
        $this->_enabled = false;
        return $this;
    }

    /**
     * Is TinyMce enabled?
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->_enabled;
    }

    /**
     * Set path to local TinyMce library
     *
     * @param  string $path
     * @return Zucchi_Wysiwyg_View_Helper_TinyMce_Container
     */
    public function setLocalPath($path)
    {
        $this->_localPath = (string) $path;
        return $this;
    }

    /**
     * Get local path to jQuery
     *
     * @return string
     */
    public function getLocalPath()
    {
        return $this->_localPath;
    }

    /**
     * Are we using a local path?
     *
     * @return boolean
     */
    public function useLocalPath()
    {
        return (null === $this->_localPath) ? false : true;
    }


    public function setCompressorOptions($location = '/lib/tinymce/', array $plugins = null, array $themes = null, array $languaues = null)
    {
        	
        $this->_localPath = $location;   
       
        if ($plugins) {
                $this->_defaultPlugins = $plugins;
        }
        if ($themes) {
                $this->_themes = $themes;
        }
        if ($languages) {
                $this->_languages = $languages;
        }
    }
   
    /**
     * array ( "selectorName" => array ( 'mode'
     * @param $config
     * @return unknown_type
     */
   
    public function setEditorOptions($config)
    {
        $this->addEditorOptions($config);
    }
   
    public function addEditorOptions($config)
    {
        if ($config instanceof Zend_Config) {
                $config = $config->toArray();
        }
        // check for existsing css selector and overwrite
        $selector = (isset($config['editor_selector'])) ? $config['editor_selector'] : 'no_selector';
        $this->_editors[$selector] = $config;

        if (isset($config['plugins'])) {
                $this->addPlugins($config['plugins'], $selector);      
        }
       
    }
   
    public function addPlugins($config, $editor_selector)
    {
        if ($config instanceof Zend_Config) {
                $config = $config->toArray();
        }
        foreach($config AS $pluginName => $pluginConfig) {
                if (class_exists('HM_View_Helper_TinyMce_' . ucfirst(strtolower($pluginName)))) {
                        $pluginClass = 'HM_View_Helper_TinyMce_' . ucfirst(strtolower($pluginName));
                        $this->_registeredPlugins[$editor_selector][$pluginName] = new $pluginClass($config);
                }
        }
    }
   
   
    /**
     * String representation of jQuery environment
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->isEnabled()) {
            return '';
        }
        $script  = $this->_renderCompressor() . PHP_EOL
               . $this->_renderInit() . PHP_EOL;
        return $script;
    }
   
    protected function _renderCompressor()
    {              
        if ($this->_useCompressor) {
                $registeredPlugins = array();
                        if (count($this->_registeredPlugins) > 0 ) {
                                foreach ($this->_registeredPlugins AS $editor => $data) {
                                        foreach ($data AS $pluginName => $pluginData) {
                                                if (!in_array($pluginName, $registeredPlugins));
                                                $registeredPlugins[] = $pluginName;
                                        }
                                }
                        }
                        $plugins = join(',',array_merge($registeredPlugins, $this->_defaultPlugins));
                       
                $themes = join(',', $this->_themes);
                $languages = join(',', $this->_languages);                
               
                $compressor =  "
                <script type=\"text/javascript\" src=\"".$this->_localPath."tiny_mce_gzip.js\"></script>
                        <script type=\"text/javascript\">
                        tinyMCE_GZ.init({
                                        baseUrl: '".$this->_localPath."',
                                        plugins : '".$plugins."',
                                        themes : '".$themes."',
                                        languages : '".$languages."',
                                        disk_cache : true,
                                        debug : false
                                });
                        </script>";
                return $compressor;
        } else {
                return "<script type=\"text/javascript\" src=\"".$this->_localPath."tiny_mce.js\"></script>";
        }
        return '';
    }

        protected function _renderInit()
        {
                $init = '<script type="text/javascript">';
                if ($this->_editors) {
                        foreach ($this->_editors AS $editorSelector => $editorConfig) {
                                $init .= $this->_renderEditor($editorConfig, $editorSelector);
                        }
                } else {
                        $init .= $this->_renderEditor($this->_defaultEditor);
                }
                $init .= '</script>';
                return $init;
        }    
       
       

        protected function _renderEditor($config = null, $editorSelector = null)
        {
        	
                $editor = '
                        tinyMCE.init({' . PHP_EOL;
                $config['selector'] = "tinyMceEditor";
                $config['editor_selector'] = "tinyMceEditor";    
                if (isset($config['mode'])) {
                        $editor .= ' mode: "'.$config['mode'].'" ' . PHP_EOL ;
                        unset($config['mode']);

                        if (isset($config['plugins'])) {
                                $registeredPlugins = array();
                                if (count($this->_registeredPlugins) > 0 ) {
                                        foreach ($this->_registeredPlugins[$editorSelector] AS $pluginName => $pluginData) {
                                                $registeredPlugins[] = $pluginName;
                                        }
                                }
                                $plugins = join(',',array_merge($registeredPlugins, $this->_defaultPlugins));
                               
                                $editor .= ', plugins: "' . $plugins . '" '. PHP_EOL;
                        } else {
                                $plugins = join(',',$this->_defaultPlugins);
                                $editor .= ', plugins: "' . $plugins . '" '. PHP_EOL;
                        }
                        foreach($config AS $key => $value) {
                                if (is_array($value)) {
                                        $value = join(', ',$value);
                                }
                                switch ($key) {
                                        default:
                                                $editor .= ', '.$key.': "'.$value.'" '.PHP_EOL;
                                                break; 
                                }
                        }
                }
               
               
                if (count($this->_registeredPlugins) > 0 ) {
                        foreach ($this->_registeredPlugins[$editorSelector] AS $plugin) {
                                $editor .= $plugin->__toString();
                                $editor .= PHP_EOL;
                        }
                }
                $editor .= '
                        });'. PHP_EOL;
                return $editor;
        }
       
       

}