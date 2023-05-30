{?php?}

require_once('Zend/Locale.php');
require_once('Zend/Json.php');
require_once('Zend/Config.php');
require_once('Zend/Config/Ini.php');
require_once('Zend/Registry.php');

$tplThis = $this->get_template_vars('this');
$this->assign('theme_url', $tplThis->root_url."/themes/".$tplThis->theme);
$this->assign('root_url', $tplThis->root_url);

$attributes = array();

$attributes['env'] = isset($_SERVER["APPLICATION_ENV"]) ? $_SERVER["APPLICATION_ENV"] : 'production';

$locale = Zend_Registry::isRegistered('Zend_Locale') ? Zend_Registry::get('Zend_Locale') : NULL;
if (isset($locale) and $locale) {
    $attributes['locale'] = array(
        'region'   => $locale->getRegion(),
        'language' => $locale->getLanguage(),
        'locale'   => $locale->toString()
    );
} else {
    $attributes['locale'] = array(
        'region'   => 'RU',
        'language' => 'ru',
        'locale'   => 'ru_RU'
    );
}

$config = Zend_Registry::isRegistered('config') ? Zend_Registry::get('config') : NULL;
if (isset($config) and $config and isset($config->webuiclient) and ($config->webuiclient instanceof Zend_Config)) {
    $attributes['config'] = $config->webuiclient->toArray();
}

if (isset($_SESSION['s']['mid']) and $_SESSION['s']['mid']) {
    $attributes['user'] = array(
        'id'         => $_SESSION['s']['mid'],
        'login'      => $_SESSION['s']['login'],
        'permission' => $_SESSION['s']['perm']
    );
}

$sessionGc_maxlifetime = ini_get('session.gc_maxlifetime');
if ($sessionGc_maxlifetime === FALSE) {
    $attributes['config']['pingInterval'] = 1440 / 2;
} else {
    $attributes['config']['pingInterval'] = (int)((int)$sessionGc_maxlifetime / 2);
}

$attributes_array = array();
foreach ($attributes as $attr => $value) {
    if (is_array($value)) {
        $value = Zend_Json::encode($value);
    }
    $attributes_array["data-".$attr] = $value;
}

$this->assign('is_development', $_SERVER["APPLICATION_ENV"] == 'development' ? true : false);

$this->assign('translateJsFileName', Zend_Registry::get('translate')->getAdapter()->translateJsFileName);

