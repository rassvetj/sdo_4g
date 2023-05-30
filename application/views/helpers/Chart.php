<?php
class HM_View_Helper_Chart extends HM_View_Helper_Abstract {

    protected static $_jsBridgeInited = false;
	private $chartId;

	public function chart($chartId, $chartType = 'amcolumn', $width = '100', $height = '300', $params = array())
	{
		$this->chartId = $chartId;
		$msg = _('Загрузка...');
		$html = <<<E0D
			<div id="{$this->chartId}-chart-container" style="width: {$width}%;">
				<p>{$msg}</p>
			</div>
E0D;

		$baseUrl = Zend_Registry::get('config')->url->base;
        $this->view->headScript()->appendFile("{$baseUrl}js/lib/jquery/jquery.urldecoder.min.js");
		$this->view->headScript()->appendFile("{$baseUrl}js/lib/amcharts/swfobject.js");

		$controller = $this->chartId;
		if(isset($params['controller'])){
		    $controller = $params['controller'];
		}
		
		$time = time();
		$dataUrlArr = $settingUrlArr = array(
			'module' => 'infoblock',
			'controller' => $controller,
			'format' => 'xml',
		);
		
		if(!empty($params)){
		    $dataUrlArr = $settingUrlArr = array_merge($dataUrlArr, $params);
		}
		
		
		$dataUrlArr['action'] = 'get-data';
		$settingUrlArr['action'] = 'get-settings';

		$dataUrl = $this->view->url($dataUrlArr);
		$settingUrl = $this->view->url($settingUrlArr);
		$loadingStr = _('Загрузка');

		$jsBridge = self::getJsBridge();
		$script = <<<E0D
			$(function(){
				{$this->chartId}Chart = new SWFObject("{$baseUrl}js/lib/amcharts/{$chartType}.swf", "{$this->chartId}-chart", "100%", {$height}, "8", "#FFFFFF");
				{$this->chartId}Chart.addVariable("chart_id", "{$this->chartId}-chart");
				{$this->chartId}Chart.addVariable("path", "{$baseUrl}js/lib/amcharts/");
				{$this->chartId}Chart.addVariable("settings_file", encodeURIComponent('{$settingUrl}'));
				{$this->chartId}Chart.addVariable("data_file", encodeURIComponent('{$dataUrl}'));
				{$this->chartId}Chart.addVariable("loading_settings", '{$loadingStr}');
				{$this->chartId}Chart.addVariable("loading_data", '{$loadingStr}');
				{$this->chartId}Chart.addParam("wmode", 'opaque');
				{$this->chartId}Chart.write("{$this->chartId}-chart-container");
			});
			{$jsBridge}
E0D;
		$this->view->headScript()->appendScript($script);
		return $html;
	}

    public static function getJsBridge()
    {
        if (!self::$_jsBridgeInited) {
			$script = <<<E0D
				var data = new Array();
				function amChartInited(chart_id) {
					chart = document.getElementById(chart_id);
					chart.getData();
				}

				function amReturnData(chart_id, chart_data) {
					data[chart_id] = $.url.decode(chart_data);
					eval(chart_id.replace('-','') + 'Inited()');
				}
E0D;
        	return $script;
        }
        return '';
    }

}
