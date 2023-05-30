{?foreach name=data from=$data->structure item=item?}
{?html_image file="images/spacer.gif" width=$item.level*10 height="1"?}{?if $item.mod_ref?}<a href='course{?$data->id?}/index_{?$item.mod_ref?}.html'>{?$item.title?}</a>{?else?}{?$item.title?}{?/if?}<br>
{?/foreach?}