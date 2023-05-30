{?foreach from=$posts item=post?}
            <table width="100%" border="0" cellspacing="0" class=main bgcolor="<?echo $bgcolor;?>">
                      <tr><th>{?$post.date1?} {?$post.date2?}, <a href="javascript:void(0);" onClick="wopen('userinfo.php?mid={?$post.mid?}','',600,425);">{?$post.name?}</a></th></tr>
                      <tr><td {?$post.bgcolor?}>{?$post.text?}<br><br></td></tr>
{?if $post.course?}
                      <tr><td {?$post.bgcolor?}>{?$post.course?}
                      </td></tr>
{?/if?}
            </table><br>
 {?/foreach?}
