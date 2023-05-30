        <style type="text/css">
            @import '{?$sitepath?}js/timeline_materials.css';
        </style>
        
        <script type="text/javascript" src="{?$sitepath?}js/domdrag_materials.js"></script>
		<script type="text/javascript" src="{?$sitepath?}js/wz_jsgraphics_packed.js"></script>
        <script type="text/javascript" src="{?$sitepath?}js/timeline_materials.js"></script>
        
        <script type="text/javascript">
        $(window).ready(function(){
        self.t_absolute = new timeline()
        
        t_absolute.setStartDate('{?$start?}')
        t_absolute.setEndDate('{?$stop?}')
        
        //t_absolute.addItem('Элемент 1', '1', '#717897', 'Jun 01, 2007 00:00:00', 'Jun 10, 2007 00:00:00',30)
        
        {?if $modules?}
        //t_absolute.addCondition('2','{?t?}Процент выполненного{?/t?}','images/icons/i_cond02.gif')
        {?foreach from=$modules item=module?}
        t_absolute.addItem('{?$module.Title|escape?}','{?$module.CID?}','#717897','{?$module.start_date?}','{?$module.stop_date?}','{?$module.progress?}'
            {?if $conditions[$module.CID]?}
            , {operation: 'AND', conditions:[
            {?foreach name=conditions from=$conditions[$module.CID] item=condition?}
            {?if !$smarty.foreach.conditions.first?},{?/if?}
            {id: '1', value: '!', linkwith: '{?$condition.with?}'}
            {?/foreach?}
            ]}
            {?else?}
            ,''
            {?/if?}
            ,'{?$module.developers?}'
            ,"<a target='_blank' href='{?$sitepath?}abitur.php4?c={?$module.CID?}'>{?$module.students_num?}</a>"
            ,"<a target='_blank' href='{?$sitepath?}abitur.php4?t=1&c={?$module.CID?}'>{?$module.claimants_num?}</a>"
        );
        {?/foreach?}
        {?/if?}        
        t_absolute.showIn('container_absolute')        
        }        
        )
        </script> 
        Масштаб:
        <div id="container_absolute">
		</div>
        <br /><br /><br />
        <table border=0 cellpadding=0 cellspacing=0 class="auto-hscroll"><tr><td>
        <div class='button ok'><a href='javascript:void(0);' onclick="t_absolute.sendTo('{?$sitepath?}courses_base_timeline.php','{?$sitepath?}courses_base_timeline.php?msg=true'); return eLS.utils.form.submit(this);">ok</a></div><input type='submit' name='ok' value='ok' class='submit' style='display: none;' /><div class='clear-both'></div>
        </td></tr></table>
