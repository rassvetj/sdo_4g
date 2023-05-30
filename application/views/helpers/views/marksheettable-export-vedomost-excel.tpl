<?php 
		
		
		$expandedRowCount = 26 + count($this->persons); # обязательный параметр, определяемый кол-во строк. Если не совпадет с реальным кол-м отрисованных строк - файл не сформируется.
		
		$form_control 		= $this->additional['exam_type_name']; # взять из сессии Форма обучения
		$faculty	  		= $this->additional['faculty']; # взять из сессии Факультет
		$years 				= $this->additional['years']; # взять из сессии Учебный год /
		$course				= !empty($this->additional['course']) ? $this->additional['course'] : ceil($this->additional['semester']/2); # курс, число
		$date_issue 		= $this->additional['date_issue']; # Дата формирвания ведомости.
		$marksheet_number 	= $this->additional['marksheet_external_id']; # Номер ведомости.
		$dean 				= $this->additional['dean']; # Декан
		$tutor 				= $this->additional['tutor']; # Тьютор
		
		#$tutors 			= array();		
		$allGroups 			= array();
		$isModuleSubject 	= false;
		$group_marks		= array(); # кол-во каждой из оценок для статистики в конце таблицы
		
		foreach($this->persons as $key => $person){
			$groups = $person->studyGroups;
			if ($groups) {				
				foreach ($groups as $group) {
					$allGroups[$group['name']] = $group['name'];					
				}				
			}
			if($isModuleSubject === false){
				$isModuleSubject = (isset($this->scores[$key."_total"]['integrateMediumRating'])) ? true : false;
			}
		}
		
		/*
		if(!empty($this->additional['tutors'])){
			foreach($this->additional['tutors'] as $t){
				$tutors[] = $t->LastName.' '.$t->FirstName.' '.$t->Patronymic;
			}			
		}
		*/		
?>
	
<?='<?xml version="1.0"?>'; ?>
<?='<?mso-application progid="Excel.Sheet"?>'; ?>

