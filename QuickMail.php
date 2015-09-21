<?php

/**
 * Send mail quickly.
 * This class use the Zend_Mail class.
 * 
 * @author Louis Hatier
 */
class QuickMail extends Zend_Mail
{
    private $_transport;

    /**
     * Set the charset and default smtp transport
     * 
     * @param string $charset
     * @param string $smtpServer
     * @param string $username
     * @param string $password
     * @return QuickMail
     */
    public function __construct($charset = 'UTF-8', $smtpServer = 'localhost', $username = null, $password = null)
    {
        if (!is_null($username) && !is_null($password)) {
            $mailConfig = array(
                'auth'     => 'login',
                'username' => $username,
                'password' => $password
            );
        } else {
            $mailConfig = array();
        }

        parent::__construct($charset);
        $this->_transport = new Zend_Mail_Transport_Smtp($smtpServer, $mailConfig);
        Zend_Mail::setDefaultTransport($this->_transport);
    }

    /**
     * Feed the informations
     * 
     * @param string $content
     * @param string $subject
     * @param array $recipients
     * @param string $from
     * @param string $fromName
     * @param array $attachments
     * @param array $headers
     * @param string $contentText
     * @return void
     */
    public function feed($content, $subject, array $recipients, $from, $fromName = null, array $recipientsCc = null, array $recipientsBcc = null, array $attachments = null, array $headers = null, $contentText = null)
    {
        // encode subject and from name if definied
        $subject = mb_encode_mimeheader($subject, $this->_charset, 'B');
        if (!is_null($fromName)) {
            $fromName = mb_encode_mimeheader($fromName, $this->_charset, 'B');
        }
        
        // add the recipient
        foreach ($recipients as $recipient) {
            $this->addTo($recipient);
        }

        // add the recipient CC
        if (!empty($recipientsCc)) {
            foreach ($recipientsCc as $recipientCc) {
                $this->addCc($recipientCc);
            }
        }

        // add the recipient BCC
        if (!empty($recipientsBcc)) {
            foreach ($recipientsBcc as $recipientBcc) {
                $this->addBcc($recipientBcc);
            }
        }
        
        // add the attachments
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $newAttachment = $this->createAttachment(
                    file_get_contents($attachment),
                    mime_content_type($attachment),
                    //Zend_Mime::DISPOSITION_ATTACHMENT,
                    Zend_Mime::DISPOSITION_INLINE,
                    Zend_Mime::ENCODING_BASE64,
                    basename($attachment)
                );
                $newAttachment->id = basename($attachment);
            }
        }

        // add headers
        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                $this->addHeader($key, $value);
            }
        }

        // add plain text content
        if (!empty($contentText)) {
            $this->setBodyText($contentText);
        }

        // set everything
        $this->setBodyHtml($content);
        $this->setFrom($from, $fromName);
        $this->setReplyTo($from, $fromName);
        $this->setSubject($subject);
    }

    /**
     * Send the email and return the log
     * 
     * @param Zend_Mail_Transport_Smtp $transport
     * @return string $log
     */
    public function send($transport = null)
    {
        parent::send($this->_transport);
        return $this->_transport->getConnection()->getLog();
    }

    /**
     * Check the email format and the dns domain - accept an array of emails
     * 
     * @param string|array $emails
     * @return bool
     */
    static public function isValid($emails)
    {
        if (is_string($emails)) {
            $emails = array($emails);
        }

        foreach ($emails as $email) {
            // valid the email format
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                return false;
            }

            // check on dns domain
            list($account, $domain) = explode('@', $email, 2);
            if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
                return false;
            }
        }

        return true;
    }
}
