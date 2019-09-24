<?php


header('Content-Type: text/html; charset=ISO-8859-1');
error_reporting(E_ALL);



  function arrayToString($a, $s = null, $e = null){
        $o = "";
        if(count(func_get_args()) >= 2){
            for ($i = $s; $i < $e; $i++){
                $o .= $a[$i];
            }
        }
        else{
            for ($i = 0; $i < sizeof($a); $i++){
                $o .= $a[$i];
            }
        }
        return $o;
    }
    
     function grk_Week_Range($DateString, $FirstDay=6){
        if(empty($DateString) === TRUE){
            $DateString = date('Y-m-d');
        }
        $Days = array(
            0 => 'monday',
            1 => 'tuesday',
            2 => 'wednesday',
            3 => 'thursday',
            4 => 'friday',
            5 => 'saturday',
            6 => 'sunday'
        );  
        $DT_Min = new DateTime('last '.(isset($Days[$FirstDay]) === TRUE ? $Days[$FirstDay] : $Days[6]).' '.$DateString);
        $DT_Max = clone($DT_Min);
        return array(
            $DT_Min->modify('+1 day')->format('Y-m-d'),
            $DT_Max->modify('+7 days')->format('Y-m-d')
        );
    }
    
    function grk_Month_Range($DateString, $FirstDay=1){
        if(empty($DateString) === TRUE){
            $DateString = date('Y-m-d');
        } 
        $DT_Min = new DateTime('now');
        $DT_Max = clone($DT_Min);
        return array(
            $DT_Min->format('Y-m-01'),
            $DT_Max->format('Y-m-t')
        );
    }



  $period_time = 1;
  $period_type = "month";
  
  $db_link = mysqli_connect (
                     'localhost', 
                     'test', 
                     'test', 
                     'tools'
                    );

  



          
if(isset($_POST["todo"])){
  $todo = utf8_encode($_POST["todo"]);
  if(!empty($todo)){
    $sql = "INSERT INTO `todo` (`text`, `deleted`, `created`, `createdby`) VALUES('".mysqli_real_escape_string($db_link, $todo)."', null, now(), '".mysqli_real_escape_string($db_link, $_SERVER['REMOTE_ADDR'])."');";
    $db_erg = mysqli_query( $db_link, $sql );
     echo mysqli_error($db_link);
  }
}


if(isset($_POST["edited"])){
  $edited = $_POST["edited"];
  $edit = $_POST["edit_$edited"];
  if(!empty($edited) && !empty($edit)){
    $sql = "UPDATE `todo` SET `text` = '".utf8_encode(mysqli_real_escape_string($db_link, $edit))."', updated = now() ,updatedby = '".mysqli_real_escape_string($db_link, $_SERVER['REMOTE_ADDR'])."' WHERE `todo`.`id` = ".mysqli_real_escape_string($db_link, $edited).";";
    // Debugging: echo "<br>test22 " .$sql;
    $db_erg = mysqli_query( $db_link, $sql );
     echo mysqli_error($db_link);
  }
}


if(isset($_POST["delete"])){
  $delete = $_POST["delete"];
  if(!empty($delete)){
    $sql = "select * from todo WHERE id = ".mysqli_real_escape_string($db_link, $delete).";";
    $db_erg = mysqli_query( $db_link, $sql );
    while ($zeile = mysqli_fetch_array( $db_erg, MYSQLI_ASSOC)){
      $del = $zeile["deleted"];    
    }
    
    if($del == 'null' or $del == '0' or $del == 0){
      $sql = "UPDATE `todo` SET `deleted` = now(),deletedby = '".mysqli_real_escape_string($db_link, $_SERVER['REMOTE_ADDR'])."' WHERE `todo`.`id` = ".mysqli_real_escape_string($db_link, $delete).";";
      $db_erg = mysqli_query( $db_link, $sql );
       echo mysqli_error($db_link);
    }
    else{
      $sql = "UPDATE `todo` SET `deleted` = null, deletedby = '".mysqli_real_escape_string($db_link, $_SERVER['REMOTE_ADDR'])."' WHERE `todo`.`id` = ".mysqli_real_escape_string($db_link, $delete).";";
    $db_erg = mysqli_query( $db_link, $sql );
     echo mysqli_error($db_link);
    }
  }
}


