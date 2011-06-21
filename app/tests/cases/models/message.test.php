<?php 
/* SVN FILE: $Id$ */
/* Message Test cases generated on: 2009-08-26 21:56:52 : 1251338212*/
App::import('Model', 'Message');

class MessageTestCase extends CakeTestCase {
	var $Message = null;
	var $fixtures = array('app.message', 'app.user', 'app.thread');

	function startTest() {
		$this->Message =& ClassRegistry::init('Message');
	}

	function testMessageInstance() {
		$this->assertTrue(is_a($this->Message, 'Message'));
	}

	function testMessageFind() {
		$this->Message->recursive = -1;
		$results = $this->Message->find('first');
		$this->assertTrue(!empty($results));

		$expected = array('Message' => array(
			'id'  => 1,
			'body'  => 'Lorem ipsum dolor sit amet',
			'date_created'  => 'Lorem ipsum dolor sit amet',
			'user_id'  => 1,
			'thread_id'  => 1
		));
		$this->assertEqual($results, $expected);
	}
}
?>