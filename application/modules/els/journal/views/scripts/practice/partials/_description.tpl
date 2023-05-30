<style>
.item-rules {
	padding: 5px;
    font-size: 15px;    
}
.btn-rules {
	font-weight: bold;
    color: #0067a4;
	cursor: pointer;
}

</style>

<div class="item-rules" >
	<span  class="btn-rules">Информация</span>
	<div class="content-rules">
		<ol>
			<li>Чтобы изменить «явку», «формат присутствия», «оценку» или «заголовок колонки», необходимо нажать на соответствующий элемент.</li>
			<li><span style="font-weight:bold;">офф-лайн</span> - студент присутствовал в аудитории.</li>
			<li><span style="font-weight:bold;">он-лайн</span> - студент присутствовал удаленно.</li>
			<li><span style="font-weight:bold; color:#d66161; font-size: 18px;">*</span> - символ над элементом означает, что он был изменен. Для сохранения изменений нужно нажать кнопку "Сохранить".</li>
			
		</ol>
	</div>
</div>

<?php $this->inlineScript()->captureStart()?>	
$('.btn-rules').click(function(){			
	var content = $(this).closest('.item-rules').find('.content-rules');
	if(content.hasClass('hidden')){ content.removeClass('hidden'); }
	else                          { content.addClass('hidden');    }
	return false;
});
<?php $this->inlineScript()->captureEnd()?>