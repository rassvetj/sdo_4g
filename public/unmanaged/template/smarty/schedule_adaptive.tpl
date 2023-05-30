        <style type="text/css">
            @import 'styles/timeline.css';
        </style>
        
        <script type="text/javascript" src="js/domdrag.js"></script>
        <script type="text/javascript" src="js/timeline.js.php"></script>
        
        <script type="text/javascript">
					$(window).ready(function(){
				{?foreach from=$timelines key=type item=timeline?}
	            	self.t_{?$type?} = new timeline()
	                
	            	{?if $type eq $smarty.const.SCHEDULE_TYPE_RELATIVE?}t_{?$type?}.showOffsets = true{?/if?}
	            	
	                t_{?$type?}.setStartDate('{? $timeline.date_begin ?}')
	                t_{?$type?}.setEndDate('{? $timeline.date_end ?}')
	                
	                t_{?$type?}.addButton('<a href="schedule.php4?c=modify&sheid=','"><img src="images/icons/edit.gif" border="0" /></a>')
	                t_{?$type?}.addButton('<a href="schedule.php4?CID={?$CID?}&rp=adaptive&c=delete&sheid=','" onClick="javascript:return(confirm(\'{?t?}Вы действительно желаете удалить это занятие?{?/t?}\'))"><img src="images/icons/delete.gif" border="0" /></a>')
	                
	                t_{?$type?}.addCondition('2','{?t?}Процент выполненного{?/t?}','images/icons/i_cond03.gif') // '1' reserved for lesson2lesson link
	                t_{?$type?}.addCondition('3','{?t?}Средний балл по курсу{?/t?}','images/icons/i_cond01.gif')
	                t_{?$type?}.addCondition('4','{?t?}Суммарный балл по курсу{?/t?}','images/icons/i_cond02.gif')
									
                    {?assign var='comma' value='false'?}
	                {?foreach from=$timeline.schedules item=schedule?}
	
	                t_{?$type?}.addItem('{? $schedule.title|strip ?}', '{? $schedule.SHEID ?}', '{?$timeline.color?}', '{? $schedule.date_begin ?}', '{? $schedule.date_end ?}'
                        , {operation: '{?if $schedule.cond_operation?}OR{?else?}AND{?/if?}', conditions:[
                        {?if $schedule.cond_sheid?}
                        {?foreach name=conditions from=$schedule.cond_sheid item=condition key=cond_key?}
                        {?assign var='comma' value='true'?}
                        {?if !$smarty.foreach.conditions.first?},{?/if?}
                        {id: '1', value: '{?$schedule.cond_mark[$cond_key]?}', linkwith: '{?$condition?}'}
                        {?/foreach?}
                        {?/if?}                        
                        {?if $schedule.cond_progress?}
                        {?if $comma == 'true'?},{?/if?}
                        {?assign var='comma' value='true'?}
                        {id: '2', value: '{?$schedule.cond_progress?}'}
                        {?/if?}
                        {?if $schedule.cond_avgbal?}
                        {?if $comma == 'true'?},{?/if?}
                        {?assign var='comma' value='true'?}
                        {id: '3', value: '{?$schedule.cond_avgbal?}'}
                        {?/if?}
                        {?if $schedule.cond_sumbal?}
                        {?if $comma == 'true'?},{?/if?}
                        {?assign var='comma' value='true'?}
                        {id: '4', value: '{?$schedule.cond_sumbal?}'}
                        {?/if?}
                        ]}
                        )
	                {?/foreach?}
	                
	                t_{?$type?}.showIn('container_{?$type?}')
	            {?/foreach?}       })
        </script>