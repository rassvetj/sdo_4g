<?php
interface HM_Adapter_Import_Interface
{
    public function setFileName($filename);

    public function setOptions($options);

    /**
     * Возвращает true в случае если требуется загрузка файла с структурой
     * @abstract
     * @return bool
     */
    public function needToUploadFile();
}