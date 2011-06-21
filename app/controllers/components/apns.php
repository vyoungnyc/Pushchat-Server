<?php
  class ApnsComponent extends Object{

        function getUsersByThread($userId=null,$threadId=null){
	  $subscriptionInstance=ClassRegistry::init('Subscription');
	  $ck = 'getusersbythread_'.$threadId;
          if(!$userList = Cache::read($ck,'subscriptions')){
            if($userList = $subscriptionInstance->find('all',array('fields'=>array('id','user_id', 'thread_id'),'conditions'=>array('Subscription.thread_id'=>$threadId)))){
 	      Cache::write($ck,$userList,'subscriptions');
              $results=array();
              while(list($key,$user) = each($userList)){
                if($userId!=$user['Subscription']['user_id']){
                  array_push($results,$user['Subscription']['user_id']);
                }
	      }
	      return $results;
	    }else {
              return false;
	    }

	  } else if($userList){
	      $results=array();
              while(list($key,$user) = each($userList)){
                if($userId!=$user['Subscription']['user_id']){
                  array_push($results,$user['Subscription']['user_id']);
                }
              }
       	      return $results;
	  } else {
	    return false;
	  }
        }

        function pushAllViaUserId($message){
        $userInstance = ClassRegistry::init('User');
          $ck = 'viewgetuser_'.all;
          if(!$results = Cache::read($ck,'userauth')){

            if($results = $userInstance->find('all',array('fields'=>array('id', 'username','salt','pin','picture','status','fullname','email','device_token')))){
              Cache::write($ck,$results,'userauth');
              $memcache_obj=memcache_connect('192.168.133.71',21211);
              if($data['payload']=$message){
                $data['deviceToken']=$results['User']['device_token'];
                $output = json_encode($data);
                memcache_set($memcache_obj,'pushqueue',$output,0,0);
              }
              memcache_close($memcache_obj);
            }
          } else if ($results){
              $memcache_obj=memcache_connect('192.168.133.71',21211);
              if($data['payload']=$message){
                $data['deviceToken']=$results['User']['device_token'];
                $output = json_encode($data);
                memcache_set($memcache_obj,'pushqueue',$output,0,0);
              }
              memcache_close($memcache_obj);

          }

        }

	function pushViaUserId($user_id,$message){
	$userInstance = ClassRegistry::init('User');
          $ck = 'viewgetuser_'.$user_id;
          if(!$results = Cache::read($ck,'userauth')){

	    if($results = $userInstance->find('first',array('fields'=>array('id', 'username','salt','pin','picture','status','fullname','email','device_token'), 'conditions'=>array('User.id'=>$user_id)))){
	      Cache::write($ck,$results,'userauth');
	      $memcache_obj=memcache_connect('192.168.133.71',21211);
	      if($data['payload']=$message){
                $data['deviceToken']=$results['User']['device_token'];
                $output = json_encode($data);
                memcache_set($memcache_obj,'pushqueue',$output,0,0);
              }
	      memcache_close($memcache_obj);
	    }
	  } else if ($results){
              $memcache_obj=memcache_connect('192.168.133.71',21211);
              if($data['payload']=$message){
                $data['deviceToken']=$results['User']['device_token'];
                $output = json_encode($data);
                memcache_set($memcache_obj,'pushqueue',$output,0,0);
              }
              memcache_close($memcache_obj);

	  }
	
	}	
	function push($device_token,$message){
	  $memcache_obj = memcache_connect('192.168.133.71',21211);
	  while(list($key,$deviceToken) = each($device_token)){
            
	  if($data['payload']=$message){
	      $data['deviceToken']=$deviceToken;
              $output = json_encode($data);
              memcache_set($memcache_obj,'pushqueue',$output,0,0);
	    } 
	  }
	  memcache_close($memcache_obj);
	}

	function createMessage($params){
	 
	  if (isset($params['alert'])){
	    $payload['aps'] = array('alert'=>$params['alert']);
	  } if (isset($params['alert1'])){
            $payload['aps'] = array('alert'=>$params['alert1'], 'badge'=>1, 'sound'=>'default');
	  } if (isset($params['alertc'])){
            $payload['aps'] = array('alert'=>$params['alertc'], 'badge'=>1, 'sound'=>'default');
          } if (isset($params['type'])){
	    $payload['type']=$params['type'];
	  } if (isset($params['time'])){
            $payload['time']=$params['time'];
	  } if (isset($params['event'])){
	    $payload['event']=$params['event'];
	  } if (isset($params['id'])){
	    $payload['id']=$params['id'];
	  } if (isset($params['misc'])){
	    $payload['misc']=$params['misc'];
	  } if (isset($params['salt'])){
	    $payload['salt']=$params['salt'];
	  } if (isset($params['hash'])){
            $payload['hash']=$params['hash'];
          } if (isset($params['userid'])){
            $payload['userId']=$params['userid'];
          } if (isset($params['msgid'])){
            $payload['msgId']=$params['msgid'];
          } if (isset($params['msgread'])){
            $payload['msgRead']=$params['msgread'];
          } if (isset($params['threadid'])){
            $payload['threadId']=$params['threadid'];
          } if (isset($params['subarray'])){
            $payload['subArray']=$params['subarray'];
          } if (isset($params['threadactive'])){
            $payload['active']=$params['threadactive'];
          } if (isset($params['cid'])){
            $payload['cid']=$params['cid'];
          } if (isset($params['friend_id'])){
            $payload['friend_id']=$params['friend_id'];
          } if (isset($params['friend_accepted'])){
            $payload['friend_accepted']=$params['friend_accepted'];
          }if (isset($params['request_notification'])){
            $payload['request_notification']=$params['request_notification'];
          }if (isset($params['blocked_by_friend'])){
            $payload['blocked_by_friend']=$params['blocked_by_friend'];
          }if (isset($params['blocking_friend'])){
            $payload['blocking_friend']=$params['blocking_friend'];
          } if(isset($params['ca1'])){
	    $payload['ca1']=$params['ca1'];
	  } if(isset($params['ca2'])){
            $payload['ca2']=$params['ca2'];
          }


	  return json_encode($payload);
	
	}



}
