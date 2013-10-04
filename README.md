QuickMail
=========

Send quick mail.
This class is based on the Zend_Mail class (version 1.11).

How to use it
-------------------------

If you are not in a Zend Framework project, you have to include 2 class which you can found usually here :
* /usr/share/php/Zend/Mail.php
* /usr/share/php/Zend/Mail/Transport/Smtp.php

Here is a full example

    $content = '<p>Hi, this is me : <img src="cid:1" /></p>';
    $subject = 'My subject';
    $recipients = array(
        'friend@domain.com'
    );
    $from = 'mymail@domain.com';
    $fromName = 'Louis';
    $recipientsCc = array(
        'copy.friend@domain.com'
    );
    $recipientsBcc = array(
        'hidden.friend@domain.com'
    );
    $attachments = array(
        '/tmp/myphoto.jpg',
        '/tmp/random-pdf.pdf'
    );
    $headers = array(
        'X-Mailer' => 'QuickMail'
    );

    if (QuickMail::isValid($recipients)) {
        $mail = new QuickMail();
        $mail->feed(
            $content,
            $subject,
            $recipients,
            $from,
            $fromName,
            $recipientsCc,
            $recipientsBcc,
            $attachments,
            $headers
        );
        $mail->send();
    } else {
        echo 'One of the email is not valid';
    }

