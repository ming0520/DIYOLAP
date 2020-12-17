<?php
include_once('dbh.inc.php');
Class Report extends Dbh{
    
    public $query;
    public $result;

    public function getResult(){
        $stmt = $this->connect()->prepare($this->query);
        $stmt->execute();
        $this->result  = $stmt;
        print'<pre>';
        print_r($this->result->rowCount());
        print'</pre>';
    } 
}

$query = 'SELECT DISTINCT COUNT(aid),dates.year FROM attendences RIGHT JOIN dates ON attendences.did=dates.dateid GROUP BY dates.year';
$report = new Report;
$report->query = $query;
$report->getResult();

?>
<!-- SELECT DISTINCT COUNT(aid),dates.year FROM attendences RIGHT JOIN dates ON attendences.did=dates.dateid GROUP BY dates.year -->