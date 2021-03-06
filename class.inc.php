<?php


/*+----------------------------------------------------------------------
 ||
 ||  Class Dimension
 ||
 ||         Author:  Zhong Ming Tan
 ||
 ||        Purpose:  make a easy access to support the fact table
 ||
 ||  Inherits From:  No
 ||
 ||     Interfaces:  No
 ||
 |+-----------------------------------------------------------------------
 ||
 ||      Constants:  No
 ||
 |+-----------------------------------------------------------------------
 ||
 ||   Constructors: parameter is tableName and primaryKey for dimension's table
 ||
 ||  Class Methods:
 ||  Inst. Methods:  
 ||                 -addColumn(arrayKeyStr, colNameStr) : None
 ||                 -getTblCol(arrayKeyStr) : tblName.column
 ||                 -printDimension(none):none
 ||
 ++-----------------------------------------------------------------------*/

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

Class Pivot{
    public $measure;
    public $row;
    public $col;
}

class Measure
{
    public $name;
    public $column;
    public $operation;
    public $formatString;
}
/*+----------------------------------------------------------------------
 ||
 ||  Class Dimension
 ||
 ||         Author:  Zhong Ming Tan
 ||
 ||        Purpose:  easy use fact class to replace xml
 ||
 ||  Inherits From:  Class Dimension, Class Measure, Class Pivot
 ||
 ||     Interfaces:  No
 ||
 |+-----------------------------------------------------------------------
 ||
 ||      Constants:  No
 ||
 |+-----------------------------------------------------------------------
 ||
 ||   Constructors: None
 ||
 ||  Class Methods:
 ||  Inst. Methods:  
 ||                 -printAllMeasure() : None
 ||                 -printAllDimensionSlice() : None
 ||                 -printAllDimension():none
 ||                 -addReference(str,str):None
 ||                 -get_dimension(str):str
 ||                 -printMeasure(str):None or False
 ||                 -getMeasure(str):str
 ||                 -generateSQL(bool):None
 ||
 ++-----------------------------------------------------------------------*/
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
            print('&nbsp;&nbsp;<input class = "btn btn-primary btn-sm" type="submit" value="Add"> ');
            print('</form>');
        }
    }

    public function printCommonDimension($table){
        print('<h4>Common Table</h4>');
        foreach($table->dimension as $dim){
            foreach($_SESSION['dimension'] as $sDim){
                $var = explode('.',$sDim)[0];
                if($var == $dim->tblName){
                    $commonTbl[] = $var;
                }
            }
            
        }

        $uniqueTbl =  array_unique($commonTbl);
        
        foreach($uniqueTbl as $tbl){
            $dimArr[] = $this->get_dimension($tbl);
        }
        
        foreach($dimArr as $dim){
            $self = $_SERVER['PHP_SELF'];
            print("<form action='$self' method='post'>");
                print('<label>'.ucfirst($dim->tblName).'</label>&nbsp;:&nbsp;');
                print('<div class="form-row">');
                    print('<div class="form-group col-md-8">');
                        print("<select class='custom-select' name='commonDimension' id='commonDimension'>");
                        $dim->printDimension();
                        print('</select>');
                    print('</div>');
                    print('<div class="form-group col-md-4">');
                        print('&nbsp;&nbsp;<input class = "btn btn-primary" type="submit" value="Add"> ');
                    print('</div>');
                print('</div>');
            print('</form>');    
        }

    }

    public function printHasTeamMeasure(){
        print("<h3>HasTeam</h3>");
        foreach($this->measure as $key => $mea){ 
            $self = $_SERVER['PHP_SELF'];
            print("<form action='$self' method='post'>");
            print('<label>'.ucfirst($mea->name).'</label>&nbsp;:&nbsp;');
            print('<div class="form-row">');
                print('<div class="form-group col-md-8">');
                    print("<select class='custom-select' name='hasTeamMeasure' id='hasTeamMeasure'>");
                    $this->printMeasure($key);
                    print('</select>');
                print('</div>');
                print('<div class="form-group col-md-4">');
                    print('&nbsp;&nbsp;<input class = "btn btn-primary" type="submit" value="Add"> ');
                print('</div>');
            print('</div>');
        print('</form>');
        }
    }

    public function printAllDimensionSlice(){
        foreach($this->dimension as $key => $dim){ 
            $self = $_SERVER['PHP_SELF'];
            print("<form action='$self' method='post'>");
                print('<label>'.ucfirst($dim->tblName).'</label>&nbsp;:&nbsp;');
                print('<div class="form-row">');
                    print('<div class="form-group col-md-4">');
                        print("<select class='custom-select' name='slice' id='slice'>");
                        $dim->printDimension();
                        print('</select>');
                    print('</div>');
                    print('<div class="form-group col-md-2">');
                        print('<input type="text" name="value" id="value" class="form-control" placeholder="Value">');
                    print('</div>');
                    print('<div class="form-group col-md-2">');
                        print('&nbsp;&nbsp;<input class = "btn btn-primary" type="submit" value="Add"> ');
                    print('</div>');
                print('</div>');
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
            print('&nbsp;&nbsp;<input class = "btn btn-primary btn-sm" type="submit" value="Add"> ');
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
    public function generateSQL($isSlice)
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
            if(!empty($_SESSION['measure'])){
                $query = $query . ",";
            }
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
        $tables = array_filter($tables);
        $uniques = array_unique($tables);
      

        // Adding join query
        foreach($uniques as $unique){
            $query = $query .
            $join. $unique. " ON " . 
            $this->tblName . ".". $this->reference[$unique] . "=".
            $unique."." .$this->dimension[$unique]->pkey
            ;
        }

        if($isSlice){
            // Adding WHERE
            if($_POST['sliceBtn']){
                $sliceArr = array_filter($_SESSION['slice']);
                $counter = 1;
                $len     = count($sliceArr);
                if(!empty($sliceArr)){
                    $keyArr;
                    foreach($sliceArr as $key => $slice){
                        $keyArr[] = $slice->dimension;
                    }

                   $uniqueKey = array_unique($keyArr);
                }
                $val;
                foreach($uniqueKey as $ky){
                    foreach($sliceArr as $key => $slice){
                        if($ky == $slice->dimension){
                            $val[$ky][] = $slice->value;
                        }
                    }
                }
                $test = " WHERE";
                $countVal = 1;
                $lenVal = count($val);
                foreach($val as $key => $value){
                    $test = $test . " ". $key. " in (";
                    $counter = 1;
                    $len = count($value);
                    foreach($value as $x){
                        $test = $test."'".$x."'";
                        if($counter != $len){
                            $test = $test.",";
                        }
                        $counter++;
                    }
                    $test = $test.")";
                    if($countVal != $lenVal){
                        $test = $test." AND";
                    }
                    $countVal++;
                }
                $query=$query.$test;
           
            }            
        }
        // Adding Group By Command
        if(!empty($_SESSION['dimension'])){
            $query = $query . ' GROUP BY ';
        }
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

        return $query;
    }
}
