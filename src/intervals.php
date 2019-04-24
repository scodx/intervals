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
        $updatedIntervals[] = [
          'interval_id' => $collisionEnd->interval_id,
          'date_start' => $startDate,
          'price' => $newPrice,
        ];
      } else {
        // if the end date is before the collisions we need to create a new interval
        $newIntervals[] = [
          'date_start' => $startDate,
          'date_end' => $endDate,
          'price' => $newPrice,
        ];
        $updatedIntervals[] = [
          'interval_id' => $collisionEnd->interval_id,
          'date_start' => date('Y-m-d', strtotime('+1 Day', $endDateTimestamp)),
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
          $updatedIntervals[] = [
            'interval_id' => $collisionStart->interval_id,
            'date_end' => date('Y-m-d', strtotime('-1 Day', $startDateTimestamp)),
          ];
          $updatedIntervals[] = [
            'interval_id' => $collisionEnd->interval_id,
            'date_start' => $startDate,
            'date_end' => $endDate,
            'price' => $newPrice,
          ];

          unset($collisionsIds[$collisionStart->interval_id]);
          unset($collisionsIds[$collisionEnd->interval_id]);

        } else {
          $updatedIntervals[] = [
            'interval_id' => $collisionStart->interval_id,
            'date_end' => $endDate,
          ];
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
        $updatedIntervals[] = [
          'interval_id' => $collisionStart->interval_id,
          'date_end' => date('Y-m-d', strtotime('-1 Day', $startDateTimestamp)),
        ];
        $updatedIntervals[] = [
          'interval_id' => $collisionEnd->interval_id,
          'date_start' => date('Y-m-d', strtotime('+1 Day', $endDateTimestamp)),
          'price' => $collisionEnd->price,
        ];
        unset($collisionsIds[$collisionStart->interval_id]);
        unset($collisionsIds[$collisionEnd->interval_id]);
        $deletedIntervals = array_keys($collisionsIds);
      }

    }

//    $this->processIntervals($updatedIntervals, $newIntervals, $deletedIntervals);
    return [
      'collisions' => $collisions,
      'updatedIntervals' => $updatedIntervals,
      'newIntervals' => $newIntervals,
      'deletedIntervals' => $deletedIntervals,
    ];
  }

  public function processIntervals(Array $updates, Array $inserts, Array $deletes)
  {

    $this->db->beginTransaction();

    try {
      // making the updates
      foreach ($updates as $update) {
        $interval_id = $update['interval_id'];
        unset($update['interval_id']);
        $updatesCursor = $this->db->prepare('UPDATE intervals SET', $update, 'WHERE interval_id = ?', $interval_id);
        $updatesCursor->execute();

        $data = [
          'name' => $name,
          'surname' => $surname,
          'sex' => $sex,
          'id' => $id,
        ];
        $sql = "UPDATE intervals SET date_start=:name, date_end=:surname, price=:sex WHERE id=:id";
        $stmt= $dpo->prepare($sql);
        $stmt->execute($data);


      }

      var_dump(\dibi::$sql);
      // making the inserts
      $this->db->insert('intervals', $inserts);
//var_dump($inserts);
      var_dump(\dibi::$sql);
      $this->db->query('DELETE FROM intervals WHERE interval_id in (%i)', $deletes);
      var_dump(\dibi::$sql);
//      $this->db->commit();

    } catch (\Exception $e) {
      $this->db->rollBack();
      echo 'Excepción capturada: '.$e->getMessage()."\n";
    }



  }


}