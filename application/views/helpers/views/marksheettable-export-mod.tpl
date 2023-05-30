<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
xmlns:css="http://macVmlSchemaUri" xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta name=Title content="">
<meta name=Keywords content="">
<meta http-equiv=Content-Type content="text/html; charset=<?php echo Zend_Registry::get('config')->charset?>">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 2008">
<meta name=Originator content="Microsoft Word 2008">
<title></title>
<!--[if gte mso 9]><xml>
 <o:OfficeDocumentSettings>
  <o:AllowPNG/>
 </o:OfficeDocumentSettings>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:Zoom>BestFit</w:Zoom>
  <w:SpellingState>Clean</w:SpellingState>
  <w:GrammarState>Clean</w:GrammarState>
  <w:TrackMoves>false</w:TrackMoves>
  <w:TrackFormatting/>
  <w:DoNotHyphenateCaps/>
  <w:PunctuationKerning/>
  <w:DrawingGridHorizontalSpacing>9,35 pt</w:DrawingGridHorizontalSpacing>
  <w:DrawingGridVerticalSpacing>9,35 pt</w:DrawingGridVerticalSpacing>
  <w:ValidateAgainstSchemas/>
  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
  <w:Compatibility>
   <w:SplitPgBreakAndParaMark/>
   <w:DontVertAlignCellWithSp/>
   <w:DontBreakConstrainedForcedTables/>
   <w:DontVertAlignInTxbx/>
   <w:Word11KerningPairs/>
   <w:CachedColBalance/>
   <w:UseFELayout/>
  </w:Compatibility>
 </w:WordDocument>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:LatentStyles DefLockedState="false" LatentStyleCount="276">
 </w:LatentStyles>
</xml><![endif]-->
<style>
<!--p.MSONORMAL
	{mso-bidi-font-size:8pt;}
li.MSONORMAL
	{mso-bidi-font-size:8pt;}
div.MSONORMAL
	{mso-bidi-font-size:8pt;}
p.SMALL
	{mso-bidi-font-size:1pt;}

 /* Font Definitions */
@font-face
	{font-family:Times;
	panose-1:2 0 5 0 0 0 0 0 0 0;
	mso-font-charset:0;
	mso-generic-font-family:auto;
	mso-font-pitch:variable;
	mso-font-signature:3 0 0 0 1 0;}
@font-face
	{font-family:Verdana;
	panose-1:2 11 6 4 3 5 4 4 2 4;
	mso-font-charset:0;
	mso-generic-font-family:auto;
	mso-font-pitch:variable;
	mso-font-signature:3 0 0 0 1 0;}
@font-face
	{font-family:Cambria;
	panose-1:2 4 5 3 5 4 6 3 2 4;
	mso-font-charset:0;
	mso-generic-font-family:auto;
	mso-font-pitch:variable;
	mso-font-signature:3 0 0 0 1 0;}
 /* Style Definitions */
p.MsoNormal, li.MsoNormal, div.MsoNormal
	{mso-style-parent:"";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:7.5pt;
	font-family:Verdana;
	mso-fareast-font-family:Verdana;
	mso-bidi-font-family:"Times New Roman";
	mso-bidi-theme-font:minor-bidi;}
p.small, li.small, div.small
	{mso-style-name:small;
	mso-style-parent:"";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:1.0pt;
	font-family:Verdana;
	mso-fareast-font-family:Verdana;
	mso-bidi-font-family:"Times New Roman";
	mso-bidi-theme-font:minor-bidi;}
span.SpellE
	{mso-style-name:"";
	mso-spl-e:yes;}
@page Section1
	{size:612.0pt 792.0pt;
	margin:72.0pt 90.0pt 72.0pt 90.0pt;
	mso-header-margin:35.4pt;
	mso-footer-margin:35.4pt;
	mso-paper-source:0;}
div.Section1
	{page:Section1;}
-->
</style>
<!--[if gte mso 10]>
<style>
 /* Style Definitions */
table.MsoNormalTable
	{mso-style-name:"Table Normal";
	mso-tstyle-rowband-size:0;
	mso-tstyle-colband-size:0;
	mso-style-noshow:yes;
	mso-style-parent:"";
	mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
	mso-para-margin:0cm;
	mso-para-margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:12.0pt;
	font-family:Cambria;
	mso-ascii-font-family:Cambria;
	mso-ascii-theme-font:minor-latin;
	mso-hansi-font-family:Cambria;
	mso-hansi-theme-font:minor-latin;}
</style>
<![endif]--><!--[if gte mso 9]><xml>
 <o:shapedefaults v:ext="edit" spidmax="1027">
  <o:colormenu v:ext="edit" strokecolor="none"/>
 </o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
 <o:shapelayout v:ext="edit">
  <o:idmap v:ext="edit" data="1"/>
 </o:shapelayout></xml><![endif]-->
</head>

