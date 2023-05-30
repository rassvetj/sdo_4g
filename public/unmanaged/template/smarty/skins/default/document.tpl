{?php?}
$tplThis = $this->get_template_vars('this');
$this->assign('theme_url', $tplThis->root_url."/themes/".$tplThis->theme);
$this->assign('root_url', $tplThis->root_url);

if(isset($_POST['search_query'])) {
	$this->assign('search_query', htmlspecialchars($_POST['search_query']));
} else {
	if(preg_match('#/search_query/([^/]*)#i', $_SERVER['REQUEST_URI'], $match)) {
		$this->assign('search_query', htmlspecialchars($match[1]));
	}
}
$this->assign('pages', $_SESSION['s']['infopages_with_url']);
$objUser        = $this->_tpl_vars['this']->children['user_home']->objects['user'];
$objLangChooser = $this->_tpl_vars['this']->children['user_home']->objects['lang_chooser'];
$isAuthorized = (bool)$objUser && (bool)$objUser->isAuthorized();
//$hasUserBlock = $isAuthorized || ((bool)$objLangChooser && (bool)$objLangChooser->enabled() && count($objLangChooser->objects['lang_controller']->langs) > 1);
$hasUserBlock = true;
$this->assign('hasUserBlock', $hasUserBlock);
$this->assign('isAuthorized', $isAuthorized);
{?/php?}
{?capture name=container_capture_block assign=container?}

		<div id="container" class="{?if $isAuthorized?}is-authorized{?/if?} {?if $hasUserBlock?}has-user-block{?/if?}">
			<div id="header"><div class="block" id="logo">
				<div><div>
					<a href="{?$root_url?}" title="{?t?}Главная{?/t?}">
						<!--<img class="logo-left" alt="{?t?}На главную{?/t?}" src="{?$theme_url?}/images/logo.jpg">-->
						<!--<img class="logo-right" alt="{?t?}На главную{?/t?}" src="{?$theme_url?}/images/logo_right.jpg">-->
					</a>
				</div></div>
			</div>{?
				$this->displayChild('user_home')
			?}{?php?}
				if ($this->_tpl_vars['this']->children['user_home']->objects['user'] and $this->_tpl_vars['this']->children['user_home']->objects['user']->isAuthorized()) {
			{?/php?}{?if !$this->disable_search?}<div id="search-block">
				<h3>{?t?}Поиск по Базе знаний{?/t?}:</h3>
				<div class="search-form">
					<form action="{?$root_url?}/resource/search?page_id=unknown" method="POST">
						<input class="input-seach" placeholder="{?t?}поиск{?/t?}" name="search_query" type="text" value="{?$search_query?}">
						<!-- <input class="submit-search ui-button" type="submit" value="{?t?}Найти{?/t?}"> -->
						<button class="submit-search ui-button" value="{?t?}Найти{?/t?}"><span>{?t?}Найти{?/t?}</span></button>
					</form>
				</div>
				<a href="{?$root_url?}/resource/catalog">{?t?}Просмотр Базы знаний{?/t?}</a>
			</div>{?/if?}{?php?} } else { {?/php?}{?php?} } {?/php?}</div>
			<div class="main" id="main">
				<div class="tab-bar">
					<div class="wrapper">
						<ul>{?if $this->children.menu_main->enabled()?}{?$this->displayChild('menu_main')?}{?else?}<li class="home"><a href="{?$root_url?}"><span>{?$smarty.const.APPLICATION_TITLE?}</span></a></li><li class="ui-tabs-selected ui-state-active"><a href="{?$root_url?}"><span>{?$smarty.const.APPLICATION_TITLE?}</span></a></li>{?/if?}</ul>
					</div>
					<div class="clocks"></div>
				</div>
				<div class="els-body">
					<!--[if lt IE 7]>
					<div class="ooops-this-is-ie6">
						<a href="http://twitter.com/Microsoft/status/43753653189885952" target="_blank"><img src="{?$root_url?}/images/mstweet.png" alt="MS Tweet"></a>
					</div>
					<![endif]-->
					<div class="els-box">
						{?if $this->children.menu_breadcrumbs->enabled()?}
						<div class="breadcrumbs">{?$this->displayChild('menu_breadcrumbs')?} <!--placeholder:breadcrumbs--></div>
						{?/if?}
						{?if ($this->header || $this->subheader)?}
						<a class="help-activator" data-help-url="{?$root_url?}/help/index/index" href="{?$root_url?}/help/index/index" title="{?t?}Помощь{?/t?}">{?t?}Помощь{?/t?}</a>
						<hgroup>
							{?if $this->header?}<h1 id="page-title">{?$this->header?}</h1>{?/if?}
							{?if $this->subheader?}<h2 id="page-subtitle">{?$this->subheader?}</h2>{?/if?}
						</hgroup>
						{?/if?}
						<div id="error-box" class="error-box"></div>
					</div>
					<div class="els-content els-box">
						{?if $this->content?}
						{?$this->content?}
						{?/if?}
						<!--placeholder:content-->
						<!--placeholder:pageSupport-->
					</div>
				</div>
			</div>
            <div class="clearfix"></div>
			<div id="footer">
				<div class="wrapper">
					<div id="credits">
						{?if $pages?}
						<div class="pages">{?foreach from=$pages item=page key=id?}
                        {?if $page.url?}
                            <div><a href="{?$root_url?}/htmlpage/index/view/htmlpage_id/{?$id?}/?page_id=m0000" target="_blank">{?$page.name?}</a></div>
                        {?else?}
						    <div><a href="{?$root_url?}/htmlpage/index/view/htmlpage_id/{?$id?}/?page_id=m0000">{?$page.name?}</a></div>
                        {?/if?}
						{?/foreach?}
						</div>{?/if?}
						<div class="copyright">
    						<p>&copy; HyperMethod 2004-{?$smarty.now|date_format:"%Y"?}</p>
    						<p><a href="http://hypermethod.ru" title="HyperMethod" target="_blank">www.hypermethod.ru</a></p>
		    			</div>
		    			<div class="clearfix"></div>
					</div>
				</div>
			</div>
		</div>
		<!-- end of #container -->			
		{?php?}
			/* Если это не тестовый сайт. Потом перевести в функцию. */
			if($_SERVER['LOCAL_ADDR'] != '192.168.132.220' && $_SERVER['SERVER_ADDR'] != '192.168.132.220') {
				echo '
					<!-- Yandex.Metrika counter --><script type="text/javascript">(function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter30466342 = new Ya.Metrika({id:30466342, webvisor:true, clickmap:true, trackLinks:true, accurateTrackBounce:true}); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="//mc.yandex.ru/watch/30466342" style="position:absolute; left:-9999px;" alt="" /></div></noscript><!-- /Yandex.Metrika counter -->
				';
			}
		{?/php?}
{?/capture?}
{?if $this->bodyClass?}
{?assign var='cls' value=$this->bodyClass?}
{?else?}
{?assign var='cls' value='document'?}
{?/if?}
{?include file='_common.tpl' cls=$cls?}