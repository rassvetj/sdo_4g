<html>
<head>
<title>{?$smarty.const.APPLICATION_TITLE?}</title>
{?if $block_content_copy?}
<script src="{?$sitepath?}js/disableclick.js" language="JavaScript" type="text/javascript"></script>
{?/if?}
<script type="text/javascript" src="{?$sitepath?}js/jquery.js"></script>
<script type="text/javascript">
<!--
var eLearning_server_metadata = {
    version_string: "3000",
    revision: 23423,
    course_options: {
        use_internal_navigation: {?$use_external_navigation?},
        block_content_copy: {?$block_content_copy?},
        metadata_page: '{?$sitepath?}teachers/{?$strParamsLink?}{?$strParamsMain?}',
        glossary_url: '{?$sitepath?}glossary_get.php?cid={?$item.cid?}'
    },
    permission: "{?$smarty.session.s.perm?}",
    coursexml: "{?$sitepath?}COURSES/course{?$item.cid?}/course.xml"    
}
//-->
</script>
<script type="text/javascript">
<!--
  applyGlossary = function()
  {
    return;
  }
//-->
</script>
</head>
<frameset rows="50,*" frameborder="0" border="0" framespacing="0" name="superMainFrameset" id="superMainFrameset">
  <frame src="{?$sitepath?}course_structure_top.php?cid={?$item.cid?}&show_material" id="topFrame" name="topFrame" noresize="noresize" scrolling="no">
  <frame onload="applyGlossary();" src="{?$sitepath?}lib_get.php?bid={?$item.module?}&cid={?$item.cid?}&oid={?$item.oid?}{?if $item.vol2?}&run={?$item.vol2?}{?/if?}" name="mainFrame" id="mainFrame" {?if $condition?} onload="applyGlossary(); disableClickInFrames(this.id);"{?/if?} scrolling="auto">
</frameset>
<noframes>
    <body>
    </body>
</noframes>
</html>