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

switch ($httpMethod) {
  // create a new interval
  case 'POST':
    $startDate = $request->get('date_start');
    $endDate = $request->get('date_end');
    $price = $request->get('price');
    $data = $intervals->add($startDate, $endDate, $price);
    break;

  // deletes an interval
  case 'DELETE':
    $intervalId = $request->get('interval_id');
    if ($request->get('m') === 'delete') {
      $data = $intervals->deleteAllIntervals();
    } else {
      $data = $intervals->deleteInterval($intervalId);
    }
    break;

  // updates an interval, this operation is the same as adding a new one,
  // so we just call that method
  case 'PUT':
    $startDate = $request->get('date_start');
    $endDate = $request->get('date_end');
    $price = $request->get('price');
    $data = $intervals->add($startDate, $endDate, $price);
    break;

  // get all intervals
  case 'GET':
  default:
    $startDate = $request->get('date_start', date('2010-01-01'));
    $endDate = $request->get('date_end', date('2020-12-31'));
    $data = $intervals->search($startDate, $endDate);
}


$response = new JsonResponse();
$response->setData($data);
$response->send();