<?php
session_start();
include_once('dbh.inc.php');
include_once('navbar.php');
include_once('class.inc.php');

function print_pre($msg){
    print('<pre>');
    print_r($msg);
    print('</pre>');
}
// Add dates dimension table, corespond to database schema
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

// Create a fact table for attendences, corespond to database schema
$attendences                     = new Fact;
$attendences->tblName            = 'attendences';
$attendences->dimension['dates'] = $datesTbl;
$attendences->dimension['locations'] = $locationsTbl;
$attendences->dimension['organisers'] = $organisersTbl;
$attendences->dimension['events'] = $eventsTbl;
$attendences->dimension['participants'] = $participantsTbl;


// Add the foriegn key for attendences fact table
$attendences->addReference('dates','did');
$attendences->addReference('locations','lid');
$attendences->addReference('organisers','oid');
$attendences->addReference('events','eid');
$attendences->addReference('participants','pid');

// Create the fee measure structure
$measure               = new Measure;
$measure->name         = 'Fee';
$measure->column       = 'fee';
$measure->operation    = 'SUM';
$measure->formatString = '#,###.00';

// add the measure to fact table
$attendences->measure['fee'] = $measure;

// Create attendences measure structure
$measure               = new Measure;
$measure->name         = 'Attendence';
$measure->column       = 'aid';
$measure->operation    = 'SUM';
$measure->formatString = '#,###.00';

$attendences->measure['attendence'] = $measure;

// assign the attendences obj to session
$_SESSION['attendences'] = serialize($attendences);

// if clear btn clicked, clear all dimension and measure
if (isset($_POST['clear'])) {
    session_unset();
    $_SESSION['dimension'][] = "";
    $_SESSION['measure'][]   = "";
}

// add dimension to session array
if (isset($_POST['dimension'])) {
    $input = $_POST['dimension'];
    $dim = explode(".",$input)[0];
    $isSame = false;
    foreach($_SESSION['dimension'] as $x){
        $xdim = explode(".",$x)[0];
        // print_pre($xdim);
        if(strcmp($xdim,$dim) === 0){
            print('<h1>Cannot select same dimension!</h1>');
            $isSame = true;
            break;
        }
    }
    if(!$isSame){
        $_SESSION['dimension'][] = $_POST['dimension'];
    }
    $isSame = false;
}

// add measure to session array
if (isset($_POST['measure'])) {
    $input = $_POST['measure'];
    $dim = explode(".",$input)[0];
    $isSame = false;
    foreach($_SESSION['measure'] as $x){
        $xdim = explode(".",$x)[0];
        // print_pre($xdim);
        if(strcmp($xdim,$dim) === 0){
            print('<h1>Cannot select same measure!</h1>');
            $isSame = true;
            break;
        }
    }
    if(!$isSame){
        $_SESSION['measure'][] = $_POST['measure'];
    }
    $isSame = false;
}

// if create report btn clicked, generate sql and pass to report.php
if (isset($_POST['create'])) {
    $_SESSION['create'][] = $_POST['create'];
    $query = $attendences->generateSQL($false);
    $_SESSION['query'] = $query;
    header("Location: report.php");
    exit();
}

// if deleteDimension is clicked, delete corespond dimension
if (isset($_POST['deleteDimension'])) {
    $del_val = $_POST['remove'];
    $key = array_search($del_val, $_SESSION['dimension']);
    if (false !== $key) {
        unset($_SESSION['dimension'][$key]);
    }
}
// if deleteMeasure is clicked, delete corespond measure
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
        <h1>Attendences Fact</h1>
		<table>
			<tr>
				<td>
					<?php
                        print('<br>');
                        $self = $_SERVER['PHP_SELF'];
                        print('<h3>Measure</h3>');
                        if(isset($_SESSION['measure'])){
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
                        }
                        print('<hr>');
                        print('<h3>Dimension</h3>');
                        if(isset($_SESSION['dimension'])){
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
                        }
                    ?>
                </td>
				<td>
                        <?php 
                            $attendences->printAllMeasure();
                            $attendences->printAllDimension() 
                        ?>

					<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
						<button type="submit" class = 'btn btn-warning' name='clear' value='clear'>Clear</button>
					</form>
					<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
						<button type="submit" class = "btn btn-success"name='create' value='create'>Create Report</button>
					</form>
					<hr> </td>
			</tr>
		</table>
	</body>
</div>
	</html>
