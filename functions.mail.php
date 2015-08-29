<?php
/**
 * Mail Functions
 *
 * We use PEAR to send HTML mail. Rather than repeating the same code again
 * and again, we call the function pearMail with more or less the same parameters
 * as the regular PHP mail() function, so that we can centralize the way
 * mail is sent. Also, if a server does not support / have PEAR, we can change
 * only this function and the rest will be fine. If you need to send a more
 * complicated mail (e.g. with attachments), then you can still include this
 * file to get the PEAR includes, but you'll have to write the code instead 
 * of using this function. 
 *
 * Uses PEAR - PHP Extension and Application Repository
 * PEAR is BSD Licensed
 * More at http://pear.php.net/
 */


require_once 'Mail.php';
require_once 'Mail/mime.php';

function pearMail($to, $subject, $html, $text, $headers = false) {
  $headers = $headers ? $headers : 
  array(
    'From'    => EMAIL,
    'Subject' => $subject,
  );

  $html = 
  '<html>
    <head>
    <style type="text/css">
      body{font-family: Arial, sans-serif;}
      a{color: #0088cc}
      a:hover {color: #005580;text-decoration: none;}
    </style>
    </head>
      <body>' . $html . '</body>
    </html>';

    $html = $html;
    $text = utf8_decode($text);

  // We never want mails to send out from local machines. It's all too easy
  // to accidentally send out a test mail to a client or their clients. 
  if (LOCAL) {
    die($html);
  } else {
    $mime = new Mail_mime();
    $mime->setTXTBody($text);

    // Add standard CSS or other mail headers to this string, and all mails will
    // be styled uniformly. 
    $mime->setHTMLBody($html);
    $body = $mime->get();
    $hdrs = $mime->headers($headers);
    $mail =& Mail::factory('mail');
    $mail->send($to, $hdrs, $body);
  }
}
?>
