<?php
class Subscription extends AppModel {

	var $name = 'Subscription';
	var $validate = array(
		'user_id' => array('notempty'),
		'thread_id' => array('notempty')
	);
	
	function validateContacts($user_id,$recipientList){
	  $this->bindModel(array('belongsTo'=> array( 'Contact' => array(
                        'className' => 'Contact',
                        'foreignKey' => 'user_id',
                        'conditions' => '',
                        'fields' => '',
                        'order' => ''
                ))));
	  $results = $this->Contact->find('all',array('fields'=>array('Contact.friend_id'),'conditions'=>array('Contact.blocked_by_friend'=>0,'Contact.friend_accepted'=>1,'Contact.user_id'=>$user_id,'Contact.friend_id'=>$recipientList)));
	  $this->unbindModel(array('belongsTo'=>array('Contact')));
	  if(count($results)<1){
	    return FALSE;
	  } else {
	    $recipientList = array($user_id);
	    while(list($key,$userid) = each($results)){
	        if($userid['Contact']['friend_id']!=$user_id){
		  array_push($recipientList, $userid['Contact']['friend_id']);
	    	}
	    }
	    return $recipientList;
	  }
	}

        function initiateSubscription($recipientList){
          $threadList = array();
          if($results=$this->find('all',array('fields'=>array('Subscription.thread_id','count(Subscription.user_id) as userlist'),'conditions'=>array('Subscription.user_id'=>$recipientList),'group'=>'Subscription.thread_id', 'having'=>array('userlist'=>count($recipientList))))){
	    while(list($key,$thread_id) = each($results)){
              array_push($threadList,$thread_id['Subscription']['thread_id']);
            }
	    
	    $this->unbindModel(array('belongsTo'=>array('Thread','Contact'))); //unbind model so it doesnt need to join Thread or Contact
	    $results = $this->find('first',array('fields'=>array('Subscription.thread_id', 'count(Subscription.user_id) as userlist'), 'conditions'=>array('Subscription.thread_id'=>$threadList),'group'=>'Subscription.thread_id','having'=>array('userlist'=>count($recipientList))));
	    if($results){
	    $this->unbindModel(array('belongsTo'=>array('Contact'))); //unbind model so it doesnt need to join Thread or Contact

	    $this->bindModel(array('belongsTo'=> array( 'Thread' => array(
                        'className' => 'Thread',
                        'foreignKey' => 'thread_id',
                        'conditions' => 'Thread.active=1',
                        'fields' => '',
                        'order' => ''))));
  	      //return $results['Subscription']['thread_id']; //an active thread exists!
		$this->unbindModel(array('belongsTo'=>array('Contact'))); //unbind model so it doesnt need to join Thread or Contact
		 $threadresult = $this->find('all', array('conditions'=>array('Thread.id'=>$results['Subscription']['thread_id'])));
                 $ck ='getusersbythread_'.$results['Subscription']['thread_id'];
                 Cache::delete($ck,'subscriptions');

	         return $threadresult;
	    } else {
            //rebind model so thread can be created.
              $this->bindModel(array('belongsTo'=> array( 'Thread' => array(
                        'className' => 'Thread',
                        'foreignKey' => 'thread_id',
                        'conditions' => 'Thread.active=1',
                        'fields' => '',
                        'order' => ''))));
              $thread['Thread']['active']=1;
              $this->Thread->save($thread);
              while(list($key,$userid) = each($recipientList)){
                $this->id= false; //set the last subscription id to false so it will force update instead of updating last record.
                $subscription['Subscription']['thread_id']=$this->Thread->id;
                $subscription['Subscription']['user_id']=$userid;
                $this->save($subscription);
	      }
              	   $this->unbindModel(array('belongsTo'=>array('Contact'))); //unbind model so it doesnt need to join Thread or Contact
		   $threadresult = $this->find('all', array('conditions'=>array('Thread.id'=>$this->Thread->id)));
                 $ck = 'getusersbythread_'.$this->Thread->id;
                 Cache::delete($ck,'subscriptions');

                 return $threadresult;

//	      return $this->Thread->id;
	    }
	  } else {
	    $thread['Thread']['active']=1;
            $this->Thread->save($thread);
            while(list($key,$userid) = each($recipientList)){
              $this->id= false; //set the last subscription id to false so it will force update instead of updating last record.
              $subscription['Subscription']['thread_id']=$this->Thread->id;
              $subscription['Subscription']['user_id']=$userid;
              $this->save($subscription);
	    }
           $this->unbindModel(array('belongsTo'=>array('Contact'))); //unbind model so it doesnt need to join Thread or Contact

	        $threadresult = $this->find('all', array('conditions'=>array('Thread.id'=>$this->Thread->id)));
                 $ck = 'getusersbythread_'.$this->Thread->id;
                 Cache::delete($ck,'subscriptions');

                return $threadresult;

//            return $this->Thread->id;

	  }
        }


