<div class="kbase_search">
	<div class="kbase_search_l">
        <div class="kbase_form_wrap">
        	<div class="kbase_search_form">
        		<form action="<?php echo $this->serverUrl('/resource/search?page_id=unknown'); ?>" method="POST">
        			<div class="search_form_l"><input class="input-seach" name="search_query" type="text" value="<?php echo $this->query;?>"></div>
        			<div class="search_form_r"><input class="submit-search ui-button ui-widget ui-state-default ui-corner-all" type="submit" value="<?php echo _('Найти') ?>" role="button" aria-disabled="false"></div>
        			<div class="search_form_adv clearfix"></div>
        			<div class="search_form_adv"><a href="<?php echo $this->url(array('module' => 'kbase', 'controller' => 'index', 'action' => 'advanced-search', 'page' => null, 'search_query' => null));?>"><?php echo _('Расширенный поиск');?></a></div>
        		</form>
        	</div>
        </div>	    
	</div>
	<div class="kbase_search_r"></div>
</div>