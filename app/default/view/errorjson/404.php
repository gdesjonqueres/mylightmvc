<?php
$this->setLayout(NULL);
$return = array('ok' => false,
				'message' => 'Page introuvable');
if (isset($exception)) {
	include 'exception/exception.php';
}
print toJson($return);