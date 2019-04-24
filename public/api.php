<?php
require __DIR__ . '/../vendor/autoload.php';

use scodx\intervals;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

$request = Request::createFromGlobals();
$httpMethod = $request->getMethod();
$apiMethod = $request->get('m', 'search');

$intervals = new intervals();
$data = [];

switch ($apiMethod) {
  case 'add':
    if ($request->isMethod('POST')){
      $startDate = $request->get('startDate');
      $endDate = $request->get('endDate');
      $price = $request->get('price');
      $data = $intervals->add($startDate, $endDate, $price);
    }
    break;
  case 'search':
  default:
    $startDate = $request->get('startDate', date('Y-m-01'));
    $endDate = $request->get('endDate', date('Y-m-d'));
    $data = $intervals->search($startDate, $endDate);
}


//var_dump($data); die();
$response = new JsonResponse();
$response->setData($data);
$response->send();