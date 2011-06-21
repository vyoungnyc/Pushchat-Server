<?php 
/* SVN FILE: $Id$ */
/* SubscriptionsController Test cases generated on: 2009-08-26 21:57:16 : 1251338236*/
App::import('Controller', 'Subscriptions');

class TestSubscriptions extends SubscriptionsController {
	var $autoRender = false;
}

class SubscriptionsControllerTest extends CakeTestCase {
	var $Subscriptions = null;

	function startTest() {
		$this->Subscriptions = new TestSubscriptions();
		$this->Subscriptions->constructClasses();
	}

	function testSubscriptionsControllerInstance() {
		$this->assertTrue(is_a($this->Subscriptions, 'SubscriptionsController'));
	}

	function endTest() {
		unset($this->Subscriptions);
	}
}
?>