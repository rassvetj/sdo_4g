<settings> 
  <data_type>xml</data_type> 
  <font>Tahoma</font>   
  <text_size>11</text_size>
  <depth>0</depth>
  <angle>30</angle>
  <thousands_separator>,</thousands_separator>
  
  <column>
    <width>96</width>     
    <spacing>0</spacing> 
    <grow_time>1</grow_time>

    <grow_effect>elastic</grow_effect>    
    <balloon_text>                                                    
      <![CDATA[{value}]]>
    </balloon_text>    
  </column>

  <plot_area>
    <margins>
      <top>40</top>
      <left>60</left>
      <bottom>40</bottom>
      <right>40</right>

    </margins>
  </plot_area>
  
  <grid>                 
    <category>                                                                
      <alpha>10</alpha>  
      <dashed>true</dashed>
    </category>
    <value>                      
      <alpha>10</alpha>    
      <dashed>true</dashed>

    </value>
  </grid>
  
  <values>                 
    <category>             
      <color>999999</color>
    </category>
    <value>                    
      <min>0</min>             
    </value>
  </values>

  
  <axes>                       
    <category>                 
      <color>E7E7E7</color>    
      <width>1</width>         
    </category>
    <value>                    
      <color>#E7E7E7</color>       
      <width>1</width>         
    </value>
  </axes>  
  
  <balloon>                    
    <text_color>000000</text_color>
	<color>#D4B922</color>
    <corner_radius>4</corner_radius>
    <border_width>3</border_width>
    <border_alpha>50</border_alpha>
    <border_color>#000000</border_color>
    <alpha>80</alpha>
  </balloon>

    
  <labels>                         
    <label>
      <x>0</x>
      <y>7</y>
      <text_color>000000</text_color>
      <text_size>13</text_size>
      <align>center</align>
    </label>
  </labels>
  
  <legend>
    <enabled>0</enabled>
    <align></align>
    <border_alpha>100</border_alpha>

    <border_color>E7E7E7</border_color>
    <margins>5</margins>
  </legend>

  <graphs>
    <graph gid="activity-times">                          
      <color>#4B8C3E</color>                      
      <line_width>2</line_width>                  
      <balloon_text>                                                    
	      <![CDATA[<?php echo _('Среднее время в системе, час.');?>: {value}]]>
      </balloon_text>    
    </graph>      
    <graph gid="activity-times-single">                          
      <color>#4B8C3E</color>                      
      <line_width>2</line_width>                  
      <balloon_text>                                                    
	      <![CDATA[<?php echo _('Время в системе, час.');?>: {value}]]>
      </balloon_text>    
    </graph>      
    <graph gid="activity-sessions">                          
      <color>#C48DAB</color>                      
      <line_width>2</line_width>                  
      <balloon_text>                                                    
	      <![CDATA[<?php echo _('Среднее количество сессий');?>: {value}]]>
      </balloon_text>    
    </graph>      
    <graph gid="activity-sessions-single">  
      <color>#C48DAB</color>                      
      <line_width>2</line_width>                  
      <balloon_text>                                                    
	      <![CDATA[<?php echo _('Количество сессий');?>: {value}]]>
      </balloon_text>    
    </graph>      
  </graphs>

  <strings>
	  <no_data><?php echo _('Нет данных для отображения');?></no_data>
  </strings>

  </settings>