<?php
include_once('dbh.inc.php');
include_once('navbar.php');
include_once('class.inc.php');
session_start();

function print_pre($msg){
    print('<pre>');
    print_r($msg);
    print('</pre>');
}

Class CubeList{
    public $measure;
    public $dimension;
}

Class Slice{
    public $dimension;
    public $value;
}
/*+----------------------------------------------------------------------
 ||
 ||  Class Dimension
 ||
 ||         Author:  Zhong Ming Tan
 ||
 ||        Purpose:  easy use fact class to replace xml
 ||
 ||  Inherits From:  Class Dimension, Class Measure, Class Pivot, Class Fact
 ||
 ||     Interfaces:  No
 ||
 |+-----------------------------------------------------------------------
 ||
 ||      Constants:  Yes
 ||
 |+-----------------------------------------------------------------------
 ||
 ||   Constructors: None
 ||
 ||  Class Methods:
 ||  Inst. Methods:  
 ||                 -getResult(query):None
 ||                 -printOptValue(array):None
 ||                 -printPivotForm(array,str,str): none
 ||                 -printPivotMenu():None
 ||                 -generatePivot():None
 ||                 -printResult():None
 ||
 ++-----------------------------------------------------------------------*/
Class Report extends Dbh{
    
    public $query;
    public $result;
    public $column;
    public $cube;

    public function __construct(){
        $_SESSION['dimension'] = array_filter($_SESSION['dimension']);
        $_SESSION['measure'] = array_filter($_SESSION['measure']);
        $this->cube = new CubeList;
    }
// execute the query that provided by Fact Class
    public function getResult($query){
        if(!empty($query)){
            $this->query = $query;
        }
        $stmt = $this->connect()->prepare($this->query);
        $stmt->execute();
        $this->result  = $stmt;

        $totalColumn = $this->result->columnCount();
        for($counter = 0; $counter < $totalColumn; $counter++){
            $meta = $this->result->getColumnMeta($counter);
            $this->column[] = $meta['name'];
        }
    }
// print option value for pivot class
    public function printOptValue($array){
        if(empty($array)){
            print "<h1>Array is empty!</h1>";
            return false;
        }
        foreach($array as $key => $value){
            print("<option value='$value'>$value</option>");
        }

    }
// print pivot form for each row in pivoting table
    public function printPivotForm($input,$formName,$formLabel){
        $self = $_SERVER['PHP_SELF'];
        print("<form action='$self' method='post'>");
                print('<label>'.ucfirst($formLabel).'</label>');
                print('<div class="form-row">');
                        print('<div class="form-group col-md-8">');
                                print("<select class='custom-select' name='$formName' id='$formName'>");
                                    $this->printOptValue($input);
                                print('</select>');
                        print('</div>');
                        print('<div class="form-group col-md-2">');
                                print('<input class = "btn btn-primary" type="submit" value="Add"> ');
                        print('</div>');
                print('</div>');
        print('</form>');
    }
// print whole pivot column by calling printPivotForm function
    public function printPivotMenu(){
        $measures = array_filter($_SESSION['measure']);
        $dimensions = array_filter($_SESSION['dimension']);
        if(isset($_SESSION['slice'])){
            $slices = array_filter($_SESSION['slice']);
        }
        $this->printPivotForm($measures,'pivotMea','measures');
        $this->printPivotForm($dimensions,'pivotRow','Row');
        $this->printPivotForm($dimensions,'pivotCol','Column');

        $self = $_SERVER['PHP_SELF'];
// produce add pivot value form
        print("<form action='$self' method='post'>");
        print('<label>'.ucfirst('pivot value').'</label>');
        print('<div class="form-row">');
            print('<div class="form-group col-md-4">');
                    print("<select class='custom-select' name='pivotValueKey' id='pivotValueKey'>");
                        $optValue = $_SESSION['pivotCol'];
                        print("<option value='$optValue'>$optValue</option>");
                    print('</select>');
            print("</div>");
            print('<div class="form-group col-md-4">');
                    print('<input type="text" name="pivotValue" id="pivotValue" class="form-control" placeholder="Value">');
            print("</div>");
            print('<div class="form-group col-md-2">');
                    print('<input class = "btn btn-primary" type="submit" value="Add"> ');
            print("</div>");
            
        print("</div>");
        print('</form>');
    }

// process pivot query and display result
    public function generatePivot(){
        // convert session to variable
        $pivotMea = $_SESSION['pivotMea'];
        $pivotRow = $_SESSION['pivotRow'];
        $pivotCol = $_SESSION['pivotCol'];
        
        if(isset($_SESSION['pivotValue'])){
            $pivotValues = array_filter($_SESSION['pivotValue']);
        }
        //get measure operation and column name
        $meaOp = explode('(',$pivotMea)[0];
        $meaName = explode('(',$pivotMea)[1];
        $meaName = explode(')',$meaName)[0];

        $rowTbl = explode('.',$pivotRow)[0];
        $colTbl = explode('.',$pivotCol)[0];

        $rowCol = explode('.',$pivotRow)[1];
        $colCol = explode('.',$pivotCol)[1];

        // Get selected dimension unique value to pivott
        // Add select to qeury
        $query = 'SELECT DISTINCT '. $colCol. ' FROM '. $colTbl;
        // Add where statement if exists
        if(!empty($pivotValues)){
            $query .= " WHERE ";
            foreach($pivotValues as $key => $pivotValue){
                $query .= $key." IN (";
                foreach($pivotValue as $ky=>$pivot){
                    $query.= "'".$pivot."'";
                    if($pivot != end($pivotValue)){
                        $query.=",";
                    }
                }
                $query .= ") ";
                if($pivotValue != end($pivotValues)){
                    $query.=" AND ";
                }
            }
        }

        // Add order by for selected dimension to pivot
        $query .= ' ORDER BY '.$colCol;

        // Start PDO connection
        $colStmt = $this->connect()->prepare($query);
        $colStmt->execute();

        // get row count
        if($colStmt->rowCount()){
            while($row = $colStmt->fetch()){
                $column[] = $row[$colCol];
            }
        }

        // initialize & reset variable
        $query = "";

        // Initialize select and join variable
        $select = "SELECT DISTINCT ";
        $join = " RIGHT JOIN ";

        // add select statement
        $query .= $select;
        $query .= $rowTbl .".". $rowCol.",";
        // make the selected unique value to column (rotate)
        foreach($column as $col){
            $str = " ".$meaOp."(CASE WHEN ". $colCol."='".$col."'". " THEN ". $meaName ." ELSE NULL END".")". " AS '".$col."'";
            $query .= $str;
            if($col == end($column)){

            }else{
                $query .=",";
            }
        } 

        // Add from table statement
        $query .= " FROM ";
        $attendences = unserialize($_SESSION['attendences']);
        $query .= $attendences->tblName . " ";

        // if row and column from same dimension
        if($colTbl == $rowTbl){
            $dim = $attendences->dimension[$colTbl];
            $ref = $attendences->tblName.".".$attendences->reference[$colTbl];
            $colKey = $dim->tblName.".".$dim->pkey;
            $query .= $join. $dim->tblName ." ON ". $ref . " = ".$colKey;
        }
        // if row and col is not from same dimension
        else{
            $dim = $attendences->dimension[$colTbl];
            $ref = $attendences->tblName.".".$attendences->reference[$colTbl];
            $colKey = $dim->tblName.".".$dim->pkey;
            $query .= $join. $dim->tblName ." ON ". $ref . " = ".$colKey;

            $dim = $attendences->dimension[$rowTbl];
            $ref = $attendences->tblName.".".$attendences->reference[$rowTbl];
            $rowKey = $dim->tblName.".".$dim->pkey;
            $query .= $join. $dim->tblName ." ON ". $ref . " = ".$rowKey;
        }

        // Add group by statement
        $query .= " GROUP BY ";
        // Group by row
        $query .= $pivotRow;


        
        // Start PDO connection
        $pivotStmt = $this->connect()->prepare($query);
        $pivotStmt->execute();

        // get number of column
        $ttlCol = $pivotStmt->columnCount();
        $column = array();

        // assign column name to column array
        for ($counter = 0; $counter < $ttlCol; $counter ++) {
            $meta = $pivotStmt->getColumnMeta($counter);
            $column[] = $meta['name'];
        }
        
        // initialize variable
        $result = array();

        // assign each column values to variable - result by column
        if($pivotStmt->rowCount()){
            while($row = $pivotStmt->fetch()){
                foreach($column as $col){
                    $result[$col][] = $row[$col];
                }
            }

            // display dynamically
            // get row count
            $rowCount = $pivotStmt->rowCount();
            print('<div class="container-fluid">');
            print('<div class="table-responsive table-responsive-sm">');
            print "<table class='table table-striped table-hover'>";
            print '<thead class=""><tr>';
            // skip for first 2 column
                print '<tr>';
                for($j=0;$j<2;$j++){
                    print '<th>'.'</th>';
                }
                // colspan for column by total number of column - 1
                $colspan = $ttlCol - 1;
                print "<th colspan=$colspan>".$colCol."</th>";
                print '</tr>';
                    print("<th scope='row'> Count </th>");
                    foreach($column as $col){
                        print '<th>'.$col.'</th>';
                    }
                print '</tr>';
                print '</thead>';
                // display all the result
                for($i=0; $i < $rowCount; $i++){
                    print('<tr>');
                    print("<td scope='row'>".$i."</td>");
                    foreach($column as $col){
                        print('<td>'.$result[$col][$i].'</td>');
                    }
                    print('</tr>');
                }
            print '</table>';
            print '</div>';
            print '</div>';

        }
        
        return $query;
    }

    public function drillAcross(){
        // convert session to variable and unpack session object
        $attendences = unserialize($_SESSION['attendences']);
        $hasteam = unserialize($_SESSION['hasteam']);
        $commonDimension = $_SESSION['commonDimension'];
        $commonTbl = explode('.',$commonDimension)[0];
        $measures1 = $_SESSION['measure'];
        $measures2 = $_SESSION['hasTeamMeasure'];
        $slices = $_SESSION['slice'];
        $mea2Col = explode('(',$measures2)[1];
        $mea2Col = explode(')',$mea2Col)[0];

        // Initialize query statement that will use in this function
        $select = 'SELECT DISTINCT ';
        $join = ' INNER JOIN ';
        $group = ' GROUP BY ';
        $on = ' ON ';
        $equal = ' = ';
        $apos = "'"; //apostrophe
        $from = " FROM ";
        
        // start generate query by adding select statement
        $firstQuery = $select;
        $firstQuery .= ' * '.$from;
        $firstQuery .= $attendences->tblName." ";
        
        // If there is slice and dice
        if(!empty($slices)){
            // get all tableName for slice and dice
            foreach($slices as $key => $slice){
                $keyArr[] = $slice->dimension;
            }
            // get the unique table from key array
            $uniqueKey = array_unique($keyArr);
            foreach($uniqueKey as $ky){
                foreach($slices as $key => $slice){
                    if($ky == $slice->dimension){
                        $val[$ky][] = $slice->value;
                    }
                }
            }
            // join the dimension for slice
            foreach($uniqueKey as $ky){
                $firstQuery .= $join;
                $refTbl = explode('.',$ky)[0];
                $firstQuery .= $refTbl;
                $firstQuery .= $on;
                $firstQuery .= $attendences->tblName . '.' . $attendences->reference[$refTbl];
                $firstQuery .= $equal;
                $firstQuery .= $refTbl .'.'.$attendences->dimension[$refTbl]->pkey." ";
            }

            // add where statement if there is slice
            // loop through all the value
            $firstQuery .= ' WHERE ';
            foreach($val as $dim => $values){
                $firstQuery .= $dim;
                $firstQuery .= ' IN (';
                foreach($values as $index=>$value){
                    $firstQuery .= $apos.$value.$apos;
                    if($value != end($values)){
                        $firstQuery .= ",";
                    }
                }
                $firstQuery .= ")";
                if($values != end($val)){
                    $firstQuery .= " AND ";
                }
            }
        }

        // Start generating outer query
        // outer query
        $secondQuery = 'SELECT ';

        // add measure from fact table 1
        if(!empty($measures1)){
            foreach($measures1 as $measure){
                $secondQuery .= $measure;
                if($measure != end($measures1)){
                    $secondQuery .= ",";
                }
            }
        }

        // add the selected common dimension and loop until the highest hierarchy
        $dimension = $attendences->dimension[$commonTbl];
        $cd = explode('.',$commonDimension)[1];
        $arrKey = array_keys($dimension->column);
        $index = array_search($cd,$arrKey);
        
        while($index != -1 ){
            $secondQuery.=',';
            $secondQuery .= $dimension->column[$arrKey[$index]]." ";
            $index--;
        }

        // add measure from fact table 2
        if(!empty($measures2)){
            $secondQuery .= ",";
            $secondQuery .= $measures2;

        }


        // concat the inner query to outer query
        $secondQuery .= $from;
        $secondQuery .= "( ";
        $secondQuery .= $firstQuery;
        $secondQuery .= ") as tbl";
        

        // join the unique table with second fact and common dimension
        $secondQuery .= $join;
        $secondQuery .= $hasteam->tblName . $on;
        $secondQuery .= 'tbl'.".";
        $secondQuery .= $attendences->reference[$commonTbl];
        $secondQuery .= $equal;
        $secondQuery .= $hasteam->tblName.".".$hasteam->reference[$commonTbl];

        $secondQuery .= $join;
        $secondQuery .= $commonTbl . $on;
        $secondQuery .= $hasteam->tblName.".".$hasteam->reference[$commonTbl];
        $secondQuery .= $equal;
        $secondQuery .= $commonTbl.".".$attendences->dimension[$commonTbl]->pkey;
        

        // add the group by statement by looping until highest hierarchy
        $secondQuery .= $group;
        $dimension = $attendences->dimension[$commonTbl];
        $cd = explode('.',$commonDimension)[1];
        $arrKey = array_keys($dimension->column);
        $index = array_search($cd,$arrKey);
        
        while($index != -1 ){
            $secondQuery .= $dimension->column[$arrKey[$index]]." ";
            $index--;
            if($index != -1){
                $secondQuery.=',';
            }
        }

        // Display the result dynamically same as generatePivot()
        // Start PDO connection
        $pivotStmt = $this->connect()->prepare($secondQuery);
        $pivotStmt->execute();

        $ttlCol = $pivotStmt->columnCount();
        $column = array();

        for ($counter = 0; $counter < $ttlCol; $counter ++) {
            $meta = $pivotStmt->getColumnMeta($counter);
            $column[] = $meta['name'];
        }
        
        $result = array();

        if($pivotStmt->rowCount()){
            while($row = $pivotStmt->fetch()){
                foreach($column as $col){
                    $result[$col][] = $row[$col];
                }
            }

            $rowCount = $pivotStmt->rowCount();
            print('<div class="container-fluid">');
            print('<div class="table-responsive table-responsive-sm">');
            print "<table class='table table-striped table-hover'>";
            print '<thead class=""><tr>';
                print '<tr>';
                for($j=0;$j<2;$j++){
                    print '<th>'.'</th>';
                }
                $colspan = $ttlCol - 1;
                print '</tr>';
                    print("<th scope='row'> Count </th>");
                    foreach($column as $col){
                        print '<th>'.$col.'</th>';
                    }
                print '</tr>';
                print '</thead>';
                for($i=0; $i < $rowCount; $i++){
                    print('<tr>');
                    print("<td scope='row'>".$i."</td>");
                    foreach($column as $col){
                        print('<td>'.$result[$col][$i].'</td>');
                    }
                    print('</tr>');
                }
            print '</table>';
            print '</div>';
            print '</div>';

        }        
    }

    
    public function printResult(){
        
        if($this->result->rowCount()){
            while($row = $this->result->fetch()){
                foreach($_SESSION['dimension'] as $dimension){
                    $key = explode('.',$dimension)[1];
                    $this->cube->dimension[$key][] = $row[$key];
                }
                foreach($_SESSION['measure'] as $measure){
                    $this->cube->measure[$measure][] = $row[$measure];
                }

            }
        }
        $rowCount = $this->result->rowCount();
        print('<div class="container-fluid">');
        print('<div class="table-responsive table-responsive-sm">');
        print "<table class='table table-striped table-dark table-hover'>";
            print '<thead class="thead-dark"><tr>';
                print("<th scope='row'> Count </th>");
                foreach($_SESSION['measure'] as $measure){
                    print '<th>'.$measure.'</th>';
                }
                foreach($_SESSION['dimension'] as $dimension){
                    print '<th>'.$dimension.'</th>';
                }
            print '</tr></thead>';
            for($i=0; $i < $rowCount; $i++){
                print('<tr>');
                print("<td scope='row'>".$i."</td>");
                foreach($_SESSION['measure'] as $mea){
                    print('<td>'.$this->cube->measure[$mea][$i].'</td>');
                }
                foreach($_SESSION['dimension'] as $dim){
                    $key = explode('.',$dim)[1];
                    print('<td>'.$this->cube->dimension[$key][$i].'</td>');
                }
                print('</tr>');
            }
        print '</table>';
        print '</div>';
        print '</div>';
    }
}
// Create a report instance
$report = new Report;
// Generate result by using query
$report->getResult($_SESSION['query']);
$attendences = unserialize($_SESSION['attendences']);

