<?php 
/* SVN FILE: $Id$ */
/* Contact Test cases generated on: 2009-09-03 14:21:08 : 1252002068*/
App::import('Model', 'Contact');

class ContactTestCase extends CakeTestCase {
	var $Contact = null;
	var $fixtures = array('app.contact');

	function startTest() {
		$this->Contact =& ClassRegistry::init('Contact');
	}

	function testContactInstance() {
		$this->assertTrue(is_a($this->Contact, 'Contact'));
	}

	function testContactFind() {
		$this->Contact->recursive = -1;
		$results = $this->Contact->find('first');
		$this->assertTrue(!empty($results));

		$expected = array('Contact' => array(
			'id'  => 1,
			'user_id'  => 1,
			'friend_id'  => 1,
			'pending'  => 1
		));
		$this->assertEqual($results, $expected);
	}
}
?>