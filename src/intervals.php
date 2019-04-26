<?php

namespace scodx;

class intervals
{

  /**
   * db access layer to use
   * @var db
   */
  private $db;

  /**
   * intervals constructor.
   */
  public function __construct ()
  {
    // just injecting the dbal into this class to later use
    $db = new db();
    $this->db = $db;
  }

  /**
   * Performs a search between ranges, it directly uses the method
   * with the same name from the dbal
   * @param $startDate
   * @param $endDate
   * @return mixed[]
   */
  public function search($startDate, $endDate)
  {
    return $this->db->search($startDate, $endDate);
  }

  /**
   * @param $startDate
   * @param $endDate
   * @param $price
   * @return array
   * @throws \Exception
   */
  public function add($startDate, $endDate, $price)
  {
    $db = $this->db;
    $collisions = $db->searchCollisions($startDate, $endDate, $price);
    $collisionsTotal = count($collisions);
    $multipleCollisions = ($collisionsTotal > 1);
    // converting startDate to timestamp
    $startDateTimestamp = strtotime($startDate);
    $endDateTimestamp = strtotime($endDate);

    $startDateMinus1Day = $this->formatDateStr('-1 Day', $startDateTimestamp);
    $startDatePlus1Day = $this->formatDateStr('+1 Day', $startDateTimestamp);
    $endDateMinus1Day = $this->formatDateStr('-1 Day', $endDateTimestamp);
    $endDatePlus1Day = $this->formatDateStr('+1 Day', $endDateTimestamp);

    $newPrice = floatval($price);
    $collisionsIds = [];

    $newIntervals = [];
    $updatedIntervals = [];
    $deletedIntervals = [];

    if (!empty($collisions)) {

      $collisionStart = $collisions[0];
      $collisionStartDateTimestamp = strtotime($collisionStart->date_start);

      $collisionEnd = $collisions[$collisionsTotal-1];
      $collisionEndDateTimestamp = strtotime($collisionEnd->date_end);

      foreach ($collisions as $collision) {
        $collisionsIds[$collision->interval_id] = $collision->interval_id;
      }
      $generateDeletedIds = function (Array $idsToExclude) use ($collisionsIds){
        $ret = $collisionsIds;
        foreach ($idsToExclude as $id) {
          unset($ret[$id]);
        }
        return array_keys($ret);
      };

      // do we have an edge collision meaning same price, then we merge it
      if ($multipleCollisions && $collisionStart->price == $collisionEnd->price && $collisionEnd->price == $price) {
        $updatedIntervals[] = $db->generateUpdateArray([
          'date_end' => $collisionEnd->date_end,
        ], $collisionStart->interval_id);
        $deletedIntervals = $generateDeletedIds([$collisionStart->interval_id]);

      } elseif ($collisionStart->price == $price) {
        $updatedIntervals[] = $db->generateUpdateArray([
          'date_end' => $endDate,
        ], $collisionStart->interval_id);

        // if we are dealing with multiple collisions then we need to merge the edges and delete the in betweens accordingly
        if ($multipleCollisions) {
          if ($collisionEndDateTimestamp >= $endDateTimestamp) {
            $updatedIntervals[] = $db->generateUpdateArray([
              'date_start' => $endDatePlus1Day,
            ], $collisionEnd->interval_id);
            $deletedIntervals = $generateDeletedIds([$collisionStart->interval_id, $collisionEnd->interval_id]);
          }
        }

      // do we have an edge collision meaning same price, then we merge it
      } elseif ($collisionEnd->price == $price) {
        $updatedIntervals[] = $db->generateUpdateArray([
          'date_start' => $startDate,
        ], $collisionEnd->interval_id);

        // if we are dealing with multiple collisions then we need to merge the edges and delete the in between accordingly
        if ($multipleCollisions) {
          if ($collisionEndDateTimestamp >= $endDateTimestamp) {
            $updatedIntervals[] = $db->generateUpdateArray([
              'date_end' => $startDateMinus1Day,
            ], $collisionStart->interval_id);
            $deletedIntervals = $generateDeletedIds([$collisionStart->interval_id, $collisionEnd->interval_id]);
          }
        }

      // if the collision is between the start date and end date
      } elseif ($startDateTimestamp > $collisionStartDateTimestamp && $endDateTimestamp < $collisionEndDateTimestamp) {
        // only if the price is different
        if ($collisionStart->price != $price) {
          $updatedIntervals[] = $db->generateUpdateArray([
            'date_end' => $startDateMinus1Day,
          ], $collisionStart->interval_id);

          $newIntervals[] = [
            'date_start' => $startDate,
            'date_end' => $endDate,
            'price' => $newPrice,
          ];
          if ($multipleCollisions) {
            $updatedIntervals[] = $db->generateUpdateArray([
              'date_start' => $endDatePlus1Day,
            ], $collisionEnd->interval_id);
            $deletedIntervals = $generateDeletedIds([$collisionStart->interval_id, $collisionEnd->interval_id]);
          } else {
            $newIntervals[] = [
              'date_start' => $startDatePlus1Day,
              'date_end' => $collisionStart->date_end,
              'price' => $collisionStart->price,
            ];
          }
        } else {
          if ($multipleCollisions) {
            // merge start collision
            $updatedIntervals[] = $db->generateUpdateArray([
              'date_end' => $endDate,
            ], $collisionStart->interval_id);
            $updatedIntervals[] = $db->generateUpdateArray([
              'date_start' => $endDatePlus1Day,
            ], $collisionEnd->interval_id);
            $deletedIntervals = $generateDeletedIds([$collisionStart->interval_id, $collisionEnd->interval_id]);
          }
        }

      // if the collision is between the start date
      } elseif ($startDateTimestamp <= $collisionStartDateTimestamp) {
        // different price? resize collision and add new interval
        $newStartDate = ($startDateTimestamp <= $collisionStartDateTimestamp) ? $startDate : $collisionStart->date_start;
        if ($collisionStart->price != $price) {
          $newIntervals[] = [
            'date_start' => $newStartDate,
            'date_end' => $endDate,
            'price' => $newPrice,
          ];

          if ($multipleCollisions) {
            $updatedIntervals[] = $db->generateUpdateArray([
              'date_start' => $endDatePlus1Day,
              'date_end' => $collisionEnd->date_end,
            ], $collisionEnd->interval_id);
            $deletedIntervals = $generateDeletedIds([$collisionEnd->interval_id]);
          } else {
            $updatedIntervals[] = $db->generateUpdateArray([
              'date_start' => $endDatePlus1Day,
            ], $collisionStart->interval_id);
          }

        // same price? then update start date
        } else {
          $updatedIntervals[] = $db->generateUpdateArray([
            'date_start' => $newStartDate,
          ], $collisionStart->interval_id);
        }

      // if the collision is between the end date
      } elseif ($endDateTimestamp <= $collisionEndDateTimestamp || $endDateTimestamp > $collisionEndDateTimestamp) {
        $newEndDate = ($endDateTimestamp <= $collisionEndDateTimestamp) ? $collisionEnd->date_end : $endDate;
        // different price? resize collision and add new interval
        if ($collisionEnd->price != $price) {
          $updatedIntervals[] = $db->generateUpdateArray([
            'date_end' => $startDateMinus1Day,
          ], $collisionEnd->interval_id);

          $newIntervals[] = [
            'date_start' => $startDate,
            'date_end' => $newEndDate,
            'price' => $price,
          ];
          if ($multipleCollisions) {
            $deletedIntervals = $generateDeletedIds([$collisionEnd->interval_id]);
          }

          // same price? then update start date
        } else {
          $updatedIntervals[] = $db->generateUpdateArray([
            'date_end' => $newEndDate,
          ], $collisionStart->interval_id);
        }
      }

    } else {

      // no collisions!!!
      $newIntervals[] = [
        'date_start' => $startDate,
        'date_end' => $endDate,
        'price' => $newPrice,
      ];
    }

    $db->processOperations($updatedIntervals, $newIntervals, $deletedIntervals);
    return [
      'collisions' => $collisions,
      'updatedIntervals' => $updatedIntervals,
      'newIntervals' => $newIntervals,
      'deletedIntervals' => $deletedIntervals,
    ];
  }

  /**
   * @param $dateString
   * @param $timestamp
   * @return false|string
   */
  private function formatDateStr($dateString, $timestamp)
  {
    return date('Y-m-d', strtotime($dateString, $timestamp));
  }


  public function deleteInterval($intervalId)
  {
    return $this->db->getConn()->delete('intervals', ['interval_id' => $intervalId]);
  }

  public function deleteAllIntervals()
  {
    return $this->db->deleteAll();
  }

}