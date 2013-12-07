<?php
$this->setLayout(NULL);
/*$return = array('ok' => false,
				'message' => 'Service indisponible');*/
$return = array('message' => 'Service indisponible');
if (isset($exception)) {
	include 'exception/exception.php';
}
print toJson($return);