<?php
        require_once('desc.inc.'.$filesext);
        require_once('param.inc.'.$filesext);
        require_once('flag.inc.'.$filesext);

        function showtable($value,$el)
        {
        reset($value);

//        editmode();

        global $sort;


        if ($el)
         {

        $val=$value[$el];
        
        $key=$el;


        if ((!empty($val['type'])) && (!empty($val['name'])) && (!empty($val['coment'])))
          {
                switch ($val['type']) 
                                  {
                                   case "class"       : showclass($key,$value);showprevclass($key,$value); break;
                                   case "link"       : showclass($key,$value);showprevlink($key,$value); break;
                                   case "tag"       : showclass($key,$value);showprevtag($key,$value); break;

                                  } ;
          };

        };
                        
        };



        function showclass($key,$value)
        {
        
        global $all;

        
        $flag=flagsup() ;
        
        echo "<tr><td><FIELDSET><table width='100%'>\n";
        
        showshapka();
        
        $val=$value[$key];

        while (list($param) = each($value[$key]))        
         {

                showparamname($param,$val[$param]);
                $flag=flagswitch($key,$param,$val[$param],$flag);
         }
               
         if ($all) 
          {
            while (list($param) = each ($flag))
                {
                 if (!$flag[$param])
                  {
                   $val[$param]="";
                   showparamname($param,$val[$param]);
                   $flag=flagswitch($key,$param,$val[$param],$flag);
                  }
            
                } 
                                
           }

        echo "</table>\n</FIELDSET></td>\n</tr>\n";

        }
        
    
        function changesize($key,$param,$value)
        {
        ?>
        <td>
        <input name='valik[<?php echo $key."][".$param;?>]' id='<?php echo $param.$key;?>' value='<?php echo $value;?>' onclick="ChangeS('<?php echo $param.$key; ?>','<?php echo "prev".$key; ?>',value,'<?php echo $param; ?>','ok')"  onchange="ChangeS('<?php echo $param.$key; ?>','<?php echo "prev".$key; ?>',value,'<?php echo $param; ?>','ok');">
        <input name='error[<?php echo $key."][".$param;?>]' id='error<?php echo $param.$key; ?>' value='Ok' readonly="1">
        </td>
        <?php
        return(1);
        };

        function showfontcolor($key,$param,$value)
        {
        ?>
        <td>
        <input name='valik[<?php echo $key."][".$param;?>]' id='<?php echo $param.$key; ?>' value='<?php echo $value; ?>' onclick="ChangeCol('<?php echo "prev".$key; ?>','<?php echo $param.$key; ?>',value,'<?php echo $param; ?>')" onchange="ChangeCol('<?php echo "prev".$key; ?>','<?php echo $param.$key; ?>',value,'<?php echo $param; ?>')">
        <input type=button value='> select <' onClick="SelCol('<?php echo "prev".$key; ?>','<?php echo $param.$key; ?>','<?php echo $value; ?>','<?php echo $param; ?>')">
        <input name='error[<?php echo $key."][".$param;?>]' id='error<?php echo $param.$key; ?>' value='Ok' readonly="1" style="background-color :<?php echo $value;?>">
        </td>
        <?php
        return(1);
        }

        function showfontfamily($key,$param,$value)
        {
        ?>
        <td>
         <select name='valik[<?php echo $key."][".$param;?>]' id='<?php echo $param.$key; ?>' onclick="ChangeS('','<?php echo "prev".$key; ?>',value,'<?php echo $param; ?>','')">
                <option value='<?php echo $value; ?>' selected><?php echo $value; ?></option>
                <option value='Webdings'>Webdings</option>
                <option value='Wingdings'>Wingdings</option>
                <option value='Micra'>Micra</option>
                <option value='Arial, Helvetica, sans-serif'>Arial, Helvetica, sans-serif
                <option value='Times New Roman, Times, serif'>Times New Roman, Times, serif</option>
                <option value='Courier New, Courier, mono'>Courier New, Courier, mono</option>
                <option value='Georgia, Times New Roman, Times, serif'>Georgia, Times New Roman, Times, serif</option>
                <option value='Verdana, Arial, Helvetica, sans-serif'>Verdana, Arial, Helvetica, sans-serif</option>
                <option value='Geneva, Arial, Helvetica, san-serif'>Geneva, Arial, Helvetica, san-serif</option>
                <option value=""><?=_("Убрать этот параметр")?></option>
         </select>
        <input type='hidden' name='error[<?php echo $key."][".$param;?>]' id='error<?php echo $param.$key; ?>' value='Ok' readonly="1">
        </td>
        <?php
        return(1);
        }

        function showfontweight($key,$param,$value)
        {
        ?>
        <td>
         <select name='valik[<?php echo $key."][".$param;?>]' id="<?php echo $param.$key; ?>" onclick="ChangeS('','<?php echo "prev".$key; ?>',value,'<?php echo $param; ?>','')">
                <option value="<?php echo $value; ?>" selected><?php echo $value; ?></option>
                <option value="normal">normal</option>
                <option value="Bold">Bold</option>
                <option value="Bolder">Bolder</option>
                <option value="lighter">lighter</option>
                <option value=""><?=_("Убрать этот параметр")?></option>
         </select>
        <input type='hidden' name='error[<?php echo $key."][".$param;?>]' id='error<?php echo $param.$key; ?>' value='Ok' readonly="1">
        </td>
        <?php
        return(1);
        }

        function showcursor($key,$param,$value)
        {
        ?>
        <td>
         <select name='valik[<?php echo $key."][".$param;?>]' id="<?php echo $param.$key; ?>" onclick="ChangeS('','<?php echo "prev".$key; ?>',value,'<?php echo $param; ?>','')">
                <option value="<?php echo $value; ?>" selected><?php echo $value; ?></option>
                <option value="help">help</option>
                <option value="auto">auto</option>
                <option value="default">default</option>
                <option value="crosshair">crosshair</option>
                <option value="move">move</option>
                <option value=""><?=_("Убрать этот параметр")?></option>
         </select>
        <input type='hidden' name='error[<?php echo $key."][".$param;?>]' id='error<?php echo $param.$key; ?>' value='Ok' readonly="1">
        </td>
        <?php
        return(1);
        }

        function showborderstyle($key,$param,$value)
        {
        ?>
        <td>
         <select name='valik[<?php echo $key."][".$param;?>]' id="<?php echo $param.$key; ?>" onclick="ChangeS('','<?php echo "prev".$key; ?>',value,'<?php echo $param; ?>','')">
                <option value="<?php echo $value; ?>" selected><?php echo $value; ?></option>
                <option value="solid">solid</option>
                <option value="groove">groove</option>
                <option value="dotted">dotted</option>
                <option value="double">double</option>
                <option value="dashed">dashed</option>
                <option value="ridge">ridge</option>
                <option value=""><?=_("Убрать этот параметр")?></option>
         </select>
        <input type='hidden' name='error[<?php echo $key."][".$param;?>]' id='error<?php echo $param.$key; ?>' value='Ok' readonly="1">
        </td>
        <?php
        return(1);
        }

        function showtexttransform($key,$param,$value)
        {
        ?>
        <td>
         <select name='valik[<?php echo $key."][".$param;?>]' id="<?php echo $param.$key; ?>" onclick="ChangeS('','<?php echo "prev".$key; ?>',value,'<?php echo $param; ?>','')">
                <option value="<?php echo $value; ?>" selected><?php echo $value; ?></option>
                <option value="none">none</option>
                <option value="capitalize">capitalize</option>
                <option value="lowercase">lowercase</option>
                <option value="uppercase">uppercase</option>
                <option value=""><?=_("Убрать этот параметр")?></option>
         </select>
        <input type='hidden' name='error[<?php echo $key."][".$param;?>]' id='error<?php echo $param.$key; ?>' value='Ok' readonly="1">
        </td>
        <?php
        return(1);
        }


        function showwhitespace($key,$param,$value)
        {
        ?>
        <td>
         <select name='valik[<?php echo $key."][".$param;?>]' id="<?php echo $param.$key; ?>" onclick="ChangeS('','<?php echo "prev".$key; ?>',value,'<?php echo $param; ?>','')">
                <option value="<?php echo $value; ?>" selected><?php echo $value; ?></option>
                <option value="normal">normal</option>
                <option value="PRE">Pre</option>
                <option value="noWrap">noWrap</option>
                <option value=""><?=_("Убрать этот параметр")?></option>
         </select>
        <input type='hidden' name='error[<?php echo $key."][".$param;?>]' id='error<?php echo $param.$key; ?>' value='Ok' readonly="1">
        </td>
        <?php
        return(1);
        }

        function showtextalign($key,$param,$value)
        {
        ?>
        <td>
         <select name='valik[<?php echo $key."][".$param;?>]' id="<?php echo $param.$key; ?>" onclick="ChangeS('','<?php echo "prev".$key; ?>',value,'<?php echo $param; ?>','')">
                <option value="<?php echo $value; ?>" selected><?php echo $value; ?></option>
                <option value="left">left</option>
                <option value="right">right</option>
                <option value="center">center</option>
                <option value="justify">justify</option>
                <option value=""><?=_("Убрать этот параметр")?></option>
         </select>
        <input type='hidden' name='error[<?php echo $key."][".$param;?>]' id='error<?php echo $param.$key; ?>' value='Ok' readonly="1">
        </td>
        <?php
        return(1);
        }

   function changdecoration($key,$param,$value)
        {
        ?>
        <td>
         <select name='valik[<?php echo $key."][".$param;?>]' id="<?php echo $param.$key; ?>" onclick="ChangeS('','<?php echo "prev".$key; ?>',value,'<?php echo $param; ?>','')">
                <option value="<?php echo $value; ?>" selected><?php echo $value; ?></option>
                <option value="none">none</option>
                <option value="underline">underline</option>
                <option value=""><?=_("Убрать этот параметр")?></option>
         </select>
        <input type='hidden' name='error[<?php echo $key."][".$param;?>]' id='error<?php echo $param.$key; ?>' value='Ok' readonly="1">
        </td>
        <?php
        return(1);
        }

   function changedisplay($key,$param,$value)
        {
        ?>
        <td>
         <select name='valik[<?php echo $key."][".$param;?>]' id="<?php echo $param.$key; ?>" onclick="ChangeS('','<?php echo "prev".$key; ?>',value,'<?php echo $param; ?>','')">
                <option value="<?php echo $value; ?>" selected><?php echo $value; ?></option>
                <option value="none">none</option>
                <option value="inline">inline</option>
                <option value=""><?=_("Убрать этот параметр")?></option>
         </select>
        <input type='hidden' name='error[<?php echo $key."][".$param;?>]' id='error<?php echo $param.$key; ?>' value='Ok' readonly="1">
        </td>
        <?php
        return(1);
        }

        function showshapka()
        { ?>
         <tr align="center">
          <td width="25%" ><?=_("Описание")?></td>
          <td width="15%"><?=_("Сохраненное значение")?></td>
          <td width="60%"><?=_("Текущее значение")?></td>
         </tr>
        <?php 
        };

        function showlist($value)
        
        {
         reset($value);

         while (list($key,$val) = each($value))
           {
             if ((!empty($val['type'])) && (!empty($val['name'])) && (!empty($val['coment'])))
                {
                   $name=$val['coment']." (.".$val['name'].")";
                   ?>
                   <option value="<?php echo $key; ?>"><?php echo $name; ?></option>
                   <?php
                };
           };

        };
 
?>