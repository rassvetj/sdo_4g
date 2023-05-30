<?php

class HM_Captcha_CaptchaTable extends HM_Db_Table
{
    protected $_name = "captcha";
    protected $_primary = "login";

    public function getDefaultOrder()
    {
        return array('captcha.login ASC');
    }
}