In Brief
==========
Bit Funnel is a *work-in-progress* small component which will pull existing attachments from a mailbox via IMAP and store them in a local target folder directory. It relies on PHP's IMAP module for I/O (http://php.net/manual/en/intro.imap.php) 

I scratched this down out of necessity. I have found there were many attachment ripping functions out there, but nothing that seemed to do the job with any level of reliability. Attachments can be buried in different ways in the message body and this component takes a simple approach to try to catch them all. 

Right now this is a crude proof of concept but I hope to break things down into component parts and eliminate the repitition.


Example
==============

Simply set the configuration file with the details for your account and path to the folder you want, then create a BitFunnel(), the constructor connects to the server and starts downloading.

```php
<?php

require("BitFunnel.php");

$myAccount = array(
	'account' => array(
		 'server' => array(
			  'host' => 'imap.gmail.com'
			, 'port' => '993'
		    , 'user' => 'youruser'
		    , 'password' => 'swordfish'
		    , 'flags' => array('imap', 'ssl')
		    , 'mailbox' => "INBOX"
		)
	   , 'savepath' => 'attachments/'
	)
);

//grab the attachments
$bitFunnel = new BitFunnel($myAccount);

?>

```