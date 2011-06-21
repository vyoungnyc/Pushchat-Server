<?php 
/* SVN FILE: $Id$ */
/* Subscription Test cases generated on: 2009-08-26 21:56:11 : 1251338171*/
App::import('Model', 'Subscription');

class SubscriptionTestCase extends CakeTestCase {
	var $Subscription = null;
	var $fixtures = array('app.subscription', 'app.user', 'app.thread');

	function startTest() {
		$this->Subscription =& ClassRegistry::init('Subscription');
	}

	function testSubscriptionInstance() {
		$this->assertTrue(is_a($this->Subscription, 'Subscription'));
	}

	function testSubscriptionFind() {
		$this->Subscription->recursive = -1;
		$results = $this->Subscription->find('first');
		$this->assertTrue(!empty($results));

		$expected = array('Subscription' => array(
			'id'  => 1,
			'user_id'  => 1,
			'thread_id'  => 1
		));
		$this->assertEqual($results, $expected);
	}
}
?>