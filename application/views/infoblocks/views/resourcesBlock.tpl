<?php if (empty($this->totalResources)) :?>
    <div class="resources-control-block">
    	<div class="resources-control">
    		<div class="infoblock-resourcesBlock-input">
    			<div><?php echo _('Классификация');?>:</div>
    			<select id="resources-select-classifier" name="classifier">
    				<?foreach ($this->classifiers as $key => $value):?>
    				<option value="<?php echo $key?>" <? echo ($this->session->classifier == $key) ? 'selected' : ''; ?>><?php echo (strlen($value) > 50) ? substr($value, 0, 50) . '...' : $value; ?></option>
    				<?endforeach;?>
    			</select>
    		</div>
    	</div>
    	<div class="resources-control">
    		<div class="infoblock-resourcesBlock-dates">
    		    <div><?php echo _('Дата создания');?>:</div>
                <p><?php echo sprintf(_('с %s по %s'), $this->DatePicker('from', $this->session->from, array('showOn' => 'button','onSelect' => new Zend_Json_Expr('function() {loadData(this)}'))), $this->DatePicker('to', $this->session->to, array('showOn' => 'button', 'onSelect' => new Zend_Json_Expr('function() {loadData(this)}'))))?></p>
    		</div>
    	</div>

    	<?php
    	$export_url = $this->url(array(
    	    'module' => 'infoblock',
    	    'controller' => 'resources',
    	    'action' => 'get-data',
    	    'format' => 'csv',
    	));
    	$id = $this->id('button');
    	?>
    </div>
    <div class="resources-control-block-right">
    	<a title="<? echo _('Экспортировать данные в .csv')?>" href="<?php echo $export_url; ?>" target="_blank" class="ui-button export-button" id="<?php echo $id; ?>"><span class="button-icon"></span></a>
    </div>
    <?php $this->inlineScript()->captureStart(); ?>
    $(function(){

        $('#<?php echo $id; ?>').button({text: false});

    	$('#resourcesBlock select').change(function(){
    	    loadData(this);
    	});

    	$('#resourcesBlock input.hasDatepicker').change(function(){
    	    loadData(this);
    	});

    });

    function loadData(obj) {
		result = $.ajax({
			url:		'infoblock/resources/get-data/format/xml',
			type:		'POST',
			data:		{
				key:	$(obj).attr('name'),
				value:	$(obj).val()
			},
			dataType: 	'html',
			success: 	function(data) {
				if (data) {
					resourcesChart = document.getElementById('resources-chart');
					resourcesChart.setData(data);
				}
			}
		});
    }

    <?php $this->inlineScript()->captureEnd(); ?>
    <div style="clear: both"></div>
    <? echo $this->chart('resources');?>
<?php else:?>
<div class="resources-empty-message">
    <?php echo sprintf(_('Всего ресурсов в базе: %s'), $this->totalResources)?>
</div>
<div class="resources-empty-message">
    <?php echo _('Классификаторы ресурсов не созданы');?>
</div>

<?php endif;?>