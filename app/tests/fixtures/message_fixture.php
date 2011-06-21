<?php 
/* SVN FILE: $Id$ */
/* Message Fixture generated on: 2009-08-26 21:56:52 : 1251338212*/

class MessageFixture extends CakeTestFixture {
	var $name = 'Message';
	var $table = 'messages';
	var $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 20, 'key' => 'primary'),
		'body' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 140),
		'date_created' => array('type'=>'timestamp', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'),
		'user_id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'index'),
		'thread_id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 20, 'key' => 'index'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'thread_id' => array('column' => 'thread_id', 'unique' => 0), 'origin_id' => array('column' => 'user_id', 'unique' => 0))
	);
	var $records = array(array(
		'id'  => 1,
		'body'  => 'Lorem ipsum dolor sit amet',
		'date_created'  => 'Lorem ipsum dolor sit amet',
		'user_id'  => 1,
		'thread_id'  => 1
	));
}
?>