{?/php?}
<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js ie6 ie"> <![endif]-->
<!--[if IE 7 ]>   <html class="no-js ie7 ie"> <![endif]-->
<!--[if IE 8 ]>   <html class="no-js ie8 ie"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js no-ie"> <!--<![endif]-->
	<head>
		<meta charset="{?$encoding?}">
		<title>{?$this->title?}</title>
		<meta content="" name="description">
		<meta content="" name="author">
		<meta content="width=device-width, initial-scale=1.0" name="viewport">
		
		<script type="text/javascript" src="{?$translateJsFileName?}"></script>
		
		<script type="text/javascript">
			{?php?}
				foreach ($attributes_array as $key => $value) {
					echo "document.documentElement.setAttribute(".Zend_Json::encode($key).", ".Zend_Json::encode($value).");".PHP_EOL."\t\t\t";
				}
				echo PHP_EOL;
				echo "window.eLS_translations = ".Zend_Json::encode(array(
					'alert'   => array(
						'title' => _('Информация'),
						'ok' => _('OK')
					),
					'confirm' => array(
						'title' => _('Подтверждение действия'),
						'ok' => _('Да'),
						'cancel' => _('Нет')
					)
				));
			{?/php?}
		</script>

		<link href="{?$root_url?}/css/jquery-ui/jquery-ui-1.8.21.custom.css" rel="stylesheet">
		<link href="{?$root_url?}/css/common.css" rel="stylesheet">
		<link href="{?$root_url?}/css/content-modules/breadcrumbs.css" rel="stylesheet">
		<link href="{?$root_url?}/css/content-modules/roleswitcher.css" rel="stylesheet">
		<link href="{?$root_url?}/css/jquery-ui/jquery.ui.selectmenu.css" rel="stylesheet">
        <!--placeholder:hmCssBootstrap-->
		<!--placeholder:headLink-->
		<!--placeholder:jQueryHeadLink-->
		<link href="{?$theme_url?}/css/theme.css" rel="stylesheet" id="theme-css-file">
		<!--[if lt IE 7]> <link href="{?$theme_url?}/css/ie6.css" rel="stylesheet"> <![endif]-->
		<!--placeholder:headStyle-->
        

        {?if $is_development?}
		<script src="{?$root_url?}/js/lib/modernizr-2.6.1.js"></script>
		<script src="{?$root_url?}/js/lib/json2.js"></script>
		<script src="{?$root_url?}/js/logger.js"></script>
		{?else?}
		<script src="{?$root_url?}/js/lib/modernizr-2.6.1.min.js"></script>
		<script src="{?$root_url?}/js/lib/json2.min.js"></script>
		<script src="{?$root_url?}/js/logger.min.js"></script>
		{?/if?}
		
		<!-- TODO: move to the bottom -->
		{?if $is_development?}
		<script src="{?$root_url?}/js/lib/jquery/jquery-1.7.2.js"></script>
		<script src="{?$root_url?}/js/lib/jquery/jquery.ba-resize.js"></script>
		<script type="text/javascript">jQuery.resize.throttleWindow = false;</script>
		<script src="{?$root_url?}/js/lib/jquery/jquery-ui-1.8.21.custom.js"></script>
		<script src="{?$root_url?}/js/lib/underscore-1.3.3.js"></script>
		<script src="{?$root_url?}/js/lib/jquery/jquery.ui.selectmenu.js"></script>
		<script src="{?$root_url?}/js/lib/datastorage-0.6.js"></script>
		{?else?}
		<script src="{?$root_url?}/js/lib/jquery/jquery-1.7.2.min.js"></script>
		<script src="{?$root_url?}/js/lib/jquery/jquery.ba-resize.min.js"></script>
		<script type="text/javascript">jQuery.resize.throttleWindow = false;</script>
		<script src="{?$root_url?}/js/lib/jquery/jquery-ui-1.8.21.custom.min.js"></script>
		<script src="{?$root_url?}/js/lib/underscore-1.3.3.min.js"></script>
		<script src="{?$root_url?}/js/lib/jquery/jquery.ui.selectmenu.min.js"></script>
		<script src="{?$root_url?}/js/lib/datastorage-0.6.min.js"></script>
		{?/if?}
		<script src="{?$root_url?}/js/lib/polyfills/placeholder.js"></script>

        <!--placeholder:hmJsBootstrap-->

		<script src="{?$root_url?}/js/common.js" charset="UTF-8"></script>
		<!--placeholder:headScript-->
		<script src="{?$theme_url?}/js/script.js" charset="UTF-8"></script>
		<!--placeholder:jQueryHeadScript-->
		<!-- END TODO -->
	</head>
	<body{?if $cls?} class="{?$cls?}"{?/if?}>
		{?$container?}
		<!--[if lt IE 7]>
		<script src="{?$root_url?}/js/lib/dd_belatedpng.js"></script>
		<script src="{?$root_url?}/js/lib/jquery/jquery.bgiframe.min.js"></script>
		<![endif]-->
		<!--
		<script>window.DD_belatedPNG && DD_belatedPNG.fix("img"); // Fix any <img> or .png_bg bg-images. Also, please read goo.gl/mZiyb </script>
		-->

		<!--placeholder:inlineScript-->
		<!--placeholder:jQueryInlineScript-->
		{? if $this->children.message->enabled()?}
		{?$this->children.message->display()?}
		{?/if?}
		
		
		<script type="text/javascript">
			var _userway_config = {
				// position below will override position set via widget
				 position: 3,
				// uncomment the following line to override color set via widget 
				//color: '#053e67', 
				account: 'DvBJWf8YzU'
			};
		</script>
		<script src="{?$root_url?}/js/userway/widget.js"></script>
		<?--<script type="text/javascript" src="https://cdn.userway.org/widget.js" async=""></script>-->
	</body>
</html>
