<style>
#area-description {
	margin-right: 0px;
	max-width: 500px;
	width: 100%;
	margin-bottom: 15px;
}
</style>
<div id="area-description" class="<?=empty($this->description) ? 'hidden' : ''?>" >
	<?=$this->description?>
</div>