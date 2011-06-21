<?php
class UsersController extends AppController {

	var $name = 'Users';
        var $components = array('Apns','Nard','RequestHandler');
//Resources:
//add - can add new token, and register device_id


        function beforeFilter(){
//	$this->layout = 'response';
          if(in_array($this->action, array('add'))){
	    if(isset($this->params['pass'][0])){
	      $device_token =$this->Nard->sanitize_paranoid_string($this->params['pass'][0],64,64);
              if(('users/add/'.$device_token==$this->params['url']['url'])&&($salt=$this->Nard->generateSaltForDeviceToken($device_token))){ //
		if($this->user_id=$this->Nard->AuthenticateRequest($this->params,'device_token')){
		  $this->Nard->registerDevice($this->params, $salt['salt']);
	          $pushparams = array('alert'=>time(),'type'=>2, 'event'=>0, 'id'=>0, 'userid'=>$this->user_id);
                  $tokenList = array();
                  array_push($tokenList,$device_token);
                  $this->Apns->push($tokenList,$this->Apns->createMessage($pushparams));
		} else {
                  $pushparams = array('time'=>time(),'type'=>1, 'event'=>0, 'id'=>0, 'salt'=>$salt['salt'],'userid'=>$salt['id'],'hash'=>'MD5');
                  $tokenList = array();
                  array_push($tokenList,$device_token);
                  $this->Apns->push($tokenList,$this->Apns->createMessage($pushparams));
	        }
	      } else { //url with device token does not match cleaned device_token
                $this->Security->blackHole($this);
	      }
	    } else { //does not have device_token
	      $this->Security->blackHole($this);
 	    }
	   } else if(in_array($this->action, array('view')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){         //get individual user by user id
               if(!($user=$this->User->getuser($this->params['pass'][0]))){
                 $this->Security->blackHole($this);
               } else{
                 $this->set(compact('user')); //display $message to view
	       }

	    } else if(in_array($this->action, array('edit')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){         //get individual user by user id
              if(!($user=$this->User->editUser(json_decode($this->params['form']['body']), $this->user_id))){
	      $this->Security->blackHole($this);
            } else{
	      if ($userList=$this->User->getContactList($this->user_id)){
		$pushparams = array('alert'=>time(), 'event'=>2, 'type'=>2, 'id'=>$this->user_id);
		while(list($key,$u) = each($userList)){
                    $this->Apns->pushViaUserId($u,$this->Apns->createMessage($pushparams));
                }
	      }
             $this->set(compact('user')); //display $message to view
            }

	   } else if(in_array($this->action, array('listing')) && $this->user_id=$this->Nard->AuthenticateRequest($this->params,'user_id')){         //get list of user by user id
            if(!($user=$this->User->getUserByList( $this->Nard->userIdExist(json_decode($this->params['form']['body'])->{'contactList'}) , $this->user_id))){
              $this->Security->blackHole($this);
            } else{
	      $this->set(compact('user')); //display $message to view
            }

	  } else {
            $this->Security->blackHole($this);
          }


        } //end function beforeFilter()


	function edit($id){

	}
	function add($id){
	}	
	function view($id){
	}
	function listing(){
	}
}
?>
