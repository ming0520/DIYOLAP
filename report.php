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

Class Report extends Dbh{
    
    public $query;
    public $result;
    public $column;
    public $cube;

    public function __construct(){
        $_SESSION['dimension'] = array_filter($_SESSION['dimension']);
        $_SESSION['measure'] = array_filter($_SESSION['measure']);
        $this->cube = new CubeList;
        // foreach($_SESSION['dimension'] as $dimension){
        //     $this->cube->dimension[$dimension][] = '';
        //     $this->cube->dimension = array_filter($this->cube->dimension[$dimension]);
            
        // }
        // foreach($_SESSION['measure'] as $measure){
        //     $this->cube->measure[$measure][] = '';
        //     $this->cube->measure = array_filter($this->cube->measure[$measure]);
        // }
        // $this->cube->dimension = array_filter($this->cube->dimension);
        // $this->cube->measure = array_filter($this->cube->measure);
    }

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

    public function printOptValue($array){
        if(empty($array)){
            print "<h1>Array is empty!</h1>";
            return false;
        }
        foreach($array as $key => $value){
            print("<option value='$value'>$value</option>");
        }

    }

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

    public function printPivotMenu(){
        $measures = array_filter($_SESSION['measure']);
        $dimensions = array_filter($_SESSION['dimension']);
        $slices = array_filter($_SESSION['slice']);
        $this->printPivotForm($measures,'pivotMea','measures');
        $this->printPivotForm($dimensions,'pivotRow','Row');
        $this->printPivotForm($dimensions,'pivotCol','Column');

        $self = $_SERVER['PHP_SELF'];

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
        // print_pre(var_dump($measures));
        // print_pre(var_dump($dimensions));
        // print_pre(var_dump($slices));
    }

    public function generatePivot(){
        $pivotMea = $_SESSION['pivotMea'];
        $pivotRow = $_SESSION['pivotRow'];
        $pivotCol = $_SESSION['pivotCol'];

        $meaOp = explode('(',$pivotMea)[0];
        $meaName = explode('(',$pivotMea)[1];
        $meaName = explode(')',$meaName)[0];

        $rowTbl = explode('.',$pivotRow)[0];
        $colTbl = explode('.',$pivotCol)[0];

        $rowCol = explode('.',$pivotRow)[1];
        $colCol = explode('.',$pivotCol)[1];

        $query = 'SELECT DISTINCT '. $colCol. ' FROM '. $colTbl;
        $pivotValues = array_filter($_SESSION['pivotValue']);
        // print_pre($pivotValues);
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


        $query .= ' ORDER BY '.$colCol;
        // print_r($query);
        $colStmt = $this->connect()->prepare($query);
        $colStmt->execute();

        if($colStmt->rowCount()){
            while($row = $colStmt->fetch()){
                $column[] = $row[$colCol];
            }
        }

        $query = "";

        $select = "SELECT DISTINCT ";
        $join = " RIGHT JOIN ";

        $query .= $select;
        $query .= $rowTbl .".". $rowCol.",";
        foreach($column as $col){
            // $str = " ".$meaOp."(CASE WHEN ". $colCol."='".$col."'". " THEN ". $meaName ." ELSE 0 END".")". " AS '".$colCol." ".$col."'";
            $str = " ".$meaOp."(CASE WHEN ". $colCol."='".$col."'". " THEN ". $meaName ." ELSE NULL END".")". " AS '".$col."'";
            $query .= $str;
            if($col == end($column)){

            }else{
                $query .=",";
            }
        } 

        $query .= " FROM ";
        $attendences = unserialize($_SESSION['attendences']);
        $query .= $attendences->tblName . " ";

        if($colTbl == $rowTbl){
            $dim = $attendences->dimension[$colTbl];
            $ref = $attendences->tblName.".".$attendences->reference[$colTbl];
            $colKey = $dim->tblName.".".$dim->pkey;
            // print_pre(var_dump("colKey: ".$colKey));
            $query .= $join. $dim->tblName ." ON ". $ref . " = ".$colKey;
        }
        else{
            $dim = $attendences->dimension[$colTbl];
            $ref = $attendences->tblName.".".$attendences->reference[$colTbl];
            $colKey = $dim->tblName.".".$dim->pkey;
            // print_pre(var_dump("colKey: ".$colKey));
            $query .= $join. $dim->tblName ." ON ". $ref . " = ".$colKey;

            $dim = $attendences->dimension[$rowTbl];
            $ref = $attendences->tblName.".".$attendences->reference[$rowTbl];
            $rowKey = $dim->tblName.".".$dim->pkey;
            // print_pre(var_dump("colKey: ".$colKey));
            $query .= $join. $dim->tblName ." ON ". $ref . " = ".$rowKey;
        }

        $query .= " GROUP BY ";
        $query .= $pivotRow;
        // print_r($query);

        
        // Start PDO connection
        $pivotStmt = $this->connect()->prepare($query);
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
            // print "<table class='table table-striped table-dark table-hover'>";
            //     print '<thead class="thead-dark"><tr>';
            print "<table class='table table-striped table-hover'>";
            print '<thead class=""><tr>';
                print '<tr>';
                for($j=0;$j<2;$j++){
                    print '<th>'.'</th>';
                }
                $colspan = $ttlCol - 1;
                print "<th colspan=$colspan>".$colCol."</th>";
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
        
        return $query;

        // print_pre(var_dump("pivotMea: ".$pivotMea));
        // print_pre(var_dump("pivotRow: ".$pivotRow));
        // print_pre(var_dump("pivotCol: ".$pivotCol));

        // print_pre(var_dump("rowTbl: ".$rowTbl));
        // print_pre(var_dump("colTbl: ".$colTbl));

        // print_pre(var_dump("rowCol: ".$rowCol));
        // print_pre(var_dump("colCol: ".$colCol));
        
        // print_pre(var_dump("meaName: ".$meaName));
        // print_pre(var_dump("meaOp: ".$meaOp));

        // print_pre(var_dump("query: ".$query));
        // print_pre(var_dump($column));

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
        // print('<pre>');
        // var_dump($this->cube);
        // print('</pre>');
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
// print_r($_SESSION['query']);
// var_dump($_SESSION['dimension']);
// var_dump($_SESSION['measure']);
// $query = 'SELECT DISTINCT COUNT(aid),dates.year FROM attendences RIGHT JOIN dates ON attendences.did=dates.dateid GROUP BY dates.year';
$report = new Report;
$report->getResult($_SESSION['query']);
$attendences = unserialize($_SESSION['attendences']);

if (isset($_POST['dimension'])) {
    $_SESSION['dimension'][] = $_POST['dimension'];
}

if (isset($_POST['measure'])) {
    $_SESSION['measure'][] = $_POST['measure'];
}

if (isset($_POST['create'])) {
    $_SESSION['create'][] = $_POST['create'];
    $query = $attendences->generateSQL();
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

if (isset($_POST['slice'])) {
    $slice = new Slice;
    $slice->dimension = $_POST['slice'];
    $slice->value = $_POST['value'];
    $_SESSION['slice'][] = $slice;
}

if (isset($_POST['clearSlice'])) {
    unset($_SESSION['slice']);
    $_SESSION['slice'][] = "";
}

if (isset($_POST['deleteSlice'])) {
    $key = $_POST['remove'];
    if (false !== $key) {
        unset($_SESSION['slice'][$key]);
    }
}

if (isset($_POST['pivotMea'])) {
    $_SESSION['pivotMea'] = $_POST['pivotMea'];
}

if (isset($_POST['pivotCol'])) {
    $_SESSION['pivotCol'] = $_POST['pivotCol'];
}

if (isset($_POST['pivotRow'])) {
    $_SESSION['pivotRow'] = $_POST['pivotRow'];
}

if (isset($_POST['pivotValue'])) {
    $_SESSION['pivotValue'][$_POST['pivotValueKey']][] = $_POST['pivotValue'];
}

if (isset($_POST['deleteMea'])) {
    unset($_SESSION['pivotMea']);
}

if (isset($_POST['deleteCol'])) {
    unset($_SESSION['pivotCol']);
}

if (isset($_POST['deleteRow'])) {
    unset($_SESSION['pivotRow']);
}

if (isset($_POST['clearPivot'])) {
    unset($_SESSION['pivotRow']);
    unset($_SESSION['pivotCol']);
    unset($_SESSION['pivotMea']);
    unset($_SESSION['pivotValue']);
}

if (isset($_POST['deletePivotValue'])) {
    if(isset($_POST['key'])){
        $key = $_POST['key'];
        $ky = $_POST['ky'];
        $val = $_POST['value'];
        unset($_SESSION['pivotValue'][$key][$ky]);
    }

}


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
// print('<pre>');
// var_dump($_SESSION['slice']);
// print("<hr>");
// print('</pre>');
?>
<!-- SELECT DISTINCT COUNT(aid),dates.year FROM attendences RIGHT JOIN dates ON attendences.did=dates.dateid GROUP BY dates.year -->

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
        </tr>
        <tr>
            <td>
            <?php
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
                            // print "{$key} => {$value}";
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
                // print_r($_SESSION['dimension']);
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
                        print("<input type='text' hidden value='$value' name='value'>");
                        $btn = "<button type='submit' id='del-btn' name='deleteDimension' class='btn-danger'>Delete</button>";
                        print($btn);
                        print('</form>');
                    }
                }  
            ?>            
            </td>
            <td>
               <?php 
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
                    $report->printPivotMenu();
                ?>
                <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
                    <button type="submit" class = 'btn btn-warning' name='clearPivot' value='clearPivot'>Clear Pivot</button>
                </form>
                <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
                    <button type="submit" class = "btn btn-success" name='pivotBtn' value='pivotBtn'>Create Pivot</button>
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
        else{
            $report->printResult();
        }
     ?>
</body>
</html>