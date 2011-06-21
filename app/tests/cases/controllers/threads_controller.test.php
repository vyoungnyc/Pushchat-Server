<?php 
/* SVN FILE: $Id$ */
/* ThreadsController Test cases generated on: 2009-08-26 21:57:47 : 1251338267*/
App::import('Controller', 'Threads');

class TestThreads extends ThreadsController {
	var $autoRender = false;
}

class ThreadsControllerTest extends CakeTestCase {
	var $Threads = null;

	function startTest() {
		$this->Threads = new TestThreads();
		$this->Threads->constructClasses();
	}

	function testThreadsControllerInstance() {
		$this->assertTrue(is_a($this->Threads, 'ThreadsController'));
	}

	function endTest() {
		unset($this->Threads);
	}
}
?>