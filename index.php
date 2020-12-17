<?php
session_start();
include_once('dbh.inc.php');
include_once('navbar.php');
class Attendences
{
    public $aid;
    public $did;
    public $pid;
    public $lid;
    public $oid;
    public $status;
    public $fees;
}

class Dimension
{
    public $tblName;
    public $pkey;
    public $column = array();
    
    public function __construct($tblName, $pkey)
    {
        $this->tblName = $tblName;
        $this->pkey    = $pkey;
    }
    
    public function addColumn($key, $col)
    {
        $this->column[$key] = $col;
    }
    
    public function getTblCol($key)
    {
        if (array_key_exists($key, $this->column)) {
            // echo "Array Key exists...<br>";
            return $this->tblName . "." . $this->column[$key];
        } else {
            // echo "Array Key does not exist...<br>";
            return false;
        }
        return false;
    }

    public function printDimension(){
        if(empty($this->column)){
            print "<h1>Dimension column is empty!</h1>";
            return false;
        }
        $cols = $this->column;
        $tblName = $this->tblName;
        foreach($cols as $col){
            $value = $tblName."." . $col;
            print("<option value='$value'>$value</option>");
        }
    }
}

function print_pre($msg){
    print('<pre>');
    print_r($msg);
    print('</pre>');
}

class Measure
{
    public $name;
    public $column;
    public $operation;
    public $formatString;
}

class Fact
{
    public $tblName;
    public $pkey;
    public $dimension;
    public $measure;
    public $reference;
    public $agr = array('SUM', 'AVG', 'COUNT', 'MIN', 'MAX');

    public function printAllMeasure(){
        print("<h3>Measure</h3>");
        foreach($this->measure as $key => $mea){ 
            $self = $_SERVER['PHP_SELF'];
            print("<form action='$self' method='post'>");
            print('<label>'.ucfirst($mea->name).'</label>&nbsp;:&nbsp;');
            print("<select name='measure' id='measure'>");
            $this->printMeasure($key);
            print('</select>');
            print('<input type="submit" value="Add"> ');
            print('</form>');
        }
    }

    public function printAllDimension(){
        print("<h3>Dimension</h3>");
        foreach($this->dimension as $key => $dim){ 
            $self = $_SERVER['PHP_SELF'];
            print("<form action='$self' method='post'>");
            print('<label>'.ucfirst($dim->tblName).'</label>&nbsp;:&nbsp;');
            print("<select name='dimension' id='dimension'>");
            $dim->printDimension();
            print('</select>');
            print('<input type="submit" value="Add"> ');
            print('</form>');
        }
    }

    public function addReference($key,$value){
        $this->reference[$key] = $value;
    }
    
    public function get_dimension($key)
    {
        if (array_key_exists($key, $this->dimension)) {
            return $this->dimension[$key];
        } else {
            return false;
        }
    }
    
    public function printMeasure($key)
    {
        if (array_key_exists($key, $this->measure)) {
            $mea = $this->measure[$key];
            print_r($mea);
            foreach ($this->agr as $op) {
                $value = $op . "(" . $mea->column . ')';
                print("<option value='$value'>$value</option>");
            }
        } else {
            return false;
        }
    }
    
    public function getMeasure($key)
    {
        if (array_key_exists($key, $this->measure)) {
            $mea = $this->measure[$key];
            $tmp = $mea->operation . "(" . $mea->column . ")";
            return $tmp;
        } else
            return false;
    }
    public function generateSQL()
    {
        // Initialize
        $query = 'SELECT DISTINCT ';
        $join = ' RIGHT JOIN ';
        
        // Set counter and length for measure array
        $counter = 1;
        $len     = count($_SESSION['measure']);
        
        // Adding measure
        foreach ($_SESSION['measure'] as $measure) {
            if ($measure != "") {
                if ($counter != $len) {
                    $query = $query . $measure . ',';
                } else {
                    $query = $query . $measure;
                }
                
            }
            $counter++;
        }
        // Set counter and length for dimension array
        $counter = 1;
        $len     = count($_SESSION['dimension']);
        if ($len >= 1) {
            $query = $query . ",";
        }
        // Adding dimension
        foreach ($_SESSION['dimension'] as $dimension) {
            if ($dimension != "") {
                if ($counter != $len) {
                    $query = $query . $dimension . ',';
                } else {
                    $query = $query . $dimension;
                }
                
            }
            $counter++;
        }
        
        // Add fact table name
        $query = $query . ' FROM ' . $this->tblName . ' ';
        
        $tables = array();

        foreach ($_SESSION['dimension'] as $dimension){
            $tables[] = explode('.',$dimension)[0];
        }
        // https://www.tutorialrepublic.com/faq/how-to-remove-empty-values-from-an-array-in-php.php#:~:text=You%20can%20simply%20use%20the,array%20using%20a%20callback%20function.
        $tables = array_filter($tables);
        // https://www.php.net/manual/en/function.array-unique.php
        $uniques = array_unique($tables);

        // Adding join query
        foreach($uniques as $unique){
            $query = $query .
            $join. $unique. " ON " . 
            $this->tblName . ".". $this->reference[$unique] . "=".
            $unique."." .$this->dimension[$unique]->pkey
            ;
        }
        $query = $query . ' GROUP BY ';

        $_SESSION['dimension'] = array_filter($_SESSION['dimension']);
        $counter = 1;
        $len = count($_SESSION['dimension']);

        foreach($_SESSION['dimension'] as $dimension){
            if($counter != $len){
                $query = $query . $dimension . ",";
            }
            else{
                $query = $query . $dimension;
            }
            
            $counter++;
        }
        // print_pre($uniques);
        print_r("<hr>");
        print_r($query);
        return $query;
    }
}

