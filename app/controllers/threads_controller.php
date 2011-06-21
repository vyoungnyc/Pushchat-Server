<?php
class ThreadsController extends AppController {

	var $name = 'Threads';
        function beforeFilter(){
            $this->Security->blackHole($this);
	}        

	function index(){
	} 



}
?>