// If slice & dice button clicked, assign slice instant to session with slice key
if (isset($_POST['slice'])) {
    
    $slice = new Slice;
    $slice->dimension = $_POST['slice'];
    $slice->value = $_POST['value'];

    $key = array_search($slice,$_SESSION['slice']);
    if($key === false){
        $_SESSION['slice'][] = $slice;
    }else{
    }
}

// if clearSlice btn clicked, clear the slice session
if (isset($_POST['clearSlice'])) {
    unset($_SESSION['slice']);
    $_SESSION['slice'][] = "";
}

// If deleteSlice btn clicked, remove coresponding value in slice session
if (isset($_POST['deleteSlice'])) {
    $key = $_POST['remove'];
    if (false !== $key) {
        unset($_SESSION['slice'][$key]);
    }
}

// if pivotMeasure btn is clicked, add pivot measure to session
if (isset($_POST['pivotMea'])) {
    $_SESSION['pivotMea'] = $_POST['pivotMea'];
}

// if pivotCol btn is clicked, add pivot column to session
if (isset($_POST['pivotCol'])) {
    $_SESSION['pivotCol'] = $_POST['pivotCol'];
}

// if pivotRow is clicked, add pivot row to session
if (isset($_POST['pivotRow'])) {
    $_SESSION['pivotRow'] = $_POST['pivotRow'];
}

