<?php

class BitFunnel {
	
	public $errors; 
	protected $server;
	protected $connection;
	protected $emails;
	protected $attachments;
	
	function __construct($account) {
		
		
		$this->server = $account['server'];
		$this->savePath = $account['savePath'];
		$this->errors = array();
		$this->attachments = array();
		
		//connect to the mailbox based on the server properties
		$this->connection = $this->connectToMailbox();
		
		//get the emails (default scope is ALL)
		$this->emails = $this->fetchRawEmails();
		
		//sort the mail
		$this->sortMailOrder();
		
		//process the mail, strip attachments, save them to the server
		$this->processMailBatch();
		
		
	}
	
	
	/*
		Takes the current server and formats a connection string for imap_open
	*/
	private function formatHostName() {
		
		return "{".$this->server['host'].":".$this->server['port']. implode("/", $this->server['flags']) . "}" . $this->server['mailbox'];
		
	}
	
	/*
		Establishes a connection to the mailbox, returns the imap stream  
	*/
	private function connectToMailbox() {
		
		$this->connection = imap_open($this->formatHostname($this->server),$this->server['user'],$this->server['password']);
	
		if($this->connection) {
			return $this->connection;
		} else {
			$this->errors[] = "Could not connect to mailbox on server.";
			$this->errors[] = imap_last_error();
			return false;
		}
	
	}
	
	
	
	private function fetchRawEmails($scope = "ALL") {
		
		if(!$this->connection) {
			$this->errors[] = "Could not fetch e-mails, connection to mailbox not present."; 
			return false;
		} else {
			
			$this->emails = imap_search($this->connection,$scope);
			
			if(is_array($this->emails)) {
				return $this->emails;
			} else {
			
				if($scope != "ALL") {
					$this->errors[] = "No e-mails found in mailbox using that scope."; 
				} else {
					$this->errors[] = "No e-mails found in mailbox."; 
				}
			
				return false;
			}
		}
		
	}
	
	/* 
		Sorts the emails to the newest first 
		Currently just uses rsort, will expand this later.
	*/
	private sortMailOrder() {
		rsort($this->emails);
		
	}
	
	
	/*
		Take the e-mails and process them
	*/
	private processMailBatch() {
		
		
		
		
		 /* for every email... */
		  foreach($this->emails as $email_number) {
		    
		    /* get information specific to this email */
		    $overview = imap_fetch_overview($this->connection,$email_number,0);
		    $message = imap_fetchbody($this->connection,$email_number,2);
		    $structure = imap_fetchstructure($this->connection,$email_number);
		    $parts = $structure->parts;
		    $hasAttachments = false;
		    
		    //saves emails to $this->attachments
		  	$this->extract_attachments($email_number);
		   
		   }
		    
		    if(is_array($this->attachments) && count($this->attachments)) {
			    
			    $this->saveAttachmentsToDisk();
			    
		    }
		    
		    
		    
		    imap_close($this->connection);
		    
		    
		    return true;
		    
		    /* output the email header information */
		   // $output.= '<div class="toggler '.($overview[0]->seen ? 'read' : 'unread').'">';
		   // $output.= '<span class="subject">'.$overview[0]->subject.'</span> ';
		   // $output.= '<span class="from">'.$overview[0]->from.'</span>';
		   // $output.= '<span class="date">on '.$overview[0]->date.'</span>';
		  	
		  			
	}
	
	
	private function saveAttachmentsToDisk() {
		
		foreach($this->attachments as $a) {
			  	
			  		//$output.= "<span class=\"date\"> <strong> " . $a["name"] . "</strong></span> ";
			  				
			  		$pathToEndFile = $this->savePath . $a["name"];	
			  				
					if(!file_exists($pathToEndFile)) {
						//save the file to the server	
						$fp=fopen($pathToEndFile,"w+");
						fwrite($fp,$a["data"]);
						fclose($fp);
					}
			  	
		  	}
		    
		    
		    //$output.= '</div>';

		
		
	}
	
	
	//search the message body for attachments in the raw content
	//it takes a imap connection and a message number
	private function extractAttachments($email_number) {
	   
	    $attachments = array();
	    $structure = imap_fetchstructure($this->connection, $email_number);
	   
	    if(isset($structure->parts) && count($structure->parts)) {
	    
	    	$this->attachments[] = $this->scanAndRipAttachmentsFromParts($sructure, $email_number);
	    	
	    } else {
	    	//no attachments found
	    }
	    
	 }
	 
	 
	 private function scanAndRipAttachmentsFromStructure($structure, $email_number) {
	   
	   	$attachments = array();
	   	
	   	//loop through each part of the message
	        for($i = 0; $i < count($structure->parts); $i++) {
	        
	        
	   	    //an array to store some information about the attachments found
	            /*
		            $attachments[$i] = array(
	                'name' => '',
	                'data' => ''
	            );
	           
	           */
	           
	           //look through the parameters of this part to see if it contains indicators of an attachment (for propertly formatted messages)
	            if($structure->parts[$i]->ifdparameters) {
	                foreach($structure->parts[$i]->dparameters as $object) {
	                	//check_object_for_filename($object);
	                	$attribute = strtolower($object->attribute);
	                	
	                    if($attribute == 'filename' || $attribute == "name") {
	                         
	                        //attachment found!
	                         
	                        $attachments[$i]['name'] = $object->value;
	                         
	                        $attachments[$i]['data'] = imap_fetchbody($this->connection, $email_number, $i+1);
			                
			                if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
			                    $attachments[$i]['data'] = base64_decode($attachments[$i]['data']);
			                } elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
			                    $attachments[$i]['data'] = quoted_printable_decode($attachments[$i]['data']);
			                }
	                         
	                         
	                    }
	                    
	                }
	            }
	           
	            
	            
	            
	            //depending on the authoring environment of the message, attachments may be present in a sub part
	            //of the content, if this is present, check it for the presence of params and dump to the previous
	            //attachment array as a recursive process
	            if($structure->parts[$i]->parts) {
	            	$subAttachments = $this->extractAttachments($structure->parts[$i], $email_number);
	            	
	            	if(is_array($subAttachments) && count($subAttachments)) {
	            		array_push($attachments, $subAttachments);
	            	}
	            }
	            
	            	           
	        }
	       
	    
	   
	    return $attachments;
	   
	}

	
	
	
}