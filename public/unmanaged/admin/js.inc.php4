<?php
//        require_once('flag.inc.'.$filesext); 
        function showjs($value,$key) 
        {
  ?>
  <script language=JavaScript>

        function ResetF()
                {
                <?php
                if ($key!=0)
                {
                 $val = $value[$key];
                 global $all;

                 if (!$all)
                     {
                        if ((!empty($val['type'])) && (!empty($val['name'])) && (!empty($val['coment'])) && (($val['type']=='class')))
                         {
                           while (list($param) = each($value[$key]))        
                                {
                                 if (($param!='type') && ($param!='name') && ($param!='coment'))
                                        {
                                         echo "\n    ChangeS('".$param.$key."','prev".$key."','".$val[$param]."','".$param."','ok') ; \n";
                                        }
                                };
                         }
                     }
                      else
                     {
                      $flag=flagsup();
                  
                       while (list($param) = each ($flag))
                               {
                                if (($param!='type') && ($param!='name') && ($param!='coment'))
                                      {
                                       $val[$param]="";
                                       echo "\n    ChangeS('".$param.$key."','prev".$key."','".$val[$param]."','".$param."','ok') ; \n";
                                      }
                               } 
                     }               
                 }

                ?>
                }


        function ChangeCol(nprev,name,val,type)
        {
                  if (val) {cont='Ошибка'} else {cont='<?=_("Пусто")?>'};
                  document.forms['master'].elements['error'+name].value=cont;                                  
                  document.forms['master'].elements['error'+name].style['backgroundColor']='white';
                  if (document.forms['master'].elements['error'+name].style['backgroundColor']=val) {
                        cont='';
                        ChangeS(name,nprev,val,type,'ok');
                        }
                  document.forms['master'].elements['error'+name].value=cont;                  
                  
                
        }
        function SelCol(nprev,name,val,type) {
                var s = window.showModalDialog("colsel.html", val, "dialogHeight:500px;dialogWidth:650px;center:yes;resizable:yes;status:yes;help:yes;scroll:yes");
                if (s && s != '') {
                document.forms['master'].elements[name].value=s;
                ChangeCol(nprev,name,s,type);
        }
        }

        function ChangeS(pname,name,val,type,er)
        {
              if (er!='') {
                        cont='<?=_("Ошибка")?>'
                        document.forms['master'].elements['error'+pname].value=cont;
                      }
               window.document.all[name].style[type]=val;
              if (er!='') {
                        cont='Ok'
                        document.forms['master'].elements['error'+pname].value=cont;
                      }
                                    
              return(true)           
        }  

  

  </script>
  <?php 
  }
?>