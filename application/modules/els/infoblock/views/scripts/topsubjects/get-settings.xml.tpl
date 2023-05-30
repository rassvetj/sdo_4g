<settings> 
  <font>Tahoma</font>                  
  <preloader_on_reload>false</preloader_on_reload>
  
  <pie>
    <inner_radius>0</inner_radius>    
    <height>0</height>                
    <angle>0</angle>
    <gradient>none</gradient>                  
    <colors>#C24E5F, #CF7725, #D4B922, #949E08, #4B8C3E, #003F7E, #C759D2</colors>    
  </pie>

  
  <animation>
    <start_time>0.5</start_time>         
    <start_effect>strong</start_effect>
    <pull_out_time>1.5</pull_out_time> 
    <pull_out_effect>Bounce</pull_out_effect>
    <pull_out_only_one>true</pull_out_only_one>       
    
  </animation>
  
  <data_labels>

    <show>
       <![CDATA[{title}: {percents}%]]>        
    </show>
    <line_color>#000000</line_color>           
    <line_alpha>15</line_alpha>                
    <hide_labels_percent>3</hide_labels_percent>                                       
  </data_labels>
  
  <balloon>                                     
    <show>
       <![CDATA[<?php echo _('{title}');?>: {percents}% (<?php echo _('заявок');?>: {value})]]>  
    </show>

  </balloon>
    
  <legend>                     
    <enabled>false</enabled>   
  </legend>    
 
  <labels>                     
    <label>
      <x>0</x>                 
      <y>40</y>                
      <align>center</align>      
      <text_size>12</text_size> 
    </label>
  </labels>
  
  <group>
		<percent>5</percent>
		<title>Прочие</title>
		<color>#757575</color>    	  
  </group>

  <strings>
	  <no_data><?php echo _('Нет данных для отображения');?></no_data>
  </strings>

</settings>