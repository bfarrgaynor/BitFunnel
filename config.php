<?php

/*

config.php - Sets an array with connection details for your remote IMAP server.

*/


//HOST - Internet domain name or bracketed IP address of server.
$config['account'][0]['server']['host'] = "imap.gmail.com"; //eg. imap.gmail.com

//PORT - optional TCP port number, default is the default port for that service
$config['account'][0]['server']['port'] = "993"; //eg IMAP SSL (Gmail Default) 

//USERNAME - your account name
$config['account'][0]['server']['user'] = "";

//PASSWORD - your account password
$config['account'][0]['server']['password'] = "";

/*
FLAGS - optional flags, see following table.

/service=service	mailbox access service, default is "imap"
/user=user	remote user name for login on the server
/authuser=user	remote authentication user; if specified this is the user name whose password is used (e.g. administrator)
/anonymous	remote access as anonymous user
/debug	record protocol telemetry in application's debug log
/secure	do not transmit a plaintext password over the network
/imap, /imap2, /imap2bis, /imap4, /imap4rev1	equivalent to /service=imap
/pop3	equivalent to /service=pop3
/nntp	equivalent to /service=nntp
/norsh	do not use rsh or ssh to establish a preauthenticated IMAP session
/ssl	use the Secure Socket Layer to encrypt the session
/validate-cert	validate certificates from TLS/SSL server (this is the default behavior)
/novalidate-cert	do not validate certificates from TLS/SSL server, needed if server uses self-signed certificates
/tls	force use of start-TLS to encrypt the session, and reject connection to servers that do not support it
/notls	do not do start-TLS to encrypt the session, even with servers that support it
/readonly	request read-only mailbox open (IMAP only; ignored on NNTP, and an error with SMTP and POP3)
*/

$config['account'][0]['server']['flags'][] = "imap"; //Gmail defaults 
$config['account'][0]['server']['flags'][] = "ssl"; //Gmail defaults

//MAILBOX - remote mailbox name, default is INBOX
$config['account'][0]['server']['mailbox'] = "INBOX"; // eg. INBOX (Gmail Default)


//SAVEPATH - where to save attachments on the local server
$config['account'][0]['savePath'] = "attachments/";