<?php
class User extends AppModel {

	var $name = 'User';
	var $validate = array(
		'username' => array('notempty'),
		'password' => array('notempty'),
		'device_id' => array('alphanumeric'),
		'device_token' => array('alphanumeric')
	);

	function getUser($user_id = null){
	$user = array();

	 $ck = 'viewgetuser_'.$user_id;
	 if(!$contact = Cache::read($ck,'userauth')){
	   if($contact = $this->find('first', array('fields'=>array('id', 'username','salt','pin','picture','status','fullname','email','device_token'),'conditions'=>array('User.id'=>$user_id)))){
	     Cache::write($ck,$contact,'userauth');
	     $contact['User']['salt']='';
	     array_push($user,$contact);
	   }
	 } else if ($contact){
	   $contact['User']['salt']='';
	   array_push($user,$contact);
	 }
	  return $user;
	}

        function getUserByList($userList=null, $user_id = null){
          $user = array();

	  while(list($key,$friend_id) = each($userList)){
             $ck = 'viewgetuser_'.$friend_id;
             if(!$friend = Cache::read($ck,'userauth')){
		$friend = $this->find('first', array('fields'=>array('id', 'username','salt','pin','picture','status','fullname','email','device_token'),'conditions'=>array('User.id'=>$friend_id)));
	 	Cache::write($ck,$friend,'userauth');
		$friend['User']['salt']='';
		array_push($user, $friend);
	     } else if($friend){
		$friend['User']['salt']='';
		array_push($user, $friend);
	     }
	  }//end while
          return $user;
        }

	function editUser($parameters, $user_id=null){
	if($user_id==$parameters->{'id'}){

	      $user['User']['id']=$parameters->{'id'};
	      if(($parameters->{'username'})){
	      $user['User']['username']=$parameters->{'username'};
	      }
	      if(($parameters->{'status'})){
	      $user['User']['status']=$parameters->{'status'};
	      }
	      if(($parameters->{'fullname'})){
	      $user['User']['fullname']=$parameters->{'fullname'};
	      }
	      if(($parameters->{'email'})){
	      $user['User']['email']=$parameters->{'email'};
	      }
	      $this->primaryKey='id';
	      if($this->save($user['User'])){
 		$ck = 'viewgetuser_'.$user_id;
		Cache::delete($ck,'userauth');
		$user=$this->getUser($user_id);
		return $user;
	      } else {
                return false;
	      }
            } else {
	      return false;
	    }
	}
	
	function getSalt($device_token = null){
	  //check if user table contains device_token, if so send salt in user table through apn
	  //if not in user table. create new user, populate device_token and salt. send salt to device
 	  echo "\nresults:"; 
	  
	  //device_token found
	  if($salt=$this->find('first',array('fields'=>array('salt'),'conditions'=>array('User.device_token'=>$device_token)))){
	    //device_token found
	    if($salt['User']['salt']){
	      //Salt exists for device_token
	    } else { 
	      //Salt does not exist for device_token, set the salt and save it to user.
	      $salt['User']['salt'] = $this->generateNewSalt(); 
	      $this->primaryKey='device_token';
	      $this->save($salt['User']);
	      $this->primaryKey='id';
	    }
	    return $salt['User']['salt'];
	  } else { 
	    //device_token not found
	    $salt['User']['device_token']=$device_token;
	    $salt['User']['salt'] = $this->generateNewSalt();
	    $this->primaryKey='device_token'; //set primary key to device_token to get accurate count to see if exists.
	    $this->save($salt['User']);
	    $this->primaryKey='id'; //set primary key back to ID. see if this can be removed later.
	    return $salt['User']['salt'];
	  }
	}

	function getSignature($salt =null, $msg=null){
	  return sha1($salt.sha1($salt.$msg));
	}

	function registerDevice($device_token=null, $salt=null, $device_id=null){
	  
	  $results = $this->find('first',array('fields'=>array('salt','device_token','device_id'), 'conditions'=>array('User.device_id'=>$device_id)));
	  if(!$results){ 
	    //populate empty device_id for valid device_token and salt
	    $results['User']['device_token']=$device_token;
	    $results['User']['device_id']=$device_id;
	    $this->primaryKey='device_token';
	    $this->save($results['User']);
	    $this->primaryKey='id';
	  } else if(($results['User']['device_token']==$device_token)&&($results['User']['salt']==$salt)){ //device_id exists and device_token and salt matches
	    
	    //do nothing salt, device_id, device_token are valid.
	  } else if(($results['User']['device_token']!=$device_token)||($results['User']['salt']!=$salt)){
	    //delete all existing tokens, find device_id, and update it with the correct token and salt value.
	    $this->primaryKey='device_token'; 
	    $this->delete($device_token);
	    $this->primaryKey='device_id';
	    $results['User']['device_token']=$device_token;
            $results['User']['salt']=$salt;
	    $results['User']['device_id']=$device_id;
            $this->save($results['User']);
            $this->primaryKey='id';
	  }
	  
	}
	function getContactList($user_id=null){
          $ck = 'contactlist_'.$user_id;
	  if(!$userArray = Cache::read($ck,'contacts')){
	    $this->bindModel(array('belongsTo'=> array( 'Contact' => array(
                        'className' => 'Contact',
                        'foreignKey' => '',
                        'conditions' => '',
                        'fields' => '',
                        'order' => ''
            ))));
	    if($userList = $this->Contact->find('all',array('fields'=>array(),'conditions'=>array('Contact.friend_id'=>$user_id)))){
              $userArray=array();
	      while(list($key,$contact) = each($userList)){
	        if($contact['Contact']['blocked_by_friend']==0 && $contact['Contact']['blocking_friend']==0 && $contact['Contact']['friend_accepted']==1){
	          array_push($userArray,$contact['Contact']['user_id']);
	        }
              }
	      $this->unbindModel(array('belongsTo'=>array('Contact'))); //unbind model so it doesnt need to join Thread or Contact

	      Cache::write($ck,$userArray,'contacts');
	      return $userArray;
	    } else {
	      return false;
	    }
	  } else if ($userArray) {
              return $userArray;
	  }
	}

	function allowRequest($request = null, $signature = null,$device_token=null){
	}

	function generateNewSalt(){
          return substr( str_pad( dechex( mt_rand() ), 8, '0', STR_PAD_LEFT ), -8 );
	}

	function sanitize_paranoid_string($string, $min='', $max='')
	{
	  $string = preg_replace("/[^a-zA-Z0-9]/", "", $string);
	  $len = strlen($string);
	  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
	    return FALSE;
	  return $string;
	}
}
?>