$html = array();
$table = array();
$stats = array();
$row_count = 0;
$open_this_day=0;
$closed_this_day=0;
$open_this_week = 0;
$closed_this_week = 0;
$open_this_month = 0;
$closed_this_month = 0;
$all_todos = 0;

$html[] = '<html>
  <head>
    <title>Tools</title>
    <link rel="stylesheet" href="../css/style.css" type="text/css"/>
  </head>
  <body>
    <center>
    <h2><a href="todo_all.php" target="_blank">Todo</a></h2>
     <small><small style="color:red;">Diese Seite speichert bei Bearbeitung deine IP Adresse</small><br><br></small>
      <form action="todo.php" method="post" id="todo_form">
        <input type="text" name="todo" class="i_save" value="">
        <input type="submit" name="sb" class="btn s_save" value="Save">
      </form>';
      
      $db_erg = mysqli_query($db_link, "select max(id) as id from  todo");
      while ($zeile = mysqli_fetch_array( $db_erg, MYSQLI_ASSOC)){
          $all_todos = $zeile['id'];
      }
      

      
      $sql = '
select t.id, replace(replace(replace(t.`text`, "\'", "\""), ">", "&gt;"), "<", "&lt;") as text, t.deleted, t.created, t.updated, t.createdby, t.updatedby, t.deletedby
from todo t 
where t.deleted is null 
      or t.deleted > subdate(now(), interval '.$period_time. " " .$period_type.')
