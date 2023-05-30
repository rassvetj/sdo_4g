{?capture name=container_capture_block assign=container?}
 {?php?}$this->assign('globals', $GLOBALS);{?/php?}
 {?if $globals.managed || (!$globals.managed  && $this->content)?}
		<div id="container" class="hm-container-blank"><div class="main" id="main">
			<div class="els-body els-body-blank">
				<!--[if lt IE 7]>
				<div class="ooops-this-is-ie6">
					<a href="http://twitter.com/Microsoft/status/43753653189885952" target="_blank"><img src="{?$root_url?}/images/mstweet.png" alt="MS Tweet"></a>
				</div>
				<![endif]-->
				<div class="els-content els-box">
					{?if $this->content?}
					{?$this->content?}
					{?/if?}
					<!--placeholder:content-->
				</div>
			</div>
		</div></div>
		{?/if?}
{?/capture?}
{?if $this->bodyClass?}
{?assign var='cls' value=$this->bodyClass?}
{?else?}
{?assign var='cls' value='document-blank'?}
{?/if?}
{?include file='_common.tpl' cls=$cls?}
<script>
$(function(){
	$('.els-body-blank').delegate('a', 'click', function() {
		// @todo: оптимизировать
		url = $(this).attr('href');
		parts = url.split('#');
		url = parts[0];
		parts = url.split('?');
		url = parts[0];
		if ((top != self) && top.resource_id) $(this).attr('href', url + '/activity_resource_id/' + top.resource_id);
		//alert($(this).attr('href'));
		return true;
	});
});
</script>
