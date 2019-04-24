<?php

namespace scodx;

//use \scodx\db;

class intervals
{

  private $db;

  public function __construct ()
  {

    $db = new db();
    $this->db = $db;
  }

  public function search($startDate, $endDate)
  {
    return $this->db->search($startDate, $endDate);
  }

  public function add($startDate, $endDate, $price)
  {
    $db = $this->db;
    $collisions = $this->search($startDate, $endDate);
    $lastCollisionIndex = count($collisions)-1;
    // converting startDate to timestamp
    $startDateTimestamp = strtotime($startDate);
    $endDateTimestamp = strtotime($endDate);
    $newPrice = floatval($price);
    $collisionsIds = [];

    $newIntervals = [];
    $updatedIntervals = [];
    $deletedIntervals = [];

    $collisionStart = $collisions[0];
    $collisionEnd = $collisions[$lastCollisionIndex];

    $collisionStartDateTimestamp = strtotime($collisionStart->date_start);
    $collisionEndDateTimestamp = strtotime($collisionEnd->date_end);

    foreach ($collisions as $collision) {
      $collisionsIds[$collision->interval_id] = $collision->interval_id;
    }

    // Checking if the start date is before the collisions
    if ($startDateTimestamp <= $collisionStartDateTimestamp) {

      // if end date is after the collisions
      if ($endDateTimestamp >= $collisionEndDateTimestamp) {
        // delete all but last one, update that one
        $updatedIntervals[] = $db->generateUpdateArray([
          'date_start' => $startDate,
          'price' => $newPrice,
        ], $collisionEnd->interval_id);
      } else {
        // if the end date is before the collisions we need to create a new interval
        $newIntervals[] = [
          'date_start' => $startDate,
          'date_end' => $endDate,
          'price' => $newPrice,
        ];
        $updatedIntervals[] = [
          'interval_id' => $collisionEnd->interval_id,
          'date_start' => $this->formatDateStr('+1 Day', $endDateTimestamp),
          'price' => $collisionEnd->price,
        ];
      }

      unset($collisionsIds[$collisionEnd->interval_id]);
      $deletedIntervals = array_keys($collisionsIds);

    } else {
      // if start date is after the collision's start date


      // if end date is after the collisions
      if ($endDateTimestamp >= $collisionEndDateTimestamp) {

        if ($newPrice != $collisionStart->price) {
          $updatedIntervals[] = $db->generateUpdateArray([
//            'date_end' => date('Y-m-d', strtotime('-1 Day', $startDateTimestamp)),
            'date_end' => $this->formatDateStr('-1 Day', $startDateTimestamp),
          ], $collisionStart->interval_id);
          $updatedIntervals[] = [
            'interval_id' => $collisionEnd->interval_id,
            'date_start' => $startDate,
            'date_end' => $endDate,
            'price' => $newPrice,
          ];

          unset($collisionsIds[$collisionStart->interval_id]);
          unset($collisionsIds[$collisionEnd->interval_id]);

        } else {
          $updatedIntervals[] = $db->generateUpdateArray([
            'date_end' => $endDate,
          ], $collisionStart->interval_id);
          unset($collisionsIds[$collisionStart->interval_id]);
        }

        $deletedIntervals = array_keys($collisionsIds);

      } else {
        // if the end date is before the collisions we need to create a new interval only if the prices are the same
        $newIntervals[] = [
          'date_start' => $startDate,
          'date_end' => $endDate,
          'price' => $newPrice,
        ];
        $updatedIntervals[] = $db->generateUpdateArray([
//          'date_end' => date('Y-m-d', strtotime('-1 Day', $startDateTimestamp)),
          'date_end' => $this->formatDateStr('-1 Day', $startDateTimestamp),
        ], $collisionStart->interval_id);
        $updatedIntervals[] = $db->generateUpdateArray([
//          'date_start' => date('Y-m-d', strtotime('+1 Day', $endDateTimestamp)),
          'date_start' => $this->formatDateStr('+1 Day', $endDateTimestamp),
          'price' => $collisionEnd->price,
        ], $collisionEnd->interval_id);
        unset($collisionsIds[$collisionStart->interval_id]);
        unset($collisionsIds[$collisionEnd->interval_id]);
        $deletedIntervals = array_keys($collisionsIds);
      }

    }

//    $this->processIntervals($updatedIntervals, $newIntervals, $deletedIntervals);
    $db->processOperations($updatedIntervals, $newIntervals, $deletedIntervals);
    return [
      'collisions' => $collisions,
      'updatedIntervals' => $updatedIntervals,
      'newIntervals' => $newIntervals,
      'deletedIntervals' => $deletedIntervals,
    ];
  }

  private function formatDateStr($dateString, $timestamp)
  {
    return date('Y-m-d', strtotime($dateString, $timestamp));
  }

}