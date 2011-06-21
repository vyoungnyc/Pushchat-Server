<?php 
/* SVN FILE: $Id$ */
/* MessagesController Test cases generated on: 2009-08-26 21:57:04 : 1251338224*/
App::import('Controller', 'Messages');

class TestMessages extends MessagesController {
	var $autoRender = false;
}

class MessagesControllerTest extends CakeTestCase {
	var $Messages = null;

	function startTest() {
		$this->Messages = new TestMessages();
		$this->Messages->constructClasses();
	}

	function testMessagesControllerInstance() {
		$this->assertTrue(is_a($this->Messages, 'MessagesController'));
	}

	function endTest() {
		unset($this->Messages);
	}
}
?>