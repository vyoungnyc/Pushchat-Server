<?php
class ContactsController extends AppController {

	var $name = 'Contacts';
	var $components = array('Nard','RequestHandler','Apns');

//Resources:
//addFriend - send a request to add a contact
//acceptFriend - accept pending request
//index - get contact list
//blockContact - block contact
//unblockContact - unblock contact
//declineContact - do not accept pending request

        function beforeFilter(){
          if(in_array($this->action, array('addFriend'))&&$this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){
		if(!isset($this->params['form']['body'])){
  	          $this->Security->blackHole($this);
		}
          } else if(in_array($this->action, array('acceptFriend')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){
                if(!isset($this->params['form']['body'])){
                  $this->Security->blackHole($this);
                }
	  } else if(in_array($this->action, array('index')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){
	  } else if(in_array($this->action, array('blockContact')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){
 	  } else if(in_array($this->action, array('unblockContact')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){
	  } else if(in_array($this->action, array('declineContact')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){
          } else {
            $this->Security->blackHole($this); //not a valid action or cannot authenticate
          }
        }




	function index(){
	  if($contact = $this->Contact->getContacts($this->user_id)){
            $this->set(compact('contact'));
          } else {
            $this->Security->blackHole($this);
          }
	}

        function declineContact($id){
	  if($contact = $this->Contact->declineContact($this->user_id, $this->Nard->userIdExist($this->params['pass'][0]))){
/** May not even need to send notifications for declining
	    $c =$contact[0];
            $pushparams = array('alert'=>time(), 'event'=>4, 'type'=>1, 'id'=>$this->user_id, 'cid'=>$c['Contact']['id'], 'userid'=>$c['Contact']['user_id'], 'friend_id'=>$c['Contact']['friend_id'],'friend_accepted'=>$c['Contact']['friend_accepted'], 'request_notification'=>$c['Contact']['request_notification'], 'blocked_by_friend'=>$c['Contact']['blocked_by_friend'], 'blocking_friend'=>$c['Contact']['blocking_friend'] );
            $this->Apns->pushViaUserId($c['Contact']['user_id'],$this->Apns->createMessage($pushparams));

   */
         $this->set(compact('contact'));

	  } else {
	    $this->Security->blackHole($this);
	  }        
	}
        function addFriend(){
         if($contact = $this->Contact->addContacts($this->user_id, $this->Nard->userIdExist(json_decode($this->params['form']['body'])->{'recipientList'}))){
	    $c =$contact[0];
            $pushparams = array('alertc'=>'Received contact request!', 'event'=>4, 'type'=>1, 'id'=>$this->user_id, 'cid'=>$c['Contact']['id'], 'userid'=>$c['Contact']['user_id'], 'friend_id'=>$c['Contact']['friend_id'],'friend_accepted'=>$c['Contact']['friend_accepted'], 'request_notification'=>$c['Contact']['request_notification'], 'blocked_by_friend'=>$c['Contact']['blocked_by_friend'], 'blocking_friend'=>$c['Contact']['blocking_friend'] );
	    $this->Apns->pushViaUserId($c['Contact']['friend_id'],$this->Apns->createMessage($pushparams));
	    $this->set(compact('contact'));
	  } else {
	    $this->Security->blackHole($this);
	  }
        }

        function blockContact($id){
	  if($contact = $this->Contact->blockContact($this->user_id, $this->Nard->userIdExist($this->params['pass'][0]))){

	if(count($contact)==1){
	  $c =$contact[0];
          $pushparams = array('alert'=>time(), 'event'=>4, 'type'=>1, 'id'=>$this->user_id, 'cid'=>$c['Contact']['id'], 'userid'=>$c['Contact']['user_id'], 'friend_id'=>$c['Contact']['friend_id'],'friend_accepted'=>$c['Contact']['friend_accepted'], 'request_notification'=>$c['Contact']['request_notification'], 'blocked_by_friend'=>$c['Contact']['blocked_by_friend'], 'blocking_friend'=>$c['Contact']['blocking_friend'] );
	} else if (count($contact) ==2){
	    $t1 =$contact[0];
	    $t2 =$contact[1];	
	    $c1['id']=$t1['Contact']['id'];
	    $c1['uid']=$t1['Contact']['user_id'];
            $c1['fid']=$t1['Contact']['friend_id'];
            $c1['fa']=$t1['Contact']['friend_accepted'];
            $c1['rn']=$t1['Contact']['request_notification'];
            $c1['bbf']=$t1['Contact']['blocked_by_friend'];
            $c1['bf']=$t1['Contact']['blocking_friend'];
            $c2['id']=$t2['Contact']['id'];
            $c2['uid']=$t2['Contact']['user_id'];
            $c2['fid']=$t2['Contact']['friend_id'];
            $c2['fa']=$t2['Contact']['friend_accepted'];
            $c2['rn']=$t2['Contact']['request_notification'];
            $c2['bbf']=$t2['Contact']['blocked_by_friend'];
            $c2['bf']=$t2['Contact']['blocking_friend'];
            $pushparams = array('alert'=>time(), 'event'=>4, 'type'=>2, 'id'=>$this->user_id, 'ca1'=>$c1, 'ca2'=>$c2);
	} 


	    $this->Apns->pushViaUserId($this->params['pass'][0],$this->Apns->createMessage($pushparams));

	  $this->set(compact('contact'));
	  } else {
	    $this->Security->blackHole($this);
	  }
        }

        function unblockContact($id){
          if($contact = $this->Contact->unblockContact($this->user_id, $this->Nard->userIdExist($this->params['pass'][0]))){
            

        if(count($contact)==1){
          $c =$contact[0];
          $pushparams = array('alert'=>time(), 'event'=>4, 'type'=>1, 'id'=>$this->user_id, 'cid'=>$c['Contact']['id'], 'userid'=>$c['Contact']['user_id'], 'friend_id'=>$c['Contact']['friend_id'],'friend_accepted'=>$c['Contact']['friend_accepted'], 'request_notification'=>$c['Contact']['request_notification'], 'blocked_by_friend'=>$c['Contact']['blocked_by_friend'], 'blocking_friend'=>$c['Contact']['blocking_friend'] );
        } else if (count($contact) ==2){
            $t1 =$contact[0];
            $t2 =$contact[1];
            $c1['id']=$t1['Contact']['id'];
            $c1['uid']=$t1['Contact']['user_id'];
            $c1['fid']=$t1['Contact']['friend_id'];
            $c1['fa']=$t1['Contact']['friend_accepted'];
            $c1['rn']=$t1['Contact']['request_notification'];
            $c1['bbf']=$t1['Contact']['blocked_by_friend'];
            $c1['bf']=$t1['Contact']['blocking_friend'];
            $c2['id']=$t2['Contact']['id'];
            $c2['uid']=$t2['Contact']['user_id'];
            $c2['fid']=$t2['Contact']['friend_id'];
            $c2['fa']=$t2['Contact']['friend_accepted'];
            $c2['rn']=$t2['Contact']['request_notification'];
            $c2['bbf']=$t2['Contact']['blocked_by_friend'];
            $c2['bf']=$t2['Contact']['blocking_friend'];
            $pushparams = array('alert'=>time(), 'event'=>4, 'type'=>2, 'id'=>$this->user_id, 'ca1'=>$c1, 'ca2'=>$c2);
        }    


            $this->Apns->pushViaUserId($this->params['pass'][0],$this->Apns->createMessage($pushparams));

	    $this->set(compact('contact'));
          } else {
            $this->Security->blackHole($this);
          }
	}


        function acceptFriend(){
          if($contact = $this->Contact->AcceptContacts($this->user_id, $this->Nard->userIdExist(json_decode($this->params['form']['body'])->{'recipientList'}))){
          
        if(count($contact)==1){
          $c =$contact[0];
  	  if($this->user_id==$c['Contact']['user_id']){
	    $uid=$c['Contact']['friend_id'];
	  } else if ($this->user_id==$c['Contact']['friend_id']){
	    $uid=$c['Contact']['user_id'];
	  }

          $pushparams = array('alert'=>1, 'event'=>4, 'type'=>1, 'id'=>$this->user_id, 'cid'=>$c['Contact']['id'], 'userid'=>$c['Contact']['user_id'], 'friend_id'=>$c['Contact']['friend_id'],'friend_accepted'=>$c['Contact']['friend_accepted'], 'request_notification'=>$c['Contact']['request_notification'], 'blocked_by_friend'=>$c['Contact']['blocked_by_friend'], 'blocking_friend'=>$c['Contact']['blocking_friend'] );
        } else if (count($contact) ==2){



            $t1 =$contact[0];
            $t2 =$contact[1];
          if($this->user_id==$t1['Contact']['user_id']){
            $uid=$t1['Contact']['friend_id'];
          } else if ($this->user_id==$t1['Contact']['friend_id']){
            $uid=$t1['Contact']['user_id'];
          }

            $c1['id']=$t1['Contact']['id'];
            $c1['uid']=$t1['Contact']['user_id'];
            $c1['fid']=$t1['Contact']['friend_id'];
            $c1['fa']=$t1['Contact']['friend_accepted'];
            $c1['rn']=$t1['Contact']['request_notification'];
            $c1['bbf']=$t1['Contact']['blocked_by_friend'];
            $c1['bf']=$t1['Contact']['blocking_friend'];
            $c2['id']=$t2['Contact']['id'];
            $c2['uid']=$t2['Contact']['user_id'];
            $c2['fid']=$t2['Contact']['friend_id'];
            $c2['fa']=$t2['Contact']['friend_accepted'];
            $c2['rn']=$t2['Contact']['request_notification'];
            $c2['bbf']=$t2['Contact']['blocked_by_friend'];
            $c2['bf']=$t2['Contact']['blocking_friend'];
            $pushparams = array('alert'=>1, 'event'=>4, 'type'=>2, 'id'=>$this->user_id, 'ca1'=>$c1, 'ca2'=>$c2);
        }
            $this->Apns->pushViaUserId($uid,$this->Apns->createMessage($pushparams));
          $this->set(compact('contact'));
	  } else {
	    $this->Security->blackhole($this);
	  }
        }



}
?>
