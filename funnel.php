<html>
<head>
<title>Funnel Project</title>

<style type="text/css">

div.toggler  { border:1px solid #ccc; background:url(gmail2.jpg) 10px 12px #eee no-repeat; cursor:pointer; padding:10px 32px; }
div.toggler .subject  { font-weight:bold; }
div.read  { color:#666; }
div.toggler .from, div.toggler .date { font-style:italic; font-size:11px; }
div.body   { padding:10px 20px; }

</style>

</head>

<body>

<?php



$hostname = '{ash.resolutionim.com:993/imap/ssl/novalidate-cert}INBOX';
$username = 'brendan';
$password = 'PASSWORD HERE';


/* connect to gmail */
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'brendandevbox@gmail.com';
$password = 'PASSWORD HERE';

$whereToSaveAttachments = "/var/www/html/attachments/";


//require_once('mparser/MimeMailParser.class.php');

/* try to connect */
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to mail server: ' . imap_last_error());

/* grab emails */
//$emails = imap_search($inbox,'SINCE "21 July 2011"');
$emails = imap_search($inbox,'ALL');


/* if emails are returned, cycle through each... */
if($emails) {
  
  /* begin output var */
  $output = '';
  
  /* put the newest emails on top */
  rsort($emails);
  
  
  /* for every email... */
  foreach($emails as $email_number) {
    
    /* get information specific to this email */
    $overview = imap_fetch_overview($inbox,$email_number,0);
    $message = imap_fetchbody($inbox,$email_number,2);
    $structure = imap_fetchstructure($inbox,$email_number);
    $parts = $structure->parts;
    $hasAttachments = false;
    
  	//print_r($structure);
  	
    $attachments = extract_attachments($inbox, $email_number);
    
    //print_r($attachments);
    
    
    /* output the email header information */
    $output.= '<div class="toggler '.($overview[0]->seen ? 'read' : 'unread').'">';
    $output.= '<span class="subject">'.$overview[0]->subject.'</span> ';
    $output.= '<span class="from">'.$overview[0]->from.'</span>';
    $output.= '<span class="date">on '.$overview[0]->date.'</span>';
  	
  	foreach($attachments as $a) {
	  	if($a["is_attachment"]) {
	  		$output.= "<span class=\"date\"> <strong> " . $a["name"] . "</strong></span> ";
	  				
	  		$pathToEndFile = $whereToSaveAttachments . $a["name"];	
	  				
			if(!file_exists($pathToEndFile)) {
				//save the file to the server	
				$fp=fopen($pathToEndFile,"w+");
				fwrite($fp,$a["attachment"]);
				fclose($fp);
			}
	  	}
  	}
    
    
    $output.= '</div>';
    
    /* output the email body */
   	// $output.= '<div class="body">'.strip_tags($message).'</div>';
  }
  
  echo $output;
  
} 

/* close the connection */
imap_close($inbox);


function extract_attachments($connection, $message_number) {
   
    $attachments = array();
    $structure = imap_fetchstructure($connection, $message_number);
   
    if(isset($structure->parts) && count($structure->parts)) {
   
        for($i = 0; $i < count($structure->parts); $i++) {
        
        
   
            $attachments[$i] = array(
                'is_attachment' => false,
                'filename' => '',
                'name' => '',
                'attachment' => ''
            );
           
            if($structure->parts[$i]->ifdparameters) {
                foreach($structure->parts[$i]->dparameters as $object) {
                    if(strtolower($object->attribute) == 'filename') {
                        $attachments[$i]['is_attachment'] = true;
                        $attachments[$i]['filename'] = $object->value;
                    }
                }
            }
           
            if($structure->parts[$i]->ifparameters) {
                foreach($structure->parts[$i]->parameters as $object) {
                    if(strtolower($object->attribute) == 'name') {
                        $attachments[$i]['is_attachment'] = true;
                        $attachments[$i]['name'] = $object->value;
                    }
                }
            }
           
            if($attachments[$i]['is_attachment']) {
                $attachments[$i]['attachment'] = imap_fetchbody($connection, $message_number, $i+1);
                if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                    $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                }
                elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
                    $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                }
            }
            
            //added by brendan to detect deeper sub parts
            if($structure->parts[$i]->parts) {
            	//print "THIS MESSSAGE HAS SUB PARTS";
            	/////////////////////////////////add the children in too
            	$substructure = $structure->parts[$i];
            	
            	
            	for($si = 0; $si < count($substructure->parts); $si++) {
            	
            	$partNumber = ($i + 1) . "." . ($si + 1);
            	
            		if($substructure->parts[$si]->ifdparameters) {
		                foreach($substructure->parts[$si]->dparameters as $object) {
		                    if(strtolower($object->attribute) == 'filename') {
		                        $attachments[$i]['is_attachment'] = true;
		                        $attachments[$i]['filename'] = $object->value;
		                    }
		                }
		            }
		           
		            if($substructure->parts[$si]->ifparameters) {
		                foreach($substructure->parts[$si]->parameters as $object) {
		                    if(strtolower($object->attribute) == 'name') {
		                        $attachments[$i]['is_attachment'] = true;
		                        $attachments[$i]['name'] = $object->value;
		                    }
		                }
		            }
		           
		            if($attachments[$i]['is_attachment']) {
		                $attachments[$i]['attachment'] = imap_fetchbody($connection, $message_number, $partNumber);
		                if($substructure->parts[$si]->encoding == 3) { // 3 = BASE64
		                    $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
		                }
		                elseif($substructure->parts[$si]->encoding == 4) { // 4 = QUOTED-PRINTABLE
		                    $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
		                }
		            }
				}
            	/////////////////////////////////
            }
           
        }
       
    }
   
    return $attachments;
   
}





?>
</body>
</html>