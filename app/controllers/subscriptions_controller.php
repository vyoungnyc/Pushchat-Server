<?php
class SubscriptionsController extends AppController {

	var $name = 'Subscriptions';
	var $paginate = array('limit' => 15, 'page' => 1); 

        var $components = array('Apns','Nard','RequestHandler');
        function beforeFilter(){
          if(in_array($this->action, array('add'))&&$this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){			//subscribe current user to subscription/thread
	    if(!isset($this->params['form']['body'])){
                  $this->Security->blackHole($this);
            } else {

	      if($recipientList=$this->Subscription->validateContacts($this->user_id, $this->Nard->userIdExist(json_decode($this->params['form']['body'])->{'recipientList'}))){
		if ($subscription= $this->Subscription->initiateSubscription($recipientList)){
		$sub = $subscription[0];
		$subarray=array();
		while(list($key,$s)=each($subscription)){
		 $temparray['s']=$s['Subscription']['id'];
		 $temparray['u']=$s['Subscription']['user_id'];
		 array_push($subarray,$temparray);
		 //array_push($subarray,$s['Subscription']['id']);
		}
                  $this->set(compact('subscription'));
		  $userList=$this->Apns->getUsersByThread($this->user_id,$sub['Thread']['id']);
		  $pushparams = array('alert'=>time(), 'event'=>3, 'type'=>1, 'id'=>$this->user_id, 'subarray'=>$subarray, 'threadid'=>$sub['Thread']['id'], 'threadactive'=>$sub['Thread']['active']);
                  while(list($key,$user) = each($userList)){
                    $this->Apns->pushViaUserId($user,$this->Apns->createMessage($pushparams));
                  }


                } else {
                  $this->Security->blackHole($this);
                }
	      } else {//contacts dont validate(not friends)
		$this->Security->blackHole($this);
	      }
	    }




          } else if(in_array($this->action, array('inviteContacts'))&&$this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){ 
            if(!isset($this->params['form']['body'])){
                  $this->Security->blackHole($this);
            } else {

              if($recipientList=$this->Subscription->validateContacts($this->user_id, $this->Nard->userIdExist(json_decode($this->params['form']['body'])->{'recipientList'}))){
                if($subscription= $this->Subscription->subscribeFriendToThread($this->user_id,$recipientList,$this->params['pass'][0])){
                  $this->set(compact('subscription'));
                  $sub = $subscription[0];
                  $subarray=array();
                  while(list($key,$s)=each($subscription)){
                    $temparray['s']=$s['Subscription']['id'];
                    $temparray['u']=$s['Subscription']['user_id'];
                    array_push($subarray,$temparray);
                  }
		  $this->set(compact('subscription'));
                  $userList=$this->Apns->getUsersByThread($this->user_id,$sub['Subscription']['thread_id']);
		  $pushparams = array('alert'=>time(), 'event'=>3, 'type'=>1, 'id'=>$this->user_id, 'subarray'=>$subarray, 'threadid'=>$sub['Subscription']['thread_id'],'threadactive'=>1);
                
		  while(list($key,$user) = each($userList)){
                    $this->Apns->pushViaUserId($user,$this->Apns->createMessage($pushparams));
                  }

                } else {
                  $this->Security->blackHole($this);
                }
              } else {//contacts dont validate(not friends)
                $this->Security->blackHole($this);
              }
            }

           } else if(in_array($this->action, array('thread')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){     //get all subscriptions for thread
              if(!($thread=$this->Subscription->getThread($this->params['pass'][0]))){
                $this->Security->blackHole($this);
              } else{
              $this->set(compact('thread')); //display $message to view
              }
           } else if(in_array($this->action, array('view')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){     //get all subscription 
              if(!($thread=$this->Subscription->getThread($this->params['pass'][0]))){
                $this->Security->blackHole($this);
              } else{
              $this->set(compact('thread')); //display $message to view
              }


	  } else if(in_array($this->action, array('threads')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){	//get all subscriptions for current user
              if(!($threads=$this->Subscription->getThreads($this->user_id))){
                $this->Security->blackHole($this);
              } else{
              $this->set(compact('threads')); //display $message to view
              }
          } else if(in_array($this->action, array('subscribers')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){   //get all subscribers in thread
            if(!($users=$this->Subscription->getUsersByThread($this->user_id,$this->params['pass'][0]))){
              $this->Security->blackHole($this);
            } else{
             $this->set(compact('users')); //display $message to view
            }

	 } else if(in_array($this->action, array('unsubscribe')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){	//unsubscribe current user in thread
            if(!($thread=$this->Subscription->unsubscribeThread($this->user_id,$this->params['pass'][0]))){
              $this->Security->blackHole($this);
            } else{ 
             $this->set(compact('thread')); //display $message to view
                  $userList=$this->Apns->getUsersByThread($this->user_id,$thread['Thread']['id']);
                  $pushparams = array('alert'=>time(), 'event'=>3, 'type'=>2, 'id'=>$this->user_id, 'threadid'=>$thread['Thread']['id']);
                  while(list($key,$user) = each($userList)){
                    $this->Apns->pushViaUserId($user,$this->Apns->createMessage($pushparams));
                  }


            }
	  } else if(in_array($this->action, array('typing')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'device_id')){   //get all subscribers in thread
	      if( (isset($this->params['form']['body'])) && ($device_token = json_decode($this->params['form']['body'])->{'device_token'}) ){
              //get device_token, get user_id, get thread_id to create message for APNS, and send to APNS telling it to update flag for user_id is typing in thread_id
		  $pushparams = array('alert'=>time(),'type'=>2, 'event'=>0, 'id'=>$this->user_id, 'typing'=>1);
                  $tokenList = array();
                  array_push($tokenList,$device_token);
                  $this->Apns->push($tokenList,$this->Apns->createMessage($pushparams));
	      } else {
		$this->Security->blackHole($this);
	      }
          } else {
            $this->Security->blackHole($this); //not a valid action or cannot authenticate
          }
        }

	function typing($id){
	}
	function thread($id){
	}
	function view($id){
	}
	function index(){
	}
	function threads(){
	}
	function subscribers($id){
	}
	function unsubscribe($id){
	}
	function inviteContacts($id){
	}
        function add(){
	}

}
?>
