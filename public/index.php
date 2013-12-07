<?php
require_once '../inc/bootstrap.inc.php';

//$defaultRoute = array('module' => Application::getInstance()->DEFAULT_MODULE);

session_name(Application::getInstance()->SESSION_NAME);
session_start();

// Dispatch la requête HTTP sur l'action controller demandé
$front = FrontController::dispatch(/*$defaultRoute*/);