$datesTbl = new Dimension('dates', 'dateid');
$datesTbl->addColumn('year', 'year');
$datesTbl->addColumn('quater', 'quater');
$datesTbl->addColumn('month', 'month');
$datesTbl->addColumn('day', 'day');


$locationsTbl = new Dimension('locations', 'locationid');
$locationsTbl->addColumn('country', 'country');
$locationsTbl->addColumn('oemail', 'state');
$locationsTbl->addColumn('city', 'city');
$locationsTbl->addColumn('streetaddress', 'streetaddress');

$organisersTbl = new Dimension('organisers', 'organiserid');
$organisersTbl->addColumn('oname', 'oname');
$organisersTbl->addColumn('oemail', 'oemail');
$organisersTbl->addColumn('odetails', 'odetails');

$eventsTbl = new Dimension('events', 'eventid');
$eventsTbl->addColumn('ecategory', 'ecategory');
$eventsTbl->addColumn('etype', 'etype');
$eventsTbl->addColumn('ename', 'ename');

$participantsTbl = new Dimension('participants', 'participantid');
$participantsTbl->addColumn('pcategory', 'pcategory');
$participantsTbl->addColumn('ptype', 'ptype');
$participantsTbl->addColumn('gender', 'gender');
$participantsTbl->addColumn('pname', 'pname');

// $datesTbl->column = array('day'=>'day','month'=>'month','quater'=>'quater','year'=>'year');
$attendences                     = new Fact;
$attendences->tblName            = 'attendences';
$attendences->dimension['dates'] = $datesTbl;
$attendences->dimension['locations'] = $locationsTbl;
$attendences->dimension['organisers'] = $organisersTbl;
$attendences->dimension['events'] = $eventsTbl;
$attendences->dimension['participants'] = $participantsTbl;

$attendences->addReference('dates','did');
$attendences->addReference('locations','lid');
$attendences->addReference('organisers','oid');
$attendences->addReference('events','eid');
$attendences->addReference('participants','pid');

$measure               = new Measure;
$measure->name         = 'Fee';
$measure->column       = 'fee';
$measure->operation    = 'SUM';
$measure->formatString = '#,###.00';

$attendences->measure['fee'] = $measure;

$measure               = new Measure;
$measure->name         = 'Attendence';
$measure->column       = 'aid';
$measure->operation    = 'SUM';
$measure->formatString = '#,###.00';

$attendences->measure['attendence'] = $measure;

if (isset($_POST['clear'])) {
    session_unset();
    $_SESSION['dimension'][] = "";
    $_SESSION['measure'][]   = "";
}
if (isset($_POST['dimension'])) {
    $_SESSION['dimension'][] = $_POST['dimension'];
}

if (isset($_POST['measure'])) {
    $_SESSION['measure'][] = $_POST['measure'];
}

if (isset($_POST['create'])) {
    $_SESSION['create'][] = $_POST['create'];
    $attendences->generateSQL();
}

if (isset($_POST['deleteDimension'])) {
    $del_val = $_POST['remove'];
    $key = array_search($del_val, $_SESSION['dimension']);
    if (false !== $key) {
        unset($_SESSION['dimension'][$key]);
    }
}

if (isset($_POST['deleteMeasure'])) {
    $del_val = $_POST['remove'];
    $key = array_search($del_val, $_SESSION['measure']);
    if (false !== $key) {
        unset($_SESSION['measure'][$key]);
    }
}


?>
	<!-- Start HTML -->
	<!DOCTYPE html>
	<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Data Mining</title>
		<style>
        table{
            width:80%;
        }
		table,
		th,
		td {
			border: 1px solid black;
			padding: 20px;
		}
		</style>
	</head>
<div class="container">
	<body>
		<table>
			<tr>
				<td>
					<?php
                        // print_r($_SESSION['dimension']);
                        print('<br>');
                        $self = $_SERVER['PHP_SELF'];
                        print('<h3>Measure</h3>');
                        foreach ($_SESSION['measure'] as $key => $value) {
                            if ($value != "") {
                                print("<form action='$self' method='POST'>");
                                print "{$key} => {$value}";
                                print("<input type='text' hidden value='$value' name='remove'>");
                                $btn = "<button type='submit' id='del-btn' name='deleteMeasure' class='btn-danger'>Delete</button>";
                                print($btn);
                                print('</form>');
                            }
                        }
                        print('<hr>');
                        print('<h3>Dimension</h3>');
                        foreach ($_SESSION['dimension'] as $key => $value) {
                            if ($value != "") {
                                print("<form action='$self' method='POST'>");
                                print "{$key} => {$value}";
                                print("<input type='text' hidden value='$value' name='remove'>");
                                $btn = "<button type='submit' id='del-btn' name='deleteDimension' class='btn-danger'>Delete</button>";
                                print($btn);
                                print('</form>');
                            }
                        }

                        
                    ?>
                </td>
				<td>
                        <?php 
                            $attendences->printAllMeasure();
                            $attendences->printAllDimension() 
                        ?>

					<form action="<?php
echo $_SERVER['PHP_SELF'];
?>" method="POST">
						<button type="submit" name='clear' value='clear'>Clear</button>
					</form>
					<form action="<?php
echo $_SERVER['PHP_SELF'];
?>" method="POST">
						<button type="submit" name='create' value='create'>Create Report</button>
					</form>
					<hr> </td>
			</tr>
		</table>
	</body>
</div>
	</html>
