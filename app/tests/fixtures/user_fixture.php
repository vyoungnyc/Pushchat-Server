<?php 
/* SVN FILE: $Id$ */
/* User Fixture generated on: 2009-08-26 21:54:58 : 1251338098*/

class UserFixture extends CakeTestFixture {
	var $name = 'User';
	var $table = 'users';
	var $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'username' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 200, 'key' => 'unique'),
		'password' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 200),
		'email' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 200, 'key' => 'unique'),
		'date_created' => array('type'=>'datetime', 'null' => false, 'default' => NULL),
		'date_modified' => array('type'=>'timestamp', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'),
		'device_id' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 40, 'key' => 'unique'),
		'device_token' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 64, 'key' => 'unique'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'username' => array('column' => 'username', 'unique' => 1), 'email' => array('column' => 'email', 'unique' => 1), 'device_id' => array('column' => 'device_id', 'unique' => 1), 'device_token' => array('column' => 'device_token', 'unique' => 1))
	);
	var $records = array(array(
		'id'  => 1,
		'username'  => 'Lorem ipsum dolor sit amet',
		'password'  => 'Lorem ipsum dolor sit amet',
		'email'  => 'Lorem ipsum dolor sit amet',
		'date_created'  => '2009-08-26 21:54:58',
		'date_modified'  => '2009-08-26 21:54:58',
		'device_id'  => 'Lorem ipsum dolor sit amet',
		'device_token'  => 'Lorem ipsum dolor sit amet'
	));
}
?>