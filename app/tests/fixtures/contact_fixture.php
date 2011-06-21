<?php 
/* SVN FILE: $Id$ */
/* Contact Fixture generated on: 2009-09-03 14:21:08 : 1252002068*/

class ContactFixture extends CakeTestFixture {
	var $name = 'Contact';
	var $table = 'contacts';
	var $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 20, 'key' => 'primary'),
		'user_id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'friend_id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'pending' => array('type'=>'boolean', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'user_id' => array('column' => 'user_id', 'unique' => 0), 'friend_id' => array('column' => 'friend_id', 'unique' => 0))
	);
	var $records = array(array(
		'id'  => 1,
		'user_id'  => 1,
		'friend_id'  => 1,
		'pending'  => 1
	));
}
?>