<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">  
  <Version>12.00</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">  
  <RefModeR1C1/>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="m36492384">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="m36492180">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="m36492220">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="m36492260">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s63">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
  </Style>
  <Style ss:ID="s65">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s67">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s68">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders/>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s75">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s76">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s77">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s78">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s79">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders/>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s81">
   <Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders/>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s82">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s84">
   <Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s85">
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s86">
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="9"
    ss:Color="#000000" ss:Bold="1" ss:Italic="1"/>
  </Style>
  <Style ss:ID="s88">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="9"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s89">
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s90">
   <Borders>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s93">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s98">
   <Alignment ss:Vertical="Bottom"/>
  </Style>
  <Style ss:ID="s102">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s103">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s110">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Color="#000000"
    ss:Bold="1"/>
  </Style>
  <Style ss:ID="s111">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Color="#000000"
    ss:Bold="1"/>
  </Style>
  <Style ss:ID="s112">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:Rotate="90"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Color="#000000"
    ss:Bold="1"/>
  </Style>
  <Style ss:ID="s113">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:Rotate="90"
    ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Color="#000000"
    ss:Bold="1"/>
  </Style>
  <Style ss:ID="s116">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Color="#000000"
    ss:Bold="1"/>
   <Interior ss:Color="#B3B3B3" ss:Pattern="Solid"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Тестовая сессия от 21.01.2016 (">
  <Table ss:ExpandedColumnCount="9" ss:ExpandedRowCount="<?=$expandedRowCount;?>" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="0" ss:Width="31.5"/>
   <Column ss:AutoFitWidth="0" ss:Width="183"/>
   <Column ss:AutoFitWidth="0" ss:Width="38.25"/>
   <Column ss:AutoFitWidth="0" ss:Width="45"/>
   <Column ss:AutoFitWidth="0" ss:Width="54.75"/>
   <Column ss:AutoFitWidth="0" ss:Width="45.75"/>
   <Column ss:AutoFitWidth="0" ss:Width="50.25"/>
   <Column ss:AutoFitWidth="0" ss:Width="103.5"/>
   <Column ss:AutoFitWidth="0" ss:Width="104.25"/>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="8" ss:StyleID="s63"><Data ss:Type="String">ЧЕРНОВИК</Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="8" ss:StyleID="s63"><Data ss:Type="String"></Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="8" ss:StyleID="s63"><Data ss:Type="String"><?=$faculty?></Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="8" ss:StyleID="s65"><Data ss:Type="String">ЗАЧЕТНО-ЭКЗАМЕНАЦИОННАЯ ВЕДОМОСТЬ В № <?=$marksheet_number?></Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="8" ss:StyleID="s67">
		<ss:Data ss:Type="String" xmlns="http://www.w3.org/TR/REC-html40"><Font 
			html:Color="#000000">Форма обучения </Font><U><Font html:Color="#000000"><?=$form_control?></Font></U><Font 
			html:Color="#000000"> Группа </Font><U><Font html:Color="#000000"><?=implode(', ', $allGroups);?></Font></U>			
	   </ss:Data>
	</Cell>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="8" ss:StyleID="s67">
		<ss:Data ss:Type="String" xmlns="http://www.w3.org/TR/REC-html40"><Font
			html:Color="#000000"><?=$this->additional['semester'];?> семестр </Font><U><Font html:Color="#000000">(<?=(!empty($course))?$course:'        '?> курс)</Font></U><Font
			html:Color="#000000"> Учебный год </Font><U><Font html:Color="#000000"><?=$years?></Font></U>
		</ss:Data>
	</Cell>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="8" ss:StyleID="s67">
		<ss:Data ss:Type="String" xmlns="http://www.w3.org/TR/REC-html40"><Font
			html:Color="#000000">Дисциплина </Font><U><Font html:Color="#000000"><?=$this->additional['subjectName'];?></Font></U>
		</ss:Data>
	</Cell>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="8" ss:StyleID="s68"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40"><Font html:Color="#000000">Ф.И.О. экзаменаторов </Font><U><Font
        html:Color="#000000"><?=$tutor;?><?#=implode(', ', $tutors);?></Font></U></ss:Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="0" ss:Height="180.75">
    <Cell ss:StyleID="s110"><Data ss:Type="String">№&#10;п/п</Data></Cell>
    <Cell ss:StyleID="s111"><Data ss:Type="String">Ф.И.О. обучающегося</Data></Cell>
    <Cell ss:StyleID="s112"><Data ss:Type="String">№ зачетной книжки</Data></Cell>
    <Cell ss:StyleID="s113"><Data ss:Type="String"><?=($isModuleSubject) ? ('Интегральный&#10; текущий рейтинг') : ('Итоговый текущий рейтинг обучающегося за семестр&#10; (до 80 баллов)')?></Data></Cell>
    <Cell ss:StyleID="s113"><Data ss:Type="String">Подпись педагогического &#10;работника, проводившего текущий &#10;контроль успеваемости &#10;обучающихся в семестре</Data></Cell>
    <Cell ss:StyleID="s113"><Data ss:Type="String">Pубежный рейтинг обучающегося&#10;на зачете / экзамене&#10;(до 20 баллов)</Data></Cell>
    <Cell ss:StyleID="s113"><Data ss:Type="String">Академический рейтинг&#10;обучающегося по учебной &#10;дисциплине&#10;(сумма гр. 4 и 6)</Data></Cell>
    <Cell ss:StyleID="s112"><Data ss:Type="String">Аттестационная оценка</Data></Cell>
    <Cell ss:StyleID="s113"><Data ss:Type="String">Подписи экзаменаторов</Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="0" ss:Height="10.5">
    <Cell ss:StyleID="s116"><Data ss:Type="Number">1</Data></Cell>
    <Cell ss:StyleID="s116"><Data ss:Type="Number">2</Data></Cell>
    <Cell ss:StyleID="s116"><Data ss:Type="Number">3</Data></Cell>
    <Cell ss:StyleID="s116"><Data ss:Type="Number">4</Data></Cell>
    <Cell ss:StyleID="s116"><Data ss:Type="Number">5</Data></Cell>
    <Cell ss:StyleID="s116"><Data ss:Type="Number">6</Data></Cell>
    <Cell ss:StyleID="s116"><Data ss:Type="Number">7</Data></Cell>
    <Cell ss:StyleID="s116"><Data ss:Type="Number">8</Data></Cell>
    <Cell ss:StyleID="s116"><Data ss:Type="Number">9</Data></Cell>
   </Row>   
   		<?php $row_number = 0; ?>
		<?php foreach($this->persons as $key => $person):?>		
			<?php
			$isPassTotalRating = Zend_Registry::get('serviceContainer')->getService('Lesson')->isPassTotalRating($this->additional['maxBallTotalRating'], $this->additional['dataRatingTotal'][$key], $this->additional['isDO'], $this->additional['is_practice']);            
			$isPassTotalRating = empty( $this->scores[$key.'_total']['fail_message'] ) ? $isPassTotalRating : false;			
			$row_number++;
            ?><Row ss:AutoFitHeight="0">
				<Cell ss:StyleID="s75"><Data ss:Type="String"><?=$row_number;?>.</Data></Cell>
				<Cell ss:StyleID="s76"><Data ss:Type="String"><?=$this->escape($person->getName());?></Data></Cell>
				<Cell ss:StyleID="s75"><Data ss:Type="String"><?=$this->additional['recordBookNumbers'][$key];?></Data></Cell>
				<Cell ss:StyleID="s75">
					<Data ss:Type="String"><?php if($isModuleSubject): ?><?=round($this->scores[$key."_total"]['integrateMediumRating'])?>
					<?php else : ?><?=(isset($this->additional['dataRatingMedium'][$key])) ? ( round($this->additional['dataRatingMedium'][$key]) ) : ('');?>
					<?php endif; ?></Data>
				</Cell>
				<Cell ss:StyleID="s75"/>
				<Cell ss:StyleID="s75">
					<Data ss:Type="String"><?=(isset($this->additional['dataRatingTotal'][$key]) && empty( $this->scores[$key.'_total']['fail_message'] ) ) ? ( round($this->additional['dataRatingTotal'][$key]) ) : ('0');?></Data>
				</Cell>
				<Cell ss:StyleID="s75">
					<Data ss:Type="String"><?php				
						$totalScore = -1;
						#if($this->scores[$key."_total"]['mark'] > -1) {
							if($isModuleSubject){						
								$totalScore = round($this->scores[$key."_total"]['integrateMediumRating']);
								
								if(empty( $this->scores[$key.'_total']['fail_message'] )){
									$totalScore += round($this->additional['dataRatingTotal'][$key]);
								}
								
								$mark_5 	= Zend_Registry::get('serviceContainer')->getService('Lesson')->getFiveScaleMark($totalScore); # TODO перенести в сервисный слов Lesson							
								echo $totalScore;							
							} else {
								
								$totalScore = round($this->additional['dataRatingMedium'][$key]);
								
								if(empty( $this->scores[$key.'_total']['fail_message'] )){
									$totalScore += round($this->additional['dataRatingTotal'][$key]);
								}
								
								$mark_5 	= $this->scores[$key.'_total']['mark_5'];							
								#if( $isPassTotalRating){
									echo $totalScore;
								#}
							}							
						#}
					?></Data>
				</Cell>
				<Cell ss:StyleID="s77">					
					<?php			
						if(
							empty($this->additional['dataRatingTotal'][$key]) 
							||
							!empty( $this->scores[$key.'_total']['fail_message'] )
						) {
							$mark_5 = 0; # неявка
						}elseif( !$isPassTotalRating ){
							$mark_5 = 2; #Неважно, как много набрано баллов. Если завалил экзамен, в любом случае это неуд.
						}

						$mark_5_text = Zend_Registry::get('serviceContainer')->getService('Lesson')->getTextFiveScaleMark($mark_5, $this->additional['exam_type']);
						
						if(!isset($group_marks[$mark_5_text])){
							$group_marks[$mark_5_text] = 1;	
						} else {
							$group_marks[$mark_5_text]++;
						}				
					?>
					<Data ss:Type="String"><?=$mark_5_text?></Data>
				</Cell>
				<Cell ss:StyleID="s78"/>
			</Row>
		<?php endforeach;?>		   
   <Row ss:AutoFitHeight="0">
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s68"/>
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s79"/>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="1" ss:StyleID="s81"><Data ss:Type="String">Итого:</Data></Cell>
    <Cell ss:StyleID="s82"/>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="1" ss:StyleID="s84"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40"><Font html:Color="#000000">&quot;зачтено&quot; </Font><U><Font
        html:Color="#000000"><?=(int)$group_marks['зачтено']?></Font></U></ss:Data></Cell>
    <Cell ss:Index="4" ss:MergeAcross="1" ss:StyleID="s67"><Data ss:Type="String">Декан факультета</Data></Cell>
    <Cell ss:StyleID="s85"><Data ss:Type="String">_________________________ /<?=($dean)?$dean:'_________________________'?></Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="1" ss:StyleID="s84"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40"><Font html:Color="#000000">&quot;не зачтено&quot; </Font><U><Font
        html:Color="#000000"><?=(int)$group_marks['не зачтено']?></Font></U></ss:Data></Cell>
    <Cell ss:StyleID="s82"/>
    <Cell ss:MergeAcross="1" ss:StyleID="s67"><Data ss:Type="String">Дата <?=$date_issue?></Data></Cell>
    <Cell ss:StyleID="s82"/>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="1" ss:StyleID="s84"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40"><Font html:Color="#000000">&quot;отлично&quot; </Font><U><Font
        html:Color="#000000"><?=(int)$group_marks['отлично']?></Font></U></ss:Data></Cell>
    <Cell ss:Index="4" ss:MergeAcross="1" ss:StyleID="s67"><Data ss:Type="String">Экзаменаторы (п. 9)</Data></Cell>
    <Cell ss:StyleID="s85"><Data ss:Type="String">_________________________ /<?=($tutor)?$tutor:'_________________________'?></Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="1" ss:StyleID="s84"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40"><Font html:Color="#000000">&quot;хорошо&quot; </Font><U><Font
        html:Color="#000000"><?=(int)$group_marks['хорошо']?></Font></U></ss:Data></Cell>
    <Cell ss:StyleID="s82"/>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="1" ss:StyleID="s84"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40"><Font html:Color="#000000">&quot;удовлетворительно&quot; </Font><U><Font
        html:Color="#000000"><?=(int)$group_marks['удовлетворительно']?></Font></U></ss:Data></Cell>
    <Cell ss:StyleID="s82"/>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="1" ss:StyleID="s84"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40"><Font html:Color="#000000">&quot;неудовлетворительно&quot; </Font><U><Font
        html:Color="#000000"><?=(int)$group_marks['неуд.']?></Font></U></ss:Data></Cell>
    <Cell ss:StyleID="s82"/>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:MergeAcross="1" ss:StyleID="s84"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40"><Font html:Color="#000000">&quot;не явились&quot; </Font><U><Font
        html:Color="#000000"><?=(int)$group_marks['неявка']?></Font></U></ss:Data></Cell>
    <Cell ss:StyleID="s82"/>
   </Row>
   <Row ss:AutoFitHeight="0" ss:Height="26.25">
    <Cell ss:StyleID="s86"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40"><B><I><Font html:Color="#000000">Примечания:</Font></I></B><Font
       html:Size="10" html:Color="#000000"> </Font></ss:Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="0" ss:Height="111.75">
    <Cell ss:MergeAcross="8" ss:StyleID="s88"><Data ss:Type="String">Не допускается: Внесение исправлений и дополнений «от руки» в список обучающихся; исправление оценки с помощью штриха. Ошибочно проставленная оценка зачеркивается и рядом делается запись: «Исправленному с (указать неправильную оценку) на (указать правильную оценку) верить», скрепляемая подписями экзаменаторов.&#10;&#10;В случае неявки обучающегося на зачет (экзамен) в графе №8 слева делается запись «н/я», в графе №9 ставиться подпись экзаменатора.&#10;&#10;Ведомость является недействительной без подписи декана факультета, экзаменаторов и педагогического работника, проводившего контроль текущей успеваемости обучающихся в семестре. Аттестационная оценка (о сдаче зачета/ экзамена) проставляется в перерасчете на систему: «неудовлетворительно», «удовлетворительно», «хорошо», «отлично», «зачтено», «не зачтено» по шкале:</Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s68"/>
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s79"/>
    <Cell ss:StyleID="s79"/>
   </Row>   
   <Row ss:AutoFitHeight="0">
    <Cell ss:Index="3" ss:StyleID="s89"/>
    <Cell ss:StyleID="s90"/>
    <Cell ss:StyleID="s90"/>
    <Cell ss:MergeAcross="2" ss:StyleID="m36492180"><Data ss:Type="String">0-64 баллов – неудовлетворительно</Data></Cell>
    <Cell ss:StyleID="s98"/>
   </Row>
   <Row ss:AutoFitHeight="0" ss:Height="15.75">
    <Cell ss:Index="3" ss:MergeAcross="2" ss:StyleID="s93"><Data ss:Type="String">0-64 баллов – не зачтено</Data></Cell>
    <Cell ss:MergeAcross="2" ss:StyleID="m36492220"><Data ss:Type="String">65-74 баллов – удовлетворительно</Data></Cell>
    <Cell ss:StyleID="s98"/>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:Index="3" ss:MergeAcross="2" ss:StyleID="s93"><Data ss:Type="String">65-100 баллов – зачтено</Data></Cell>
    <Cell ss:MergeAcross="2" ss:StyleID="m36492260"><Data ss:Type="String">75-84 баллов – хорошо</Data></Cell>
    <Cell ss:StyleID="s98"/>
   </Row>
   <Row ss:AutoFitHeight="0">
    <Cell ss:Index="3" ss:StyleID="s102"/>
    <Cell ss:StyleID="s103"/>
    <Cell ss:StyleID="s103"/>
    <Cell ss:MergeAcross="2" ss:StyleID="m36492384"><Data ss:Type="String">85-100 баллов – отлично</Data></Cell>
    <Cell ss:StyleID="s98"/>
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Layout x:Orientation="Landscape"/>
    <Header x:Margin="0.51181102362204722"/>
    <Footer x:Margin="0.51181102362204722"/>
    <PageMargins x:Bottom="0.98425196850393704" x:Left="0.74803149606299213"
     x:Right="0.74803149606299213" x:Top="0.98425196850393704"/>
   </PageSetup>
   <Unsynced/>
   <Print>
    <ValidPrinterInfo/>
    <PaperSizeIndex>9</PaperSizeIndex>
    <HorizontalResolution>600</HorizontalResolution>
    <VerticalResolution>600</VerticalResolution>
   </Print>
   <Selected/>
   <DoNotDisplayGridlines/>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>7</ActiveRow>
     <ActiveCol>11</ActiveCol>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>