// if pivotValue is clicked, add pivot value to session
if (isset($_POST['pivotValue'])) {
    $_SESSION['pivotValue'][$_POST['pivotValueKey']][] = $_POST['pivotValue'];
}

// delete measure for pivot from session
if (isset($_POST['deleteMea'])) {
    unset($_SESSION['pivotMea']);
}

// delete column for pivot from session
if (isset($_POST['deleteCol'])) {
    unset($_SESSION['pivotCol']);
}

// delete row for pivot from session
if (isset($_POST['deleteRow'])) {
    unset($_SESSION['pivotRow']);
}

// clear all for pivot
if (isset($_POST['clearPivot'])) {
    unset($_SESSION['pivotRow']);
    unset($_SESSION['pivotCol']);
    unset($_SESSION['pivotMea']);
    unset($_SESSION['pivotValue']);
}

// delete coresponding pivotValue
if (isset($_POST['deletePivotValue'])) {
    if(isset($_POST['key'])){
        $key = $_POST['key'];
        $ky = $_POST['ky'];
        $val = $_POST['value'];
        unset($_SESSION['pivotValue'][$key][$ky]);
    }

}

// create slice report
if (isset($_POST['sliceBtn'])) {
    if(isset($_SESSION['query'])){
        $sliced = array_filter($_SESSION['slice']);
        if(!empty($sliced)){
            $query = $attendences->generateSQL(true);
            $_SESSION['query'] = $query;
            $report->getResult($_SESSION['query']);
        }
        else{
            $query = $attendences->generateSQL(false);
            $_SESSION['query'] = $query;
            $report->getResult($_SESSION['query']);
        }
    }
    else{
        print("<p>Please create report at main page first!</p>");
    }
}

