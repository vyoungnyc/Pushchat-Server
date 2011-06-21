<?php 
/* SVN FILE: $Id$ */
/* User Test cases generated on: 2009-08-26 21:54:58 : 1251338098*/
App::import('Model', 'User');

class UserTestCase extends CakeTestCase {
	var $User = null;
	var $fixtures = array('app.user', 'app.message', 'app.subscription');

	function startTest() {
		$this->User =& ClassRegistry::init('User');
	}

	function testUserInstance() {
		$this->assertTrue(is_a($this->User, 'User'));
	}

	function testUserFind() {
		$this->User->recursive = -1;
		$results = $this->User->find('first');
		$this->assertTrue(!empty($results));

		$expected = array('User' => array(
			'id'  => 1,
			'username'  => 'Lorem ipsum dolor sit amet',
			'password'  => 'Lorem ipsum dolor sit amet',
			'email'  => 'Lorem ipsum dolor sit amet',
			'date_created'  => '2009-08-26 21:54:58',
			'date_modified'  => '2009-08-26 21:54:58',
			'device_id'  => 'Lorem ipsum dolor sit amet',
			'device_token'  => 'Lorem ipsum dolor sit amet'
		));
		$this->assertEqual($results, $expected);
	}
}
?>