	function unsubscribeThread($userId=null, $threadId=null){
	  if(($userlist = $this->getUsersByThread($userId,$threadId))){
            while(list($key,$subscription) = each($userlist)){
                if($subscription['Subscription']['user_id']==$userId && count($userlist)==1){
                  //remove from subscription and mark thread as inactive

		  $this->delete($subscription['Subscription']['id']);
	          $results['Thread']['id']=$threadId;
        	  $results['Thread']['active']=0;
            	  $this->Thread->save($results['Thread']);
                  $this->unbindModel(array('belongsTo'=>array('Contact'))); //unbind model so it doesnt need to join Thread or Contact
                  $ck = 'getusersbythread_'.$threadId;
                  Cache::delete($ck,'subscriptions');
 
                $threadresult = $this->find('all', array('conditions'=>array('Thread.id'=>$this->Thread->id)));
		return $threadresult;


		} else if ($subscription['Subscription']['user_id']==$userId && count($userlist)>1){
		  //remove subscription.
		  $this->delete($subscription['Subscription']['id']);
                  $this->unbindModel(array('belongsTo'=>array('Contact'))); //unbind model so it doesnt need to join Thread or Contact
                  $ck = 'getusersbythread_'.$threadId;
                  Cache::delete($ck,'subscriptions');
 
	        $threadresult = $this->Thread->find('all', array('conditions'=>array('Thread.id'=>$threadId)));
		return $threadresult[0];

		} 
            } 
	    return false;  // fall through to catch in case it went through user list, and couldnt find matching user to unsubscribe.
	  } else {
	    //do nothing, noone in thread, or thread is inactive, or current user is not participating in thread
	    return false;
	  }

	}

	function subscribeFriendToThread($userId=null,$recipientList=null, $threadId=null){
	    //add friend to thread where userId is currently in.
	  if($results = $this->find('first', array('fields'=>array('id'),'conditions'=>array('Subscription.thread_id'=>$threadId, 'Subscription.user_id'=>$userId, 'Thread.active'=>1, 'Thread.id'=>$threadId)))){//make sure current user is part of active thread
	    $this->unbindModel(array('belongsTo'=>array('Thread','Contact'))); //unbind model so it doesnt need to join Thread or Contact
	    while(list($key,$friendId) = each($recipientList)){
	      if($userId!=$friendId && !($this->find('first', array('fields'=>array('id'),'conditions'=>array('Subscription.thread_id'=>$threadId, 'Subscription.user_id'=>$friendId))))){
                $this->id= false; //set the last subscription id to false so it will force update instead of updating last record.
                $subscription['Subscription']['thread_id']=$threadId;
                $subscription['Subscription']['user_id']=$friendId;
                $this->save($subscription);
              }
	    }

	        $this->unbindModel(array('belongsTo'=>array('Contact','Thread'))); //unbind model so it doesnt need to join Thread or Contact
                 $ck = 'getusersbythread_'.$threadId;
                 Cache::delete($ck,'subscriptions');

                $threadresult = $this->find('all', array('conditions'=>array('Subscription.thread_id'=>$threadId)));
                return $threadresult;

	  } else {
	    return false;
	  }
	}

        function getThreads($userId=null){
          if($threadList=$this->find('all',array('fields'=>array('Subscription.id','Subscription.user_id', 'Subscription.thread_id','Thread.id', 'Thread.active'),'conditions'=>array('Subscription.user_id'=>$userId, 'Thread.id=Subscription.thread_id')))){
            return $threadList;
          }
          return false;
        }

        function getThread($threadId=null){
          if($subscriptionList=$this->find('all',array('fields'=>array('Thread.id', 'Thread.active','Subscription.id','Subscription.user_id', 'Subscription.thread_id'),'conditions'=>array('Subscription.thread_id'=>$threadId, 'Thread.id=Subscription.thread_id')))){
            return $subscriptionList; 
          }   
          return false;
        }

	function getUsersByThread($userId=null,$threadId=null){
	  if($this->find('first',array('fields'=>array('thread_id'),'conditions'=>array('Subscription.user_id'=>$userId,'Subscription.thread_id'=>$threadId))) 
	    && $userList = $this->find('all',array('fields'=>array('id','user_id', 'thread_id'),'conditions'=>array('Subscription.thread_id'=>$threadId)))){
	  return $userList;
	  }
	  return false;

	}


	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
		'Thread' => array(
			'className' => 'Thread',
			'foreignKey' => 'thread_id',
			'conditions' => 'Thread.active=1',
			'fields' => '',
			'order' => ''
		)
	);

}
?>