// add fact 2 measure
if(isset($_POST['hasTeamMeasure'])){
    $_SESSION['hasTeamMeasure'] = $_POST['hasTeamMeasure'];
}

// remove fact 2 measure
if(isset($_POST['deleteHasTeamMeasure'])){
    $key = $_POST['value'];
    unset($_SESSION['hasTeamMeasure']);
}

// unpack the session object, fact object
if(isset($_SESSION['hasteam'])){
    $hasteam = unserialize($_SESSION['hasteam']);
}

// add common dimension for drill across.
if(isset($_POST['commonDimension'])){
    $_SESSION['commonDimension'] = $_POST['commonDimension'];
    
}

// remove the common dimension
if(isset($_POST['deleteCommonDimension'])){
    unset($_SESSION['commonDimension']);
}

// clear the drill across
if(isset($_POST['clearCommonDimension'])){
    unset($_SESSION['hasTeamMeasure']);
    unset($_SESSION['commonDimension']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mining</title>
</head>
<body>
<div class="table-responsive table-responsive-sm">
    <table  class = 'table table-striped'>
        <tr>
            <td>Constrait</td>
            <td>Slice and Dice</td>
            <td>Pivoting</td>
            <td>Drill Across</td>
        </tr>
        <tr>
            <td>
            <?php
                $self = $_SERVER['PHP_SELF'];
                print('<h3>Drill Across</h3>');
                if(isset($_SESSION['hasTeamMeasure'])){
                    print('<p>Another Measure</p>');
                        $value = $key = $_SESSION['hasTeamMeasure'];
                        if ($value != "") {
                            print("<form action='$self' method='POST'>");
                            print "{$key} => ";
                            print("<input type='text' hidden value='$key' name='value'>");
                            $btn = "<button type='submit' id='del-btn' name='deleteHasTeamMeasure' class='btn-danger'>Delete</button>";
                            print($btn);
                            print('</form>');
                    }  
                }
                if(isset($_SESSION['commonDimension'])){
                    print('<p>Common Dimension</p>');
                    $key = $value = $_SESSION['commonDimension'];
                        if ($value != "") {
                            print("<form action='$self' method='POST'>");
                            print "{$value}";
                            print("<input type='text' hidden value='$key' name='value'>");
                            $btn = "<button type='submit' id='del-btn' name='deleteCommonDimension' class='btn-danger'>Delete</button>";
                            print($btn);
                            print('</form>');
                    }  
                }

                $self = $_SERVER['PHP_SELF'];
                print('<h3>Pivot</h3>');
                if(isset($_SESSION['pivotMea'])){
                    $key = $_SESSION['pivotMea'];
                    print("<form action='$self' method='POST'>");
                    print("Measure: ".$_SESSION['pivotMea']);
                    print("<input type='text' hidden value='$key' name='remove'>");
                    $btn = "<button type='submit' id='del-btn' name='deleteMea' class='btn-danger'>Delete</button>";
                    print($btn);
                    print('</form>');
                }
                if(isset($_SESSION['pivotRow'])){
                    $key = $_SESSION['pivotRow'];
                    print("<form action='$self' method='POST'>");
                    print("Row: ".$_SESSION['pivotRow']);
                    print("<input type='text' hidden value='$key' name='remove'>");
                    $btn = "<button type='submit' id='del-btn' name='deleteRow' class='btn-danger'>Delete</button>";
                    print($btn);
                    print('</form>');
                }
                if(isset($_SESSION['pivotCol'])){
                    $key = $_SESSION['pivotCol'];
                    print("<form action='$self' method='POST'>");
                    print("Column: ".$_SESSION['pivotCol']);
                    print("<input type='text' hidden value='$key' name='remove'>");
                    $btn = "<button type='submit' id='del-btn' name='deleteCol' class='btn-danger'>Delete</button>";
                    print($btn);
                    print('</form>');
                }
                if(isset($_SESSION['pivotValue'])){
                    foreach ($_SESSION['pivotValue'] as $key => $value) {
                        if ($value != "") { 
                            foreach($value as $ky => $val){
                                print("<form action='$self' method='POST'>");
                                print("<label>{$key} => {$val}</label>");
                                print("<input type='text' hidden value='$key' name='key'>");
                                print("<input type='text' hidden value='$ky' name='ky'>");
                                print("<input type='text' hidden value='$val' name='value'>");
                                $btn = "<button type='submit' id='del-btn' name='deletePivotValue' class='btn-danger'>Delete</button>";
                                print($btn);
                                print('</form>');
                            }

                        }
                    }  
                }
                print('<hr>');
            ?>
            <?php
                $self = $_SERVER['PHP_SELF'];
                print('<h3>Slice and Dice</h3>');
                if(!empty($_SESSION['slice'])){
                    $_SESSION['slice'] = array_filter($_SESSION['slice']);
                    foreach ($_SESSION['slice'] as $key => $slice) {
                        $value = $slice->dimension."=>".$slice->value;
                        if ($value != "") {
                            print("<form action='$self' method='POST'>");
                            print "{$key} => {$value}";
                            print("<input type='text' hidden value='$key' name='remove'>");
                            $btn = "<button type='submit' id='del-btn' name='deleteSlice' class='btn-danger'>Delete</button>";
                            print($btn);
                            print('</form>');
                        }
                }
            }?>
            <?php
                print('<hr>');
                $self = $_SERVER['PHP_SELF'];
                print('<h3>Measure</h3>');
                foreach ($_SESSION['measure'] as $key => $value) {
                    if ($value != "") {
                        print("<form action='$self' method='POST'>");
                        print "{$key} => {$value}";
                        print("<input type='text' hidden value='$key' name='remove'>");
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
                        print("<input type='text' hidden value='$value' name='value'>");
                        $btn = "<button type='submit' id='del-btn' name='deleteDimension' class='btn-danger'>Delete</button>";
                        print($btn);
                        print('</form>');
                    }
                }  
                print('<hr>');

            ?>            
            </td>
            <td>
               <?php 
            //    print the menu for Slice and Dice operation
                    $attendences->printAllDimensionSlice();
               ?>
                <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
                    <button type="submit" class = 'btn btn-warning' name='clearSlice' value='clear'>Clear Slice & Dice</button>
                </form>
                <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
                    <button type="submit" class = "btn btn-success" name='sliceBtn' value='sliceBtn'>Create Slice & Dice</button>
                </form>
            </td>
            <td>
                <?php
                // Print the menu for pivot operation
                    $report->printPivotMenu();
                ?>
                <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
                    <button type="submit" class = 'btn btn-warning' name='clearPivot' value='clearPivot'>Clear Pivot</button>
                </form>
                <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
                    <button type="submit" class = "btn btn-success" name='pivotBtn' value='pivotBtn'>Create Pivot</button>
                </form>                
            </td>


            <td>
            <?php
                    $hasteam->printHasTeamMeasure(); 
                    $attendences->printCommonDimension($hasteam);                
            ?>
                 <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
                    <button type="submit" class = 'btn btn-warning' name='clearCommonDimension' value='clearCommonDimension'>Clear All</button>
                </form>
                <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
                    <button type="submit" class = "btn btn-success" name='drillAcross' value='drillAcross'>Drill Across</button>
                </form>                   
            </td>
        </tr>
</div>
    </table>
    <?php 
        if(isset($_POST['pivotBtn'])){
        //     $start_time = microtime(true); 
            $report->generatePivot();
            // $end_time = microtime(true); 
            // $execution_time = ($end_time - $start_time); 
            // echo " It takes ".$execution_time." seconds to execute the script"; 
        }
        else if(isset($_POST['drillAcross'])){
            $report->drillAcross();
        }
        else{
            $report->printResult();
        }
     ?>
</body>
</html>