<?php 
/* SVN FILE: $Id$ */
/* Thread Fixture generated on: 2009-08-26 21:55:52 : 1251338152*/

class ThreadFixture extends CakeTestFixture {
	var $name = 'Thread';
	var $table = 'threads';
	var $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 20, 'key' => 'primary'),
		'date_created' => array('type'=>'timestamp', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'),
		'active' => array('type'=>'boolean', 'null' => false, 'default' => '1'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);
	var $records = array(array(
		'id'  => 1,
		'date_created'  => 1,
		'active'  => 1
	));
}
?>