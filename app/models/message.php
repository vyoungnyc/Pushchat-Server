<?php
class Message extends AppModel {

	var $name = 'Message';
	var $validate = array(
		'body' => array('notempty'),
		'user_id' => array('notempty'),
		'thread_id' => array('notempty')
	);

	function getMessage($userId=null, $messageId=null){
	$ck = 'getmessageid_'.$messageId;
        if(!$result = Cache::read($ck,'messages')){


	  if($message = $this->find('first',array('fields'=>array('Message.id','Message.body', 'Message.created', 'Message.user_id', 'Message.thread_id','Message.read'),'conditions'=>array('Message.id'=>$messageId,'Thread.active'=>1,'Subscription.user_id'=>$userId, 'Subscription.thread_id=Message.thread_id' )))){
	    $result = array();
	    if($message['Message']['user_id'] == $userId || $message['Message']['read']==1){ //message is by user, or it's read already
	      array_push($result,$message);
	      Cache::write($ck,$result,'messages');
	      return $result;  
	    } else if ($message['Message']['user_id']!=$userId && $message['Message']['read']==0){ 
	      //$this->id = false; //force update;
	      $message['Message']['read']=1;
	      if($this->save($message)){
                array_push($result,$message);
                Cache::write($ck,$result,'messages');
		return $result; 
	      } else {
		return false;
	      }
	    }
	  } else {//no result for message
            return false;
	  }

	} else if ($result){
	  $message = $result[0];
	  if($message['Message']['user_id']!=$userId && $message['Message']['read']==0){
	    $message['Message']['read']=1;
	    if($this->save($message)){
	      $result[0]=$message;
	      Cache::delete($ck,'messages');
	      Cache::write($ck,$result,'messages');
	    }
	  }
	  return $result;
	}
        }
	
	function getMessagesByThread($userId=null, $threadId=null){
//          if($messages = $this->find('all',array('fields'=>array('Message.id','Message.body', 'Message.created', 'Message.user_id', 'Message.thread_id','Message.read'),'conditions'=>array('Message.thread_id'=>$threadId,  'Thread.active'=>1, 'Thread.id'=>$threadId,'Subscription.user_id'=>$userId,'Subscription.thread_id'=>$threadId)))){
	  if($messages = $this->find('all',array('fields'=>array('Message.id', 'Message.user_id','Message.read'),'conditions'=>array('Message.thread_id'=>$threadId,  'Thread.active'=>1, 'Thread.id'=>$threadId,'Subscription.user_id'=>$userId,'Subscription.thread_id'=>$threadId)))){
            return $messages;
          } else {
            return false;
          }
	}          

	function addMessageToThread($userId=null, $threadId=null, $body=null){
		  
	  if(!$body){

}

	  if($body && $this->Subscription->find('first',array('fields'=>array('Subscription.id'),'conditions'=>array('Subscription.user_id'=>$userId, 'Subscription.thread_id'=>$threadId, 'Thread.active'=>1, 'Thread.id'=>$threadId)))){

	  $this->unbindModel(array('belongsTo'=>array('Thread','Subscription')));

	    while(list($key,$msg) = each($body)){
	      $this->id= false; //set the last message id to false so it will force update instead of updating last record.
	      $results['Message']['user_id'] = $userId;
	      $results['Message']['thread_id']=$threadId;
	      $results['Message']['body']=$msg;

	      if($this->save($results)){
		//$msgResult = $this->find('first',array('fields'=>array('Message.id', 'Message.user_id','Message.read'),'conditions'=>array('Message.thread_id'=>$threadId)));
		//$msgResult = $this->find('all',array('conditions'=>array('Message.thread_id'=>$threadId)));
		$ck = 'getmessageid_'.$this->id;
		$msgResult = $this->find('first',array('fields'=>array('Message.id','Message.body', 'Message.created', 'Message.user_id', 'Message.thread_id','Message.read'),'conditions'=>array('Message.id'=>$this->id)));
		$result = array();
		array_push($result,$msgResult);

		Cache::write($ck,$result,'messages');
		return $result; //saved successfully
	      }
	    }
          } else {
            return false;
          }
	}

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
		'Thread' => array(
			'className' => 'Thread',
			'foreignKey' => 'thread_id',
			'conditions' =>array('Thread.active=1') ,
			'fields' => '',
			'order' => ''
		),
		'Subscription'=>array(
                        'className' => 'Subscription',
                        'conditions' =>array('Subscription.thread_id = Message.thread_id'),
                        'order'=>'',
			'fields'=>'',
                        'foreignKey'=>'')
	);
}
?>
