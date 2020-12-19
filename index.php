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

// hasTeam

$_SESSION['attendences'] = serialize($attendences);

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
    $query = $attendences->generateSQL($false);
    $_SESSION['query'] = $query;
    header("Location: report.php");
    exit();
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
        <h1>Attendences Fact</h1>
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
