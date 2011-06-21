<?php 
/* SVN FILE: $Id$ */
/* Thread Test cases generated on: 2009-08-26 21:55:52 : 1251338152*/
App::import('Model', 'Thread');

class ThreadTestCase extends CakeTestCase {
	var $Thread = null;
	var $fixtures = array('app.thread', 'app.message', 'app.subscription');

	function startTest() {
		$this->Thread =& ClassRegistry::init('Thread');
	}

	function testThreadInstance() {
		$this->assertTrue(is_a($this->Thread, 'Thread'));
	}

	function testThreadFind() {
		$this->Thread->recursive = -1;
		$results = $this->Thread->find('first');
		$this->assertTrue(!empty($results));

		$expected = array('Thread' => array(
			'id'  => 1,
			'date_created'  => 1,
			'active'  => 1
		));
		$this->assertEqual($results, $expected);
	}
}
?>