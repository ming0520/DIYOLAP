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

$teamsTbl = new Dimension('teams', 'teamid');
$teamsTbl->addColumn('tname', 'tname');

$participantsTbl = new Dimension('participants', 'participantid');
$participantsTbl->addColumn('pcategory', 'pcategory');
$participantsTbl->addColumn('ptype', 'ptype');
$participantsTbl->addColumn('gender', 'gender');
$participantsTbl->addColumn('pname', 'pname');

// $datesTbl->column = array('day'=>'day','month'=>'month','quater'=>'quater','year'=>'year');
$attendences                     = new Fact;
$attendences->tblName            = 'hasteam';
$attendences->dimension['teams'] = $teamsTbl;
$attendences->dimension['participants'] = $participantsTbl;

$attendences->addReference('teams','tid');
$attendences->addReference('participants','pid');

$measure               = new Measure;
$measure->name         = 'Team';
$measure->column       = 'tid';
$measure->operation    = 'SUM';
$measure->formatString = '#,###.00';

$attendences->measure['teams'] = $measure;

$measure               = new Measure;
$measure->name         = 'Participant';
$measure->column       = 'pid';
$measure->operation    = 'SUM';
$measure->formatString = '#,###.00';

$attendences->measure['participants'] = $measure;

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
    $_SESSION['attendences'] = serialize($attendences);
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
    <h1>Has Team Fact</h1>
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
