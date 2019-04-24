<?php


namespace scodx;


use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

class db extends manager
{
  private $host = 'localhost';
  private $username = 'root';
  private $password = 'root';
  private $database = 'cloudbeds_test';
  private $port = 3306;

  private $conn;

  public function __construct ()
  {

    $config = new Configuration();
    $connectionParams = array(
      'dbname' => $this->database,
      'user' => $this->username,
      'password' => $this->password,
      'host' => $this->host,
      'port' => $this->port,
      'driver' => 'pdo_mysql',
    );
    $this->conn = DriverManager::getConnection($connectionParams, $config);
  }

  public function search($startDate, $endDate)
  {
    return $this->fetchAll(
      "SELECT interval_id, date_start, date_end, price FROM intervals 
      WHERE (date_start BETWEEN :startDate AND :endDate	)
        or (date_end BETWEEN :startDate AND :endDate	);",
      [':startDate' => $startDate, ':endDate' => $endDate,]
    );
  }

  public function fetchAll($sql, $params)
  {
    $statement = $this->conn->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
  }

  public function processOperations(Array $updates, Array $inserts, Array $deletes)
  {
    $conn = $this->conn
    $conn->beginTransaction();
    try{

      foreach ($updates as $update) {
        $interval_id = $update['interval_id'];
        unset($update['interval_id']);
        $conn->update('intervals', )
      }

      $conn->commit();
    } catch (\Exception $e) {
      $conn->rollBack();
      throw $e;
    }
  }


}