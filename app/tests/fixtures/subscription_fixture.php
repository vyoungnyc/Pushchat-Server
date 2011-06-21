<?php 
/* SVN FILE: $Id$ */
/* Subscription Fixture generated on: 2009-08-26 21:56:11 : 1251338171*/

class SubscriptionFixture extends CakeTestFixture {
	var $name = 'Subscription';
	var $table = 'subscriptions';
	var $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 20, 'key' => 'primary'),
		'user_id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'index'),
		'thread_id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 20, 'key' => 'index'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'subscribers_userID_threadID_idx' => array('column' => array('user_id', 'thread_id'), 'unique' => 1), 'user_id' => array('column' => 'user_id', 'unique' => 0), 'thread_id' => array('column' => 'thread_id', 'unique' => 0))
	);
	var $records = array(array(
		'id'  => 1,
		'user_id'  => 1,
		'thread_id'  => 1
	));
}
?>