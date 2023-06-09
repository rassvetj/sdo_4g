<?php
class HM_Kbase_Source_Csv_CsvAdapter extends HM_Adapter_Csv_Abstract
{
    // Сколько первых строк будет пропущено
    protected $_skipLines = 1; # где используется ?

    public function getMappingArray()
    {
        return array(
			#0 =>  'row', 				#порядковый номер - пропускаем
			1 =>  'direction', 			#Направление подготовки
			2 =>  'code', 				#Шифр
			3 =>  'years', 				#Год начала обучения
			4 =>  'discipline',			#Наименование дисциплины
			5 =>  'e_publishing',		#Перечень электронных изданий указанных в рабочей программе дисциплины
			6 =>  'e_publishing_url',	#Ссылки на электронные издания указанные в рабочей программе дисциплины
			7 =>  'e_educational', 		#Перечень электронных образовательных ресурсов указанных в рабочей программе дисциплины
			8 =>  'e_educational_url',	#Ссылки на электронные образовательные ресурсы   указанные в рабочей программе дисциплины			
        );
    }
}

