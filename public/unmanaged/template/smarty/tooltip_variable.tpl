<span style="visibility: hidden" id="tooltip_{?$target?}"><img src='{?$img?}'  class = 'tooltip-link' tooltip-url = '{?$url?}' /></span>
<script language="JavaScript">
$P('{?$target?}').observe('change', function(event) {
	var arr = new Array('{?$values?}');
	$P('tooltip_{?$target?}').style.visibility = (arr.indexOf($P('{?$target?}').value) != -1) ? '' : 'hidden';
});
</script>