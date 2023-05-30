
<div class="request-form" >
	<div style="font-size: 14px; font-weight: bold; position: relative; margin-top: -20px; margin-bottom: -10px;">
		<span style="color:#DA3F26;"><?=_('Прежде чем задавать вопрос, смотрите');?></span>		
		<br>
		<span><a style="position: relative; color: #5ecff5; top: 0px;" href="/htmlpage/index/view/htmlpage_id/34/" target="_blank"><?=_('Ответы на часто задаваемые вопросы');?></a></span>
	</div>
	<br>
    <a style="position: relative; float: right;" href="http://disk.yandex.ru" target="_blank"><?=_('Используйте Яндекс.Диск для скриншотов')?>
    <!--иконка-->
    <svg height="17px" width="30px" viewBox="0 7 40 27" icon="services/disk_32" version="1.1" xmlns="http://www.w3.org/2000/svg">
        <path d="M0.969,25.61c-0.305,0.325-0.533,0.604-0.697,0.827C0.458,26.187,0.696,25.908,0.969,25.61z M38.719,16.171
              c0.106,0.05,0.214,0.099,0.302,0.15C38.954,16.282,38.847,16.229,38.719,16.171z M18.304,17.051
              c11.268-3.038,17.316-1.958,19.66-1.173c-2.577-0.869-7.624-1.57-10.212-3.082c-2.474-1.445-5.105-5.819-11.66-4.052
              c-6.554,1.767-6.57,6.723-8.085,9.376c-1.241,2.176-5.085,5.357-7.038,7.489C2.824,23.63,7.545,19.953,18.304,17.051z
              M21.707,10.61c0.616-0.166,1.587,0.03,2.351,0.566c1.085,0.761,1.411,1.919,0.231,2.237c-1.181,0.318-2.692-0.519-3.162-1.446
              C20.796,11.313,21.091,10.775,21.707,10.61z M16.881,11.44c1.01-0.271,2.012,0.199,2.24,1.053
              c0.227,0.852-0.407,1.765-1.417,2.036c-1.01,0.272-2.012-0.198-2.24-1.052C15.237,12.625,15.871,11.713,16.881,11.44z
              M10.535,14.821c0.396-0.849,1.139-1.506,1.754-1.673c0.615-0.166,1.139,0.151,1.177,0.883c0.054,1.039-0.839,2.524-2.02,2.843
              C10.267,17.192,9.974,16.026,10.535,14.821z M39.944,19.243c-0.795-3.005-10.085-2.825-20.831,0.089
              C8.369,22.246,0.234,26.791,1.031,29.795c0.538,2.033,5.125,3.184,11.36,2.815c2.606,0.219,6.097-0.157,9.82-1.161
              c3.669-0.989,6.843-2.392,8.99-3.867C36.962,24.713,40.492,21.312,39.944,19.243z M21.834,30.032
              c-4.674,1.26-8.645,1.603-8.959,0.421c-0.315-1.181,3.233-3.107,7.908-4.367c4.674-1.261,8.705-1.375,9.02-0.194
              S26.508,28.772,21.834,30.032z" fill="#fff">
        </path>
    </svg>
    </a>
    <form id="request-form" >
        <div>
            <label for="theme"><?=_('Тема')?><span class="request-form-required">&nbsp;*</span></label>
        </div>
        <div>
            <input type="text" name="theme" id="theme">
        </div>
        <div>
            <label for="problem_description"><?=_('Описание проблемы')?> <span><?=_('как работает сейчас')?></span></label>
        </div>
        <div>
            <textarea name="problem_description" id="problem_description" rows="3"></textarea>
        </div>
        <div>
            <label for="wanted_result"><?=_('Ожидаемый результат')?> <span><?=_('как должно работать')?></span></label>
        </div>
        <div>
            <textarea name="wanted_result" id="wanted_result" rows="3"></textarea>
        </div>
        <div>
            <input type="button" id="submit-request-form" value="<?=_('Отправить')?>" onClick="$(this).hide(); $('#techsupport-process').show();">
			<div style="display:none; color: #5ecff5; float: left; padding-top: 3px;" id="techsupport-process">Отправка...</div>			
            <input type="button" id="cancel-request-form" value="<?=_('Закрыть')?>">
        </div>
    </form>
</div>
<div id="request-result"></div>