order by id desc
';
      
    $db_erg = mysqli_query( $db_link, $sql );
    $isRowODD = false;

    if ( ! $db_erg ){die('Ung&uuml;ltige Abfrage : ' . mysqli_error($db_link));}
    
    $table[] = '<center><table cellspacing="0" style="border-style:none; width:90%; min-width:900px;"><form action="todo.php" method="post" id="delete_form">';
    $table[] = '<tr class="tr_bht">
    <td><b></b></td>
    <td><b>TODO</b></td>
    </tr>';
    
    $del_count = 0;
    $dels ="";
    $finished = "";
    
    $week = grk_Week_Range(date("Y-m-d h:i:sa"),-1);
    $month = grk_Month_Range(date("Y-m-d h:i:sa"), -1);
    
    $t = 0;
    
    while ($zeile = mysqli_fetch_array( $db_erg, MYSQLI_ASSOC)){
        
      $a = '';
      $b = 'tr_b';
      $c = 'tr_w';
      $d = 'tr_d_';
      
      $e = '';
      $f = 'i_delete';
      $g = 'i_undelete';
      
      $datetime1 = new DateTime($zeile['created']);
      $datetime3 = new DateTime('today');
      
      $datetime1->setTime(0,0,0);
      $datetime3->setTime(0,0,0);
      
      $i1 = date_diff($datetime1,$datetime3);
      
      if($zeile['deleted'] != null){
      $t++;
        $datetime2 = new DateTime($zeile['deleted']);
        $datetime2->setTime(0,0,0);
        $i2 = date_diff($datetime2, $datetime3);
        
        if($i2->format('%a') == 0){$closed_this_day++;}
        if($datetime2->format('Y-m-d') <= $week[1] && $datetime2->format('Y-m-d') >= $week[0]){$closed_this_week++;}
        if($datetime2->format('Y-m-d') <= $month[1] && $datetime2->format('Y-m-d') >= $month[0]){$closed_this_month++;}
        
        
      }
      
      
      
      if($i1->format('%a') == 0){$open_this_day++;}
      
      
      if($zeile['created'] < $week[1] && $zeile['created'] > $week[0]){$open_this_week++;}
      
      
      if($zeile['created'] < $month[1] && $zeile['created'] > $month[0]){$open_this_month++;}
      
      
      //DEV OUTPUT
      //echo $zeile['id'] . ' => ' . $datetime1->format('d.m.Y') . ' ' .  $datetime2->format('d.m.Y') . ' ' . $datetime3->format('d.m.Y') . ' ' . $open_this_day . ' ' . $closed_this_day . '<br>';
      
      
      if($zeile['deleted'] != ''){
        if($del_count >= 18){
          $a = "tr_d_n";
        }
        else{
          $a = $d . $del_count;
          $e = $g;
          $del_count++; 
        }
         $dels = $dels ."<tr class=\"$a\">
                 <td>
                 
                 <input class=\"$e\" type=\"submit\" value=\"". $zeile['id'] ."\" name=\"delete\" title=\"Eintrag ". $zeile['id'] ." wiederherstellen\">
                 
                 </td>
                 <td><div class=\"todo_text\" title=\"Erstellt am: ".date_format(date_create($zeile['created']), 'd.m.Y H:i:s')."&nbsp;|&nbsp;Gel&ouml;scht am: ".date_format(date_create($zeile['deleted']), 'd.m.Y H:i:s')."\">". utf8_decode($zeile['text']) ."</div></td>
                 </tr>";
      }
      else{
        $row_count++;  
        if($isRowODD){
          $a = $b;
          $isRowODD = false;
        }
        else{
          $a = $c;
          $isRowODD = true;
        }
      
      
        $e = $f;
        
        //&#9998; edit
        
        $table[] = $finished ."<tr class=\"$a\">
                 <td>
                 
                 <input class=\"$e\" type=\"submit\" value=\"". $zeile['id'] ."\" name=\"delete\" title=\"Eintrag ". $zeile['id'] ." l&ouml;schen\">
                 
                 </td>
                 <td><div class=\"todo_text\" title=\"Erstellt am: ".date_format(date_create($zeile['created']), 'd.m.Y H:i:s')."\"><input autocomplete=\"off\" type='text' name=\"edit_". $zeile['id'] ."\" value='". utf8_decode($zeile['text']) ."'>
                 <input class=\"i_edit\" type=\"submit\" name=\"edited\" value=\"". $zeile['id'] ."\"  title=\"Eintrag ". $zeile['id'] ." bearbeiten\"></div></td>
                 </tr>";
      }
    }
        
    $table[] = $dels;
    
    
    
    $table[] = "</form><tr class='tr_bht'><td></td><td>&copy by Relluem94 | MUR 2016-2017</td></tr></table>";
    
    
    
    $stats[] = '<tr class="tr_bht"><td></td><td><b>Stats</b></td></tr>'
             . '<tr class="'.$c.'"><td></td><td>'
            . 'Open: <b style="color:#ff6633;">'. $row_count . '</b>&nbsp;|&nbsp;'
            . 'New Day: <b title="'.date('d.m.Y').'" style="color:#1133ff;">'.$open_this_day.'</b>&nbsp;|&nbsp;'
            . 'Done Day: <b title="'.date('d.m.Y').'" style="color:#339933">'.$closed_this_day.'</b>&nbsp;|&nbsp;'
            . 'New Week: <b title="'.date_format(date_create($week[0]), 'd.m.Y').' - '.date_format(date_create($week[1]), 'd.m.Y').'" style="color:#3366ff;">'.$open_this_week.'</b>&nbsp;|&nbsp;'
            . 'Done Week: <b title="'.date_format(date_create($week[0]), 'd.m.Y').' - '.date_format(date_create($week[1]), 'd.m.Y').'" style="color:#669933">'.$closed_this_week.'</b>&nbsp;|&nbsp;'
            . 'New Month: <b title="'.date_format(date_create($month[0]), 'd.m.Y').' - '.date_format(date_create($month[1]), 'd.m.Y').'" style="color:#7777cc;">'.$open_this_month.'</b>&nbsp;|&nbsp;'
            . 'Done Month: <b title="'.date_format(date_create($month[0]), 'd.m.Y').' - '.date_format(date_create($month[1]), 'd.m.Y').'" style="color:#99cc99">'.$closed_this_month.'</b>&nbsp;|&nbsp;'
            . 'Total: <b style="color:#666">'.$all_todos.'</b>'
            . '</td></tr>';
   
    
    echo arrayToString($html);
    echo arrayToString($table, 0,1);
    echo arrayToString($stats);
    echo arrayToString($table, 1,sizeof($table));  
  

?>    
        
