<?php
class HM_View_Helper_ReportChart extends HM_View_Helper_Abstract
{
    const TABLE_DISPLAY_NONE = 0;  
    const TABLE_DISPLAY_INLINE = 1;  
    const TABLE_DISPLAY_BLOCK = 2;  
    
    protected $_chartId;
    protected $_chartType;
    protected $_chartData;
    protected $_chartHeight;
    
    protected $_multiGraph;
    
    static public $palette = array();

    public function setPalette($colors)
    {
        self::$palette = $colors;
    } 
    
    public function reportChart($chartId, $chartType, $chartData, $chartTitle, $chartHeight = '250', $showTable = self::TABLE_DISPLAY_INLINE, $multiGraph = true)
    {
        if (!is_array($chartData) || !count($chartData)) return '';
        
        $this->_chartId = $chartId;
        $this->_chartType = $chartType;
        $this->_chartData = $chartData;
        $this->_chartHeight = $chartHeight;
        $this->_multiGraph = $multiGraph;
        
        $this->_addChart();
        
        $this->view->chartId = $chartId;
        $this->view->data = $chartData;
        $this->view->title = $chartTitle;
        //$this->view->width = $chartWidth;
        $this->view->showtable = $showTable;
        $this->view->multigraph = $multiGraph;
        
        return $this->view->render('report-chart.tpl');
    }
    
    protected function _addChart()
    {
		$baseUrl = Zend_Registry::get('config')->url->base;
		$this->view->headScript()->appendFile("{$baseUrl}js/lib/amcharts/swfobject.js");
        
		$loadingStr = _('Загрузка');
		$xmlSettings = $this->_getXmlSettings();
		$xmlData = $this->_getXmlData();
		
		$script = <<<E0D
			$(function(){
				{$this->_chartId}Chart = new SWFObject("{$baseUrl}js/lib/amcharts/{$this->_chartType}.swf", "{$this->_chartId}", "100%", {$this->_chartHeight}, "8", "#FFFFFF");
				{$this->_chartId}Chart.addVariable("chart_id", "{$this->_chartId}");
				{$this->_chartId}Chart.addVariable("path", "{$baseUrl}js/lib/amcharts/");
  				{$this->_chartId}Chart.addVariable("chart_settings", "{$xmlSettings}");
   				{$this->_chartId}Chart.addVariable("chart_data", "{$xmlData}");
				{$this->_chartId}Chart.addVariable("loading_settings", '{$loadingStr}');
				{$this->_chartId}Chart.addVariable("loading_data", '{$loadingStr}');
				{$this->_chartId}Chart.addParam("wmode", 'opaque');
				{$this->_chartId}Chart.write("{$this->_chartId}");
			});
E0D;
		$this->view->headScript()->appendScript($script);
    }
    
    protected function _getXmlSettings()
    {
        $view = new Zend_View();
        
        $view->setScriptPath(APPLICATION_PATH . "/views/helpers/views/report-chart/{$this->_chartType}/");
        $xml = $view->render("settings.tpl");
        return self::_plainify($xml);         
    }
    
    protected function _getXmlData()
    {
        $view = new Zend_View();
        $view->id = $this->_chartId;
        
        // preserving keys
        $series = ($this->_multiGraph) ? array_slice($this->_chartData, 0, 1, true) : array_shift($this->_chartData);
        $data = ($this->_multiGraph) ? array_slice($this->_chartData, 1, null, true) : array_pop($this->_chartData);

        $view->series = $series;
        $view->data = $data;
        $view->colors = self::$palette;
        
        $view->setScriptPath(APPLICATION_PATH . "/views/helpers/views/report-chart/{$this->_chartType}/");
        $xml = $view->render("data.tpl");
        //if (!$this->_multiGraph) exit($xml);
        return self::_plainify($xml);  
    }
    
    static protected function _plainify($xml)
    {
        return preg_replace('~\s*(<([^>]*)>[^<]*</\2>|<[^>]*>)\s*~','$1', str_replace(array('"', "\r", "\n"), "'", $xml));
    } 
}