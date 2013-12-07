<?php
$this->setLayout(NULL);
$return = array('ok' => false,
				'message' => 'Accès refusé');
if (isset($exception)) {
	include 'exception/exception.php';
}
print toJson($return);