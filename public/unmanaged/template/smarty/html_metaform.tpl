<form action="" method="POST">
    <label for="t">Anything</label>:
    <input type="text" name="test" id="t" meta:validator="filled"><br>
    Select: 
    <select name="sel">
      <option value="a">aaa</option>
      <option value="b">bbb</option>
    </select><br>
    {?$okbutton?}
<form>

<script type="text/javascript">
<!--
{?foreach from=$metaFormErrors item=e?}
var e = document.getElementById('{?$e.meta.id?}') || document.getElementsByName('{?$e.name?}')[0];
    if (e) {
        e.style.border = '2px solid red';
    }
{?/foreach?}
//-->
</script>

