<?php
session_start();
include_once('dbh.inc.php');
Class Attendences{
    public $aid;
    public $did;
    public $pid;
    public $lid;
    public $oid;
    public $status;
    public $fees;
}

class Dimension{
    public $tblName;
    public $pkey;
    public $column = array();
    
    public function __construct($tblName,$pkey){
        $this->tblName = $tblName;
        $this->pkey = $pkey;
    }

    public function addColumn($key,$col){
        $this->column[$key] = $col;
    }

    public function getTblCol($key){
        if (array_key_exists($key,$this->column))
        {
            // echo "Array Key exists...<br>";
            return $this->tblName . "." . $this->column[$key];
        }
        else
        {
            // echo "Array Key does not exist...<br>";
            return false;
        }        
        return false;
    }
}

Class Measure{
    public $name;
    public $column;
    public $operation;
    public $formatString;
}

class Fact{
    public $tblName;
    public $pkey;
    public $dimension;
    public $measure;
    public $agr = array('SUM','AVG','COUNT','MIN','MAX');

    public function get_dimension($key){
        if(array_key_exists($key,$this->dimension)){
            return $this->dimension[$key];
        }
        else{
            return false;
        }
    }

    public function printMeasure($key){
        if(array_key_exists($key,$this->measure)){
            $mea = $this->measure[$key];
            print_r($mea);
            foreach($this->agr as $op){
                $value = $op . "(" . $mea->column . ')';
                print("<option value='$value'>$value</option>");
            }
        }
        else{
            return false;
        }
    }

    public function getMeasure($key){
        if(array_key_exists($key,$this->measure)){
            $mea = $this->measure[$key];
            $tmp = $mea->operation."(".$mea->column.")";
            return $tmp;
        }
        else
            return false;
    }
//     SELECT DISTINCT
//     COUNT(pid),
//     locations.country,
//     locations.state,
//     locations.city,
//     locations.streetaddress
// FROM
//     attendences
// RIGHT JOIN locations ON attendences.lid = locations.locationid
// GROUP BY
//     locations.country,
//     locations.state,
//     locations.city,
//     locations.streetaddress
    public function generateSQL(){
        // Initialize
        $query = 'SELECT DISTINCT ';

        // Set counter and length for measure array
        $counter = 1;
        $len = count($_SESSION['measure']);

        // Adding measure
        foreach($_SESSION['measure'] as $measure){
            if($measure != ""){
                if($counter != $len){
                    $query = $query . $measure . ',';
                }
                else{
                    $query = $query . $measure;
                }
                
            }
            $counter++;
        }
        // Set counter and length for dimension array
        $counter = 1;
        $len = count($_SESSION['dimension']);
        if($len >= 2){
            $query = $query . ",";
        }
        // Adding dimension
        foreach($_SESSION['dimension'] as $dimension){
            if($dimension != ""){
                if($counter != $len){
                    $query = $query . $dimension . ',';
                }
                else{
                    $query = $query . $dimension;
                }
                
            }
            $counter++;
        }
        
        // Add fact table name
        $query = $query . ' FROM ' . $this->tblName . ' ';

        print_r($query);
        return $query;
    }
}


$datesTbl = new Dimension('dates','dateid');
$datesTbl->addColumn('year','year');
$datesTbl->addColumn('quater','quater');
$datesTbl->addColumn('month','month');
$datesTbl->addColumn('day','day');


// $datesTbl->column = array('day'=>'day','month'=>'month','quater'=>'quater','year'=>'year');

$attendences = new Fact;
$attendences->tblName = 'attendences';
$attendences->dimension['dates'] = $datesTbl;

$measure = new Measure;
$measure->name = 'Fee';
$measure->column = 'fee';
$measure->operation = 'SUM';
$measure->formatString='#,###.00';

$attendences->measure['fee'] = $measure;

// $key = array('a','v','b');
// $table = new Dimension;
// for($i=0; $i<3; $i++){
//     $table->column[$key[$i]]=$i;
// }

// foreach ($datesTbl->column as $key => $value){
//     print "{$key} => {$value} <br>";
// }

if(isset($_POST['clear'])){
    session_unset();
    $_SESSION['dimension'][] = "";
    $_SESSION['measure'][] = "";
}
if(isset($_POST['dimension'])){
    $_SESSION['dimension'][]=$_POST['dimension'];
}

if(isset($_POST['measure'])){
    $_SESSION['measure'][]=$_POST['measure'];
}

if(isset($_POST['create'])){
    $_SESSION['create'][]=$_POST['create'];
    $attendences->generateSQL();
}

?>
<!-- Start HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        table, th, td {
            border: 1px solid black;
            padding: 20px;
            width: 70%;
            
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td>
<?php
// print_r($_SESSION['dimension']);
print('<br>');
print('<h3>Dimension</h3>');
foreach ($_SESSION['dimension'] as $key => $value){
    if($value!=""){
        print "{$key} => {$value} <br>";
    }
}
print('<h3>Measure</h3>');
foreach ($_SESSION['measure'] as $key => $value){
    if($value!=""){
        print "{$key} => {$value} <br>";
    }
}
print('<br>');
 ?>  
 <hr>
 </td>
 <td>
    <h3>Measure</h3>
    <p>Fee</p>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <select name="measure" id="measure">
            <?php
                    // foreach ($attendences->measure as $key => $mea){
                    //     $value = $attendences->getMeasure($key);
                    //     print "<option value='$value'>$value</option>";
                    // }
                    $attendences->printMeasure('fee');
            ?>
        </select>
        <input type="submit" value="Add">
    </form>


    <h3>Dimension</h3>
    <p>Dates</p>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <select name="dimension" id="dimension">
            <?php
                foreach ($attendences->get_dimension('dates')->column as $key => $value){
                    $value = $datesTbl->getTblCol($key);
                    print "<option value='$value'>$value</option>";
                }
            ?>
        </select>
        <input type="submit" value="Add">
    </form>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <button type="submit" name='clear' value='clear'>Clear</button>
    </form>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <button type="submit" name='create' value='create'>Create Report</button>
    </form>
    <hr>
    </td>
    </tr>
    </table>
</body>
</html>