<?php
class HM_Captcha_CaptchaService extends HM_Service_Abstract
{
    const MAX_INVALID_COUNT = 3;

    public function attempt($login)
    {
        if (strlen($login)) {
            $captcha = $this->getOne($this->find($login));
            if (!$captcha) {
                $captcha = $this->insert(
                    array(
                        'login' => $login,
                        'attempts' => 0,
                        'updated' => $this->getDateTime()
                    )
                );
            }
            $captcha->updated = $this->getDateTime();
            $captcha->attempts++;
            return $this->update($captcha->getValues());
        }

    }
    
    public function purge()
    {
        $this->deleteBy($this->quoteInto('updated < ?', date('Y-m-d H:i:s', time() - 60*60*24)));
    }
}