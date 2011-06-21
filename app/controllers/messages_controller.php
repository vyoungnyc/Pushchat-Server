<?php

class MessagesController extends AppController {
//TODO: create APNS method to take in thread_id, and message


	var $name = 'Messages';
	var $components = array('Nard','RequestHandler','Apns');

	function beforeFilter(){
	if(in_array($this->action, array('add'))&&$this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){  			//add new message to thread
	   $body = 'time is: '.time();
	     if(!($message =$this->Message->addMessageToThread($this->user_id, $this->params['pass'][0], json_decode($this->params['form']['body'])->{'messageBody'} ))){
              $this->Security->blackHole($this);
            } else{
	     $this->set(compact('message')); //display $message to view
	     
	     $userList=$this->Apns->getUsersByThread($this->user_id,$this->params['pass'][0]);
	     $msg=$message[0];
	     $pushparams = array('alert1'=>'You have a new message!','type'=>2, 'event'=>1, 'id'=>$this->user_id, 'msgid'=>$msg['Message']['id'], 'msgread'=>$msg['Message']['read']);
	     while(list($key,$user) = each($userList)){
	       $this->Apns->pushViaUserId($user,$this->Apns->createMessage($pushparams));	     	   
	     }  
	
            }
	  } else if(in_array($this->action, array('view'))&& $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id') ){         //get individual message by message id 
	    if(!($message=$this->Message->getMessage($this->user_id,$this->params['pass'][0]))){
              $this->Security->blackHole($this);
            } else{
             $this->set(compact('message')); //display $message to view
	     $msg = $message[0];
             $userList=$this->Apns->getUsersByThread($this->user_id,$msg['Message']['thread_id']);
             $pushparams = array('alert'=>time(),'type'=>2, 'event'=>1, 'id'=>$this->user_id, 'msgid'=>$msg['Message']['id'],'msgread'=>$msg['Message']['read']);
             while(list($key,$user) = each($userList)){
               $this->Apns->pushViaUserId($user,$this->Apns->createMessage($pushparams));
             }

            }
	  } else if(in_array($this->action, array('thread')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){	//get all messages in thread
	    if(!($message=$this->Message->getMessagesByThread($this->user_id,$this->params['pass'][0]))){
              $this->Security->blackHole($this);
            } else{
		$message = array_reverse($message);
             $this->set(compact('message')); //display $message to view
            }
          } else {
            $this->Security->blackHole($this); //not a valid action or cannot authenticate
          }
	}
	function add(){
	}
	function index(){
	}
	function view($id){
	}
	function thread($id){
	}
}
?>