<body lang=PT style='tab-interval:36.0pt'>
	<?php 
		$allGroups 			= array();
		$isModuleSubject 	= false;
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
	?>

	<?php $totalSchedules = count($this->schedules); ?>
	<?php $totalPersons = count($this->persons); ?>
	
	<table cellspacing="1" cellpadding="1" border="1px" style="border-color:white;">
	    <colgroup><col><col><col span="<?php echo $totalSchedules;?>"></colgroup>
	    <thead>
			<tr>
				<td colspan="9" style="border-color:white;">
					<center>ФЕДЕРАЛЬНОЕ ГОСУДАРСТВЕННОЕ БЮДЖЕТНОЕ ОБРАЗОВАТЕЛЬНОЕ УЧРЕЖДЕНИЕ ВЫСШЕГО ОБРАЗОВАНИЯ</center>
				</td>
			</tr>
			<tr>
				<td colspan="9" style="border:none;">
					<center>«РОССИЙСКИЙ ГОСУДАРСТВЕННЫЙ СОЦИАЛЬНЫЙ УНИВЕРСИТЕТ»</center>
				</td>
			</tr>
			<tr>
				<td colspan="9" style="border:none;">
					<center>ЗАЧЕТНО-ЭКЗАМЕНАЦИОННАЯ  ВЕДОМОСТЬ  № </center>
				</td>
			</tr>
			<tr>
				<td colspan="9" style="border:none;">
					Форма обучения Группа  <span style="text-decoration: underline;"><?=implode(', ', $allGroups);?></span> <?=$this->additional['semester'];?> семестр Учебный год	/ 					
				</td>
			</tr>
			<tr>
				<td colspan="9" style="border:none;">
					Дисциплина	<span style="text-decoration: underline;"><?=$this->additional['subjectName'];?></span> 
				</td>
			</tr>
			<tr>
				<td colspan="9" style="border:none;">
					<?
					$tutors = '';
					if(!empty($this->additional['tutors'])){
						foreach($this->additional['tutors'] as $t){
							$tutors .= ' '.$t->LastName.' '.$t->FirstName.' '.$t->Patronymic.',';
						}
						$tutors = trim($tutors, ',');
					}
					?>
					Ф.И.О. экзаменаторов	<span style="text-decoration: underline;"><?=$tutors;?></span> 
				</td>
			</tr>
			
	        <tr>
	            <th style="width:50px;"><div style="margin:5px;">№ п/п</div></th>				
	            <th><div style="margin:5px">Ф.И.О. обучающегося</div></th>				
	            <th><div style="margin:5px">№ зачетной книжки</div></th>				
	            <th><div style="margin:5px"><?=($isModuleSubject) ? ('Интегральный текущий рейтинг') : ('Итоговый текущий рейтинг обучающегося за семестр (до 80 баллов)')?></div></th>				
	            <th><div style="margin:5px">Подпись  педагогического работника, проводившего текущий контроль успеваемости  обучающихся в семестре</div></th>				
	            <th><div style="margin:5px">Рубежный рейтинг обучающегося на зачете / экзамене (до 20 баллов)</div></th>				
	            <th><div style="margin:5px">Академический рейтинг обучающегося  по  учебной дисциплине (сумма гр. 4  и  6)</div></th>				
	            <th><div style="margin:5px">Аттестационная оценка</div></th>				
	            <th border="1px" style="border-color:black;"><div style="margin:5px">Подписи  экзаменаторов</div></th>
	        </tr>
			<tr style="background-color:#C9C9C9;">
				<th><div style="margin:5px">1</th>
				<th><div style="margin:5px">2</th>
				<th><div style="margin:5px">3</th>
				<th><div style="margin:5px">4</th>
				<th><div style="margin:5px">5</th>
				<th><div style="margin:5px">6</th>
				<th><div style="margin:5px">7</th>
				<th><div style="margin:5px">8</th>
				<th><div style="margin:5px">9</th>
			</tr>
	    </thead>
	    <tbody>
			
	        <?php
	        $flag = 0;
			$count = 0;
	        foreach($this->persons as $key => $person):?>			
			<?php
			$isPassTotalRating = Zend_Registry::get('serviceContainer')->getService('Lesson')->isPassTotalRating($this->additional['maxBallTotalRating'], $this->additional['dataRatingTotal'][$key], $this->additional['isDO'], $this->additional['is_practice']);            
			$count++;
            ?>
	        <tr>
	            <td><div style="margin:5px"><?=$count;?></td>
	            <td><div style="margin:5px"><?php echo $this->escape($person->getName());?></td>				
				<td><div style="margin:5px"><?=$this->additional['recordBookNumbers'][$key];?></td>
				<td><div style="margin:5px">
				<?php if($isModuleSubject): ?>
					<?=$this->scores[$key."_total"]['integrateMediumRating']?>
				<?php else : ?>					
					<?= (isset($this->additional['dataRatingMedium'][$key])) ? ( ceil($this->additional['dataRatingMedium'][$key]) ) : ('');?>
				<?php endif; ?>
				</td>
				<td><div style="margin:5px">&nbsp;</td>
				<td><div style="margin:5px"><?= (isset($this->additional['dataRatingTotal'][$key])) ? ( round($this->additional['dataRatingTotal'][$key]) ) : ('');?></td>								
	            <td><div style="margin:5px">
					<?php
						$totalScore = -1;
						$mark_5 = '';
						if($this->scores[$key."_total"]['mark'] > -1) {
							if($isModuleSubject){								
								$totalScore = (round($this->scores[$key."_total"]['integrateMediumRating']) + round($this->additional['dataRatingTotal'][$key]) );
								$mark_5 	= Zend_Registry::get('serviceContainer')->getService('Lesson')->getFiveScaleMark($totalScore); # TODO перенести в сервисный слов Lesson
								echo $totalScore;
							
							} else {
								$totalScore = (round($this->additional['dataRatingMedium'][$key]) + round($this->additional['dataRatingTotal'][$key]) );
								$mark_5 	= $this->scores[$key.'_total']['mark_5'];
								if($this->scores[$key."_total"]['mark'] > -1 && $isPassTotalRating){
									echo $totalScore;
								}
							}							
						}
					?>
				</div></td>
				<td><div style="margin:5px">
					<?=Zend_Registry::get('serviceContainer')->getService('Lesson')->getTextFiveScaleMark($mark_5, $this->additional['exam_type']);?>
				</td>					
				<td><div style="margin:5px">&nbsp;</td>				
	        </tr>
	        <?php endforeach;?>
			
			<tr style="border:none;">
				<td style="border:none;" colspan="9">&nbsp;</td>
			</tr>
			<tr style="border:none;">
				<td style="border:none;" colspan="9">&nbsp;</td>
			</tr>
			<tr>
				<td style="border:none;" colspan="2">Итого:</td>
				<td style="border:none;" colspan="7">&nbsp;</td>
			</tr>
			<tr>
				<td style="border:none;" colspan="2">“зачтено” ________</td>
				<td style="border:none;" colspan="7">Декан факультета</td>
			</tr>
			<tr>
				<td style="border:none;" colspan="2">“не зачтено” ________</td>
				<td style="border:none;" colspan="7">Дата <?=date('d.m.Y');?></td>
			</tr>
			<tr>
				<td style="border:none;" colspan="2">“отлично” ________</td>
				<td style="border:none;" colspan="2">Экзаменаторы (п. 9)</td>
				<td style="border:none;" colspan="5"><?=$tutors;?></td>
			</tr>
			<tr>
				<td style="border:none;" colspan="2">“хорошо” ________</td>
				<td style="border:none;" colspan="7">&nbsp;</td>
			</tr>
			<tr>
				<td style="border:none;" colspan="2">“удовлетворительно” ________</td>
				<td style="border:none;" colspan="7">&nbsp;</td>
			</tr>
			<tr>
				<td style="border:none;" colspan="2">“неудовлетворительно” ________</td>
				<td style="border:none;" colspan="7">&nbsp;</td>
			</tr>
			<tr>
				<td style="border:none;" colspan="2">“не явились” ________</td>
				<td style="border:none;" colspan="7">&nbsp;</td>
			</tr>
			<tr style="border:none;">
				<td style="border:none;" colspan="9">&nbsp;</td>
			</tr>
			<tr>
				<td style="border:none;" colspan="9"><strong>Примечания:</strong></td>			
			</tr>
			<tr>
				<td style="border:none;" colspan="9">
					<span>Не допускается: Внесение исправлений и дополнений «от руки» в список обучающихся; исправление оценки с помощью штриха. Ошибочно проставленная оценка зачеркивается и рядом делается запись:   «Исправленному с (указать неправильную оценку)  на  (указать правильную оценку)  верить», скрепляемая подписями экзаменаторов.</span>
					<br>
					<span>В случае неявки обучающегося  на зачет (экзамен) в графе №8 слева делается запись «н/я», в графе №9 ставиться подпись экзаменатора.</span>
					<br>
					<span>Ведомость является недействительной без подписи декана факультета, экзаменаторов и педагогического работника, проводившего контроль текущей успеваемости обучающихся в семестре. Аттестационная оценка (о сдаче зачета/ экзамена) проставляется в перерасчете на систему: «неудовлетворительно», «удовлетворительно», «хорошо», «отлично», «зачтено», «не зачтено»  по шкале:</span>
				</td>			
			</tr>
			<tr style="border:none;">
				<td style="border:none;" colspan="9">&nbsp;</td>
			</tr>
			<tr style="border:none;">
				<td style="border:none; text-align: center;" colspan="9">
					<div style="margin:5px;">
						<table border="1px">
							<tr>
								<td rowspan="4">
									0-64  баллов       –     не зачтено
									<br>
									65-100 баллов    –     зачтено
								</td>
								<td>0-64  баллов       –   неудовлетворительно</td>
							</tr>
							<tr>							
								<td>65-74  баллов     –   удовлетворительно</td>
							</tr>
							<tr>							
								<td>75-84 баллов      –   хорошо</td>
							</tr>
							<tr>							
								<td>85-100  баллов   –   отлично</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
	    </tbody>
	</table>


	
	<script type="text/javascript">
	<!--
	window.onload = function cleanPage(){ document.getElementById('ZFDebug_debug').innerHTML = '';};
	//-->
	</script>
	
	
</body>
</html>