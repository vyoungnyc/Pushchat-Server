<?php
	App::import('Sanitize');
	class NardComponent extends Object{
//TODO: modify getSignature and validate/authenticate request to accept md5 or sha1 for hmac

		


	function generateSaltForDeviceToken($device_token = null){
          //check if user table contains device_token, if so send salt in user table through apn
          //if not in user table. create new user, populate device_token and salt. send salt to device
//          echo "\nresults:";

          //device_token found
	  $userInstance = ClassRegistry::init('User');
	  $ck = 'viewgetuser_token_'.$device_token;
          if(!$salt = Cache::read($ck,'userauth')){

            if($salt=$userInstance->find('first',array('fields'=>array('id','salt'),'conditions'=>array('User.device_token'=>$device_token)))){
              //device_token found
              if($salt['User']['salt']){
                //Salt exists for device_token
              } else {
                //Salt does not exist for device_token, set the salt and save it to user.
                $salt['User']['salt'] = $this->generateNewSalt();
                $userInstance->primaryKey='device_token';
                $userInstance->save($salt['User']);
                $userInstance->primaryKey='id';
		//print_r($userInstance);
              }
	    
	      Cache::write($ck,$salt,'userauth');
	      return $salt['User'];

            } else {
              //device_token not found
              $salt['User']['device_token']=$device_token;
              $salt['User']['salt'] = $this->generateNewSalt();
              $userInstance->primaryKey='device_token'; //set primary key to device_token to get accurate count to see if exists.
              $userInstance->save($salt['User']);
              $userInstance->primaryKey='id'; //set primary key back to ID. see if this can be removed later.
	      Cache::write($ck,$salt,'userauth');
              return $salt['User'];
            }
          } else if ($salt) { //cache hit successful
	   //device_token found
	      if($salt['User']['salt'] && $salt['User']['id'] ){
                return $salt['User']; //Salt exists for device_token
              } else {
                //Salt does not exist for device_token, set the salt and save it to user.
                $salt['User']['salt'] = $this->generateNewSalt();
                $userInstance->primaryKey='device_token';
                $userInstance->save($salt['User']);
                $userInstance->primaryKey='id';
		Cache::delete($ck,'userauth');
		return $salt['User'];

              }
            } else {
              //device_token not found
              $salt['User']['device_token']=$device_token;
              $salt['User']['salt'] = $this->generateNewSalt();
              $userInstance->primaryKey='device_token'; //set primary key to device_token to get accurate count to see if exists.
              $userInstance->save($salt['User']);
              $userInstance->primaryKey='id'; //set primary key back to ID. see if this can be removed later.
	      Cache::delete($ck,'userauth');
		return $salt['User'];
 
            }
 

	}

        function registerDevice($params=null, $salt=null){
	  $device_token = $params['pass'][0];
	  $device_id = $params['url']['device_id'];
          $userInstance = ClassRegistry::init('User');
          $ck = 'viewgetuser_register_did_'.$device_id;
          if(!$results = Cache::read($ck,'userauth')){
	    $results = $userInstance->find('first',array('fields'=>array('salt','device_token','device_id'), 'conditions'=>array('User.device_id'=>$device_id)));

            if(!$results){
              //if device_id not found populate empty device_id for valid device_token and salt already created/
              $userInstance->primaryKey='device_token';
	      $results['User']['device_token']=$device_token;
              $results['User']['device_id']=$device_id;
              $userInstance->save($results['User']);
              $userInstance->primaryKey='id';
            } else if(($results['User']['device_token']==$device_token)&&($results['User']['salt']==$salt)){ //device_id exists and device_token and salt matches
              //do nothing device_id found and salt+device_token are valid.
	    } else if(($results['User']['device_token']!=$device_token)||($results['User']['salt']!=$salt)){
              //delete all existing tokens for device_id, find device_id, and update it with the correct token and salt value.
              $userInstance->primaryKey='device_token';
              $userInstance->delete($device_token);
              $userInstance->primaryKey='device_id';

              $results['User']['device_token']=$device_token;
              $results['User']['salt']=$salt;
              $results['User']['device_id']=$device_id;
              $userInstance->save($results['User']);
              $userInstance->primaryKey='id';

            }
	    $results = $userInstance->find('first',array('fields'=>array('id','salt','device_token','device_id'), 'conditions'=>array('User.device_id'=>$device_id)));
	    Cache::write($ck,$results,'userauth');
            $ck = 'viewgetuser_token_'.$device_token;
            Cache::delete($ck,'userauth');
	    $ck = 'viewgetuser_'.$results['User']['id'];
            Cache::delete($ck,'userauth');

          } else if ($results) {
	      if(($results['User']['device_token']!=$device_token)||($results['User']['salt']!=$salt)){
                //delete all existing tokens for device_id, find device_id, and update it with the correct token and salt value.
                $userInstance->primaryKey='device_token';
                $userInstance->delete($device_token);
                $userInstance->primaryKey='device_id';
                $results['User']['device_token']=$device_token;
                $results['User']['salt']=$salt;
                $results['User']['device_id']=$device_id;
                $userInstance->save($results['User']);
                $userInstance->primaryKey='id';
                $results = $userInstance->find('first',array('fields'=>array('id','salt','device_token','device_id'), 'conditions'=>array('User.device_id'=>$device_id)));
                Cache::write($ck,$results,'userauth');
		$ck = 'viewgetuser_'.$results['User']['id'];
		Cache::delete($ck,'userauth');
                $ck = 'viewgetuser_token_'.$device_token;
                Cache::delete($ck,'userauth');

              }

	  }


	}



	        function getSignature($salt =null, $msg=null){
        	  return hash_hmac('md5',$msg,$salt);
        	}

		function authenticateRequest($params,$type){
//return true;
//                  echo "<br/>params are here:";
//            	  print_r($params);
//            	  echo "<br/>";
            	  if(isset($params['url']['device_id'])&&isset($params['url']['expire'])&&isset($params['url']['s'])){
                    if(isset($params['form']['body'])){
                      $msg = $params['url']['expire'].':'.$params['url']['url'].':'.$params['url']['device_id'].':'.$params['form']['body'];
                    } else {
		      $msg = $params['url']['expire'].':'.$params['url']['url'].':'.$params['url']['device_id'];
                    }
                    
                    if($type=='device_id'){
//                      $salt = $this->getSalt($params['url']['device_id']);
//		      echo "<br/>".$salt['salt']." is salt <br/>";
//		      echo "msg:".$msg." has signature: ".$this->getSignature($salt['salt'],$msg)." and time: ".time();
//		      echo "<br/>";

		      if($user_id = $this->validateRequest($params['url']['device_id'], $msg, $params['url']['expire'], $params['url']['s'],$type)){
                        return $user_id;
                      } else { 
			return false;
		      }
                    } else if ($type=='device_token'){
//		      $salt = $this->getSaltByToken($params['pass'][0]);
//		      echo "<br/>".$salt['salt']." is salt <br/>";
//		      echo "msg:".$msg." has signature: ".$this->getSignature($salt['salt'],$msg)." and time: ".time();
//		      echo "<br/>".$params['pass'][0];
		      if($user_id = $this->validateRequest($params['pass'][0], $msg, $params['url']['expire'], $params['url']['s'],$type)){
			return $user_id;
                      } else {
                        return false;
                      }
                    }

              	  } else if(isset($params['url']['user_id'])&&isset($params['url']['expire'])&&isset($params['url']['s'])){
		    if(isset($params['form']['body'])){
 		      $msg = $params['url']['expire'].':'.$params['url']['url'].':'.$params['url']['user_id'].':'.$params['form']['body'];
		    } else {
		      $msg = $params['url']['expire'].':'.$params['url']['url'].':'.$params['url']['user_id'];
                    }
		    if($type=='user_id'){
//                      $salt = $this->getSaltByUserId($params['url']['user_id']);
//		    echo "<br/>".$salt['salt']." is salt <br/>";
//		    echo "msg:".$msg." has signature: ".$this->getSignature($salt['salt'],$msg)." and time: ".time();
//		    echo "<br/>";

                      if($user_id = $this->validateRequest($params['url']['user_id'], $msg, $params['url']['expire'], $params['url']['s'],$type)){
			return $user_id;
                      } else { 
                        return false;
                      }
		    }
		  }
		  return false; //wasnt able to get user_id
		}

		function validateRequest($device_idToken, $msg, $expire, $signature,$type){
		  if($this->sanitizeTime($expire,strlen(time()-310),strlen(time()+310)) && (
		    (($type=='device_id')&&($this->sanitize_paranoid_string($device_idToken,40,40)))||
		    (($type=='user_id')&&($this->sanitize_paranoid_number($device_idToken,1,10))) ||
		    (($type=='device_token')&&($this->sanitize_paranoid_string($device_idToken,64,64))))&&
		    $this->sanitize_paranoid_string($signature,32,32)&&$this->sanitize_paranoid_string($msg,1,2000)){// MD5 = 32, SHA1 = 40
	 	    if($type=='device_id'){
		      $salt = $this->getSalt($device_idToken);
		    } else if ($type=='device_token'){
		      $salt = $this->getSaltByToken($device_idToken);
		    } else if ($type=='user_id'){
		      $salt = $this->getSaltByUserId($device_idToken);
		    }
		   if($salt['salt'] && $signature==$this->getSignature($salt['salt'],$msg)){
		     return $salt['id'];
		    } else { 
		      return false;
		    }
		  } 
		  return false;
		}		

		function userIdExist($user_id){
		  $userInstance = ClassRegistry::init('User');
		  $validIds = array();
		  if(!is_array($user_id)){
		    $ck = 'viewgetuser_'.$user_id;
		    if(!$result = Cache::read($ck,'userauth')){
		      $result = $userInstance->find('first', array('fields'=>array('id', 'username','salt','pin','picture','status','fullname','email','device_token'),'conditions'=>array('User.id'=>$user_id)));
                      if($result){
                        Cache::write($ck,$result,'userauth');
                        array_push($validIds,$result['User']['id']);
                        }
		    } else if ($result){
                      array_push($validIds,$result['User']['id']);
                    }
		  } else if(is_array($user_id)){
		    while (list($key,$value) = each($user_id)){
		      $ck = 'viewgetuser_'.$value;
		      if(!$result = Cache::read($ck,'userauth')){
                        $result = $userInstance->find('first', array('fields'=>array('id', 'username','salt','pin','picture','status','fullname','email','device_token'),'conditions'=>array('User.id'=>$value)));
		        if($result){
                          Cache::write($ck,$result,'userauth');
                          array_push($validIds,$result['User']['id']);
		        }
		      } else if ($result){
			array_push($validIds,$result['User']['id']);
		      }
		    }//end while
		  }

		 return $validIds;
		}

		function getSalt($device_id){
		  $userInstance = ClassRegistry::init('User');
		  $ck = 'viewgetuser_deviceid_'.$device_id;
		  if(!$salt = Cache::read($ck,'userauth')){
		    $salt = $userInstance->find('first', array('fields'=>array('id','salt'),'conditions'=>array('User.device_id'=>$device_id)));
		    if($salt){
             		Cache::write($ck,$salt,'userauth');
                        return $salt['User'];
                    }
		  } else if ($salt){
                    return $salt['User'];
                  }



		}

                function getSaltByToken($device_token){
                  $userInstance = ClassRegistry::init('User');
		  $ck = 'viewgetuser_token_'.$device_token;
                  if(!$salt = Cache::read($ck,'userauth')){
                    $salt = $userInstance->find('first', array('fields'=>array('id','salt'),'conditions'=>array('User.device_token'=>$device_token)));
                    if($salt){
                        Cache::write($ck,$salt,'userauth');
                        return $salt['User'];
                    }
                  } else if ($salt){ 
                    return $salt['User'];
                  }

		}

                function getSaltByUserId($user_id=null){
                  $userInstance = ClassRegistry::init('User');
                  $ck = 'viewgetuser_'.$user_id;
		  if(!$salt = Cache::read($ck,'userauth')){ 
		    $salt = $userInstance->find('first', array('fields'=>array('id', 'username','salt','pin','picture','status','fullname','email','device_token'),'conditions'=>array('User.id'=>$user_id)));
		    if($salt){ 
 		        Cache::write($ck,$salt,'userauth');
 			return $salt['User'];
		    } if (!$salt) {
			//do something with null reslts for bad salt
		    }
		  } else if ($salt){
                    return $salt['User'];
		  }
                }



		function getSaltByUser($user_id=null){
		  $userInstance = ClassRegistry::init('User');

		  $ck = 'viewgetuser_'.$user_id;
		  if(!$salt = Cache::read($ck,'userauth')){
                    $salt = $userInstance->find('first', array('fields'=>array('id', 'username','salt','pin','picture','status','fullname','email','device_token'),'conditions'=>array('User.id'=>$user_id)));
                    if($salt){ 
			Cache::write($ck,$salt,'userauth');
			return $salt['User']['salt'];
                    }
		  } else if ($salt){
                    return $salt['User']['salt'];
		  }
		}


		function generateNewSalt(){
        	  return substr( str_pad( dechex( mt_rand() ), 8, '0', STR_PAD_LEFT ), -8 );
	        }

	        function sanitize_paranoid_string($string, $min='', $max='')
        	{
		 $string = str_replace("/[^a-zA-Z0-9]/", "", $string);


          	  $len = strlen($string);
          	  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))){
              	    return FALSE;
		  }
           	  return $string;
        	}

                function sanitize_paranoid_number($string, $min='', $max='')
                {
		$string = str_replace("/[^0-9]/", "", $string);

                  $len = strlen($string);
                  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))){
                    return FALSE;
                  }
                  return $string;
                }


		function sanitizeTime($string, $min='', $max='')
                {
		    $string = str_replace("/[^0-9]/", "", $string);

                  $len = strlen($string);
                  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))){
                    return FALSE;
		  }
/***		  if(($string+300)<time()){
		    return FALSE;
		  }
		  if(($string-300)>time()){
                    return FALSE;
                  }
**/
                  return $string;
                }



	}
?>
