<?php
class Thread extends AppModel {

	var $name = 'Thread';
	var $validate = array(
		'active' => array('boolean')
	);




	//The Associations below have been created with all possible keys, those that are not needed can be removed
/**	var $hasMany = array(
		'Message' => array(
			'className' => 'Message',
			'foreignKey' => 'thread_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Subscription' => array(
			'className' => 'Subscription',
			'foreignKey' => 'thread_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);
*/
}
?>
