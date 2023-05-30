<?php

class HM_Messenger_Service_Mail extends HM_Messenger_Service_Abstract
{
    public function update(SplSubject $message)
    {
        $author = HM_Messenger::SYSTEM_USER_ID;//$message->getSender();

        $recipient = $message->getReceiver();

        $validator = new Zend_Validate_EmailAddress();

        if (strlen($recipient->EMail) && $validator->isValid($recipient->EMail)) {
            $mail = new Zend_Mail(Zend_Registry::get('config')->charset);
            $messageText = $message->getMessage();

            if (null !== $message->getIcal()) {
                $mimePart = new Zend_Mime_Part($message->getIcal());
                //$mimePart->type = 'text/calendar; method=REQUEST; charset="utf-8"';
                $mimePart->type = 'text/calendar';
                $mimePart->disposition = Zend_Mime::DISPOSITION_INLINE;
                $mimePart->encoding = Zend_Mime::ENCODING_8BIT;
                $mimePart->filename = 'event.ics';

                $mail->addAttachment($mimePart);
            }
            $config = Zend_Registry::get('config');
            $useForceAttach = isset(
                $config->mail,
                $config->mail->template,
                $config->mail->template->attach
            ) && in_array(
                $message->getTemplateId(),
                $config->mail->template->attach->toArray()
            );
            if ($useForceAttach) {
                $parts = $this->_remoteToAttachments($message->getMessage());
                foreach ($parts['attachments'] as $part) {
//                    Zend_Registry::get('log_system')->debug(var_export($part, true));
                    $mail->addAttachment($part);
                }
                $messageText = $parts['message'];
                $mail->setType(Zend_Mime::MULTIPART_RELATED);
            }

            $mail->addTo($recipient->EMail, $recipient->getName());
            $mail->setSubject($message->getSubject());

            if (strlen($author->EMail)) {
                $mail->setFrom($author->EMail, $author->getName());
            } else {
                $mail->setFromToDefaultFrom();
            }

            $mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);
            try {
                return $mail->send();
            } catch (Zend_Mail_Exception $e) {
//                Zend_Registry::get('log_system')->debug($e->getMessage().'\n'.$e->getTraceAsString());
                return false;
            }
        }

        return false;

    }

    /**
     * Function parse text and return array of attchments and text with refers to attachments
     *
     * @param string $text text for parsing
     * @param array $tags optional associative array of html/xml tags
     *  which will been processing to attachements structure
     * ('tags for processing' =>'tag attribut for external source (src, href, etc)'
     *
     * @return array containing keys
     * <table>
     * <tr><td>`'attachments'`</td><td><b>array</b></td><td>Zend_Mime_Parts of attachments</td></tr>
     * <tr><td>`'message'`</td><td><b>string</b></td><td>formatted text with refers</td></tr>
     * </table>
     *
     * @author stanislav tibilius <stanislav.tibilius@hypermethod.ru>
     * @see http://www.faqs.org/rfcs/rfc1873.html RFC 1813
     * @see http://www.faqs.org/rfcs/rfc2387.html RFC 2378
     * 
     * 
     * 
     */
    protected function _remoteToAttachments($message, array $tags = array ('img' => 'src' ))
    {
        $attachments = array();
        /// php unicode break-dance
        $domModelMessage = new Zend_Dom_Query(
            mb_convert_encoding(
                $message,
                'HTML-ENTITIES',
                mb_detect_encoding($message)
            )
        );
        foreach ($tags as $tag => $attr) {
            $nodeList = $domModelMessage->queryXpath('//' . $tag);
            foreach ($nodeList as $node) {
                $source = $node->getAttribute($attr);
                if (! $source || ! ($file = $this->_urlToFile($source)) ) {
                    continue;
                }
                
                $cid = md5(basename($source));
                $node->setAttribute($attr, "cid:{$cid}");
                $mimePart = new Zend_Mime_Part(file_get_contents($file['name']));
                $finfo = finfo_open(FILEINFO_MIME_TYPE); 
                $mimePart->type = finfo_buffer($finfo, file_get_contents($file['name']));
                $mimePart->encoding = Zend_Mime::ENCODING_BASE64;                
                $mimePart->id = $cid;
                $mimePart->filename = basename($file['name']);
                $attachments[$cid] = $mimePart;
            }
            //save document changes
            $domModelMessage->setDocument($nodeList->getDocument()->saveHTML());
        }
        $message = $nodeList->getDocument()->saveHTML();
        return compact('message', 'attachments');
    }


    /**
     * Function try to corrale url to file in current file system
     *
     * @param string $url Url for resolve
     * @return FALSE|array if url can be reolved returns array
     * <table>
     * <tr><td>`'name'`<td><td><b>string<b><td><td>full filename<td></tr>
     * <tr><td>`'type'`<td><td><b><b>string<b><b><td><td>file mime type<td></tr>
     * </table>
     * if cannot resolve returns default values
     * otherwise return FALSE
     *
     * @author stanislav tibilius <stanislav.tibilius@hypermethod.ru>
     * @todo make function  HM_Messenger_Service_Mail::_urlToFile_ better
     */
    protected function _urlToFile($url)
    {
        $defaultResult = array (
            'name' => $url,
            'type' => Zend_Mime::TYPE_OCTETSTREAM,
        );
        $fileStorageService = $this->getService('StorageFileSystem');
        $fsRootUrl = $fileStorageService->getRootUrl();
        if (false === ($pos = strpos($url, $fsRootUrl))){
            return $defaultResult;
        }
        $fileParts = array(
            $fileStorageService->getRootPath(),
            substr($url, strpos($url, $fsRootUrl) + strlen($fsRootUrl))
        );
        if (file_exists($name = realpath(implode(DIRECTORY_SEPARATOR, $fileParts)))){
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $name);
            finfo_close($finfo);
            return compact('name', 'type');
        }
        return $defaultResult;
    }
}