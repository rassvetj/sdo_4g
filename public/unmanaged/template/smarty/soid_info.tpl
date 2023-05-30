<div style="padding:8px">
{?if $this->attributes.soid?}
{?if $this->attributes.mid && $this->attributes.MID?}
    <table border=0 cellpadding=10 cellspacing=0 class="card-person">
    <tr>
        <td valign=top>{?$this->attributes.photo?}</td>
        <td width="100%" valign="top">
        
        <span class="lastname">{?$this->attributes.LastName|escape?}</span><br>
        <span class="name">{?$this->attributes.FirstName|escape?}&nbsp;{?$this->attributes.Patronymic|escape?}</span>
        <br/><br/>

            <table cellspacing="0" cellpadding="1" class="card-person-info">        
            {?if $this->attributes.orgunit.name?}
                <tr><td><b>{?t?}Подразделение:{?/t?}&nbsp;</b></td><td>{?$this->attributes.orgunit.name|escape?}</td></tr>
            {?/if?}
            {?if $this->attributes.name?}
                <tr><td><b>{?t?}Должность:{?/t?}</b></td><td><span class="position">{?$this->attributes.name|escape?}</span></td></tr>
            {?/if?}
            
            {?if $this->attributes.EMail ?}
                <tr>
                    <td><b>E-mail:</b> </td>
                    <td><a href="mailto:{?$this->attributes.EMail|escape?}">{?$this->attributes.EMail|escape?}</a></td>
                </tr>
            {?/if?}
            
            {?foreach from=$this->attributes.metadata item=i key=k?}
                <tr>
                    <td nowrap><b>{?if !empty($k)?}{?$k?}:{?/if?}</b> </td>
                    <td>{?$i?}</td>
                </tr>
            {?/foreach?}
            </table>
            
        </td>
    </tr>
    </table>    
{?else?}
    <table border=0 cellpadding=10 cellspacing=0 class="card-person">
    <tr>
        <td width="100%" valign="top">
            <span class="lastname">{?$this->attributes.name|escape?}</span>
            <br />
            {?if $this->attributes.info?}
            <br/>
            <table cellspacing="0" cellpadding="1" class="card-person-info">        
                <tr><td valign="top"><b>{?t?}Описание:{?/t?}&nbsp;</b></td><td>{?$this->attributes.info|escape?}</td></tr>
            </table>
            {?/if?}
        </td>
    </tr>
    </table>
{?/if?}
{?else?}
<p>{?t?}Данного элемента организации не существует!{?/t?}
{?/if?}
</div>