<?php
class Contact extends AppModel {

	var $name = 'Contact';
	var $validate = array(
		'user_id' => array('numeric'),
		'friend_id' => array('numeric'),
		'friend_accepted' => array('boolean')
	);
	
	function getContacts($user_id=null){
	  $ck = 'getcontacts_'.$user_id;
          if(!$userList = Cache::read($ck,'contacts')){
	    $userList = array_merge($this->find('all',array('fields'=>array(),'conditions'=>array('Contact.user_id'=>$user_id))),$this->find('all',array('fields'=>array(),'conditions'=>array('Contact.friend_id'=>$user_id))));
	    if ($userList){
	      Cache::write($ck,$userList,'contacts');
	    }
	    return $userList;
	  } else if ($userList){
	    return $userList;
	  }

	
	}
        
	function declineContact($user_id = null, $friend_id =null){
	  $contact = array();
	  if($friend_id && ($friend_id!=$user_id)){
          $this->updateAll(array('Contact.request_notification'=>0), array('Contact.user_id'=>$friend_id, 'Contact.friend_id'=>$user_id,'Contact.friend_accepted'=>0, 'Contact.request_notification'=>1));
	    if($list1=$this->find('first',array('fields'=>array(),'conditions'=>array('Contact.user_id'=>$friend_id, 'Contact.friend_id'=>$user_id)))){
		$ck = 'getcontacts_'.$friend_id;
                Cache::delete($ck,'contacts');
                array_push($contact,$list1);
            }
            return $contact; 
          } else {
	    return false;
	  }
	}

	function blockContact($user_id, $friend_id){
	if($friend_id[0] && ($friend_id[0]!=$user_id)){
	  $this->updateAll(array('Contact.blocked_by_friend'=>1), array('Contact.user_id'=>$friend_id, 'Contact.friend_id'=>$user_id, 'Contact.blocked_by_friend'=>0)); 
	  $this->updateAll(array('Contact.blocking_friend'=>1), array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend_id, 'Contact.blocking_friend'=>0));

	  $contact = array();
	  
	  if($list1=$this->find('first',array('fields'=>array(),'conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend_id[0])))){
		$ck = 'getcontacts_'.$user_id;
		Cache::delete($ck,'contacts');
		array_push($contact,$list1);
	  } 
	  if($list2=$this->find('first',array('fields'=>array(),'conditions'=>array('Contact.user_id'=>$friend_id[0], 'Contact.friend_id'=>$user_id)))){
		 $ck = 'getcontacts_'.$friend_id[0];  
		 Cache::delete($ck,'contacts');
		 array_push($contact,$list2);
	  } 
	  return $contact;
	} else {
	    return false;
	}
	}

        function unblockContact($user_id, $friend_id){
        if($friend_id[0] && ($friend_id[0]!=$user_id)){
          $this->updateAll(array('Contact.blocked_by_friend'=>0), array('Contact.user_id'=>$friend_id, 'Contact.friend_id'=>$user_id, 'Contact.blocked_by_friend'=>1));
          $this->updateAll(array('Contact.blocking_friend'=>0), array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend_id, 'Contact.blocking_friend'=>1));
          
          $contact = array();

          if($list1=$this->find('first',array('fields'=>array(),'conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend_id[0])))){
		$ck = 'getcontacts_'.$user_id;
                Cache::delete($ck,'contacts');
		array_push($contact,$list1);
          }
          if($list2=$this->find('first',array('fields'=>array(),'conditions'=>array('Contact.user_id'=>$friend_id[0], 'Contact.friend_id'=>$user_id)))){
                $ck = 'getcontacts_'.$friend_id[0];
                Cache::delete($ck,'contacts');
		array_push($contact,$list2);
          }
          return $contact;
        } else {
            return false;
        }


	}



        function addContacts($user_id=null,$friend_id=null){
          if(!$friend_id) {
	    return false;
	  } else {
	    $addResults = array();
            while(list($key,$friend) = each($friend_id)){
                if($friend && ($friend!=$user_id)){

		$alreadyfriends =  $this->find('first',array('conditions'=>array('Contact.user_id'=>$friend, 'Contact.friend_id'=>$user_id, 'Contact.friend_accepted'=>1)));
                
                if (!$alreadyfriends){
                        $this->id= false; //set the last subscription id to false so it will force update instead of updating last record.
                        $results = $this->find('first',array('conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend)));
                        if(!$results){   
                                //create new Contact that is pending approval
                                $results['Contact']['user_id']=$user_id;
                                $results['Contact']['friend_id']=$friend;
                                $this->save($results);
				if($this->id){	
				  array_push($addResults,$this->find('first',array('conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend))));
				}
                        } else if($results['Contact']['user_id']==$user_id && $results['Contact']['friend_id']==$friend && $results['Contact']['blocked_by_friend']==0){
                                if($results['Contact']['friend_accepted']==1 && $results['Contact']['request_notification']==0){
                                        //do nothing already a contact
                                } else if ($results['Contact']['friend_accepted']==0 && $results['Contact']['request_notification']==0){
                                        //need to resend request notification;
                                        $results['Contact']['request_notification']=1;
                                        $this->save($results);
                                } else if ($results['Contact']['friend_accepted']==0 && $results['Contact']['request_notification']==1){
                                        //still waiting for response, do nothing
                                } else if ($results['Contact']['friend_accepted']==1 && $results['Contact']['request_notification']==1){
                                        //fix request notifcation when already a contact.
                                        $results['Contact']['request_notification']=0;
                                        $this->save($results);
                                }
				if($this->id){                      
                                  array_push($addResults,$this->find('first',array('conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend))));
                                }
                        } else {
                                if($this->id){                      
                                  array_push($addResults,$this->find('first',array('conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend))));
                                }

				//do nothing, blocked by friend.
                        }       
                } else if($alreadyfriends && $alreadyfriends['Contact']['blocked_by_friend']==0) { //already friends and not blocked
                $this->id= false; //set the last subscription id to false so it will force update instead of updating last record.
                $results = $this->find('first',array('conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend)));
                    
                    if(!$results){
                      $results['Contact']['user_id']=$user_id;
                      $results['Contact']['friend_id']=$friend;
                      $results['Contact']['friend_accepted']=1;
                      $results['Contact']['request_notification']=0;
                      $this->save($results);
                    if($this->id){    //if saved with !$results 
                                  array_push($addResults,$this->find('first',array('conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend))));
                                }

		    } else if($results['Contact']['user_id']==$user_id && $results['Contact']['friend_id']==$friend && $results['Contact']['blocked_by_friend']==0){
                      if($results['Contact']['friend_accepted']==1 && $results['Contact']['request_notification']==0){
                        //do nothing already a contact
                      } else if ($results['Contact']['friend_accepted']==0){
                        //add contact
                        $results['Contact']['friend_accepted']=1;
                        $results['Contact']['request_notification']=0;
                        $this->save($results);
                      } else if ($results['Contact']['friend_accepted']==1 && $results['Contact']['request_notification']==1){
                        //fix request notifcation when already a contact.
                        $results['Contact']['request_notification']=0;
                        $this->save($results);
                      }
			if($this->id){ //if saved... 
                                  array_push($addResults,$this->find('first',array('conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend))));
                                }

                    } else { // if $this->id was set.
                               if($this->id){
                                  array_push($addResults,$this->find('first',array('conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend))));
                                }

			//do nothing, blocked by friend.
                    }
                }
                }
                }
            	if($addResults){
	                $ck = 'getcontacts_'.$user_id;
        	        Cache::delete($ck,'contacts');
                	$ck = 'getcontacts_'.$friend;
                	Cache::delete($ck,'contacts');
		}
		return $addResults;
	  }
	}


        function acceptContacts($user_id=null,$friend_id=null){
	  if(!$friend_id){
	    return false;
	  } else{
	    $addResults = array();
	    while(list($key,$friend) = each($friend_id)){
                if($friend && ($friend!=$user_id)){
	        $ck = 'getcontacts_'.$user_id;
                Cache::delete($ck,'contacts');
                $ck = 'getcontacts_'.$friend;
                Cache::delete($ck,'contacts');
	
                $accept = $this->find('first',array('conditions'=>array('Contact.user_id'=>$friend, 'Contact.friend_id'=>$user_id, 'Contact.friend_accepted'=>0, 'Contact.blocked_by_friend'=>0)));
                if(!$accept){
                  //no such invitation exists
                } else if ($accept) {
                  $accept['Contact']['friend_accepted']=1;
                  $accept['Contact']['request_notification']=0;
                  $this->save($accept);
                  $this->id= false; //set the last subscription id to false so it will force update instead of updating last record.
                  $results = $this->find('first',array('conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend)));

                    if(!$results){
                      $results['Contact']['user_id']=$user_id;
                      $results['Contact']['friend_id']=$friend;
                      $results['Contact']['friend_accepted']=1;
                      $results['Contact']['request_notification']=0;
                      $this->save($results);
                      if($list1=$this->find('first',array('fields'=>array(),'conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend_id)))){
                        array_push($addResults,$list1);
                      }  
                      if($list2=$this->find('first',array('fields'=>array(),'conditions'=>array('Contact.user_id'=>$friend_id, 'Contact.friend_id'=>$user_id)))){
                        array_push($addResults,$list2);
                      } 
	            } else if($results['Contact']['user_id']==$user_id && $results['Contact']['friend_id']==$friend && $results['Contact']['blocked_by_friend']==0){
		       if($results['Contact']['friend_accepted']==1 && $results['Contact']['request_notification']==0){
			//do nohing already a contact
                      } else if ($results['Contact']['friend_accepted']==0){
                        //add contact
                        $results['Contact']['friend_accepted']=1;
                        $results['Contact']['request_notification']=0;
                        $this->save($results);
                      } else if ($results['Contact']['friend_accepted']==1 && $results['Contact']['request_notification']==1){
                        //fix request notifcation when already a contact.
                        $results['Contact']['request_notification']=0;
                        $this->save($results);
                      }
          	      if($list1=$this->find('first',array('fields'=>array(),'conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend_id)))){
                	array_push($addResults,$list1);
          	      }                     
          	      if($list2=$this->find('first',array('fields'=>array(),'conditions'=>array('Contact.user_id'=>$friend_id, 'Contact.friend_id'=>$user_id)))){
                  	array_push($addResults,$list2);
          	      }        

                    } else {
		      //do nothing, blocked by friend.

                    //    array_push($addResults,$this->find('first',array('conditions'=>array('Contact.user_id'=>$user_id, 'Contact.friend_id'=>$friend))));
                    }
                }
	        }
	    }
                if($addResults){
                        $ck = 'getcontacts_'.$user_id;
                        Cache::delete($ck,'contacts');
                        $ck = 'getcontacts_'.$friend;
                        Cache::delete($ck,'contacts');
                }
	   return $addResults;//finished accepting contacts
	  }
	}



}
?>
