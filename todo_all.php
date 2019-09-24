<?php
  header('Content-Type: text/html; charset=ISO-8859-1');

  
  $period_time = 1;
  $period_type = "month";
  
    $db_link = mysqli_connect (
                     'localhost', 
                     'root', 
                     '', 
                     'TOOLS'
                    );



?>

<html style="background-color:#222;">
  <head>
    <title>Tools</title>
    <link rel="stylesheet" href="../css/style.css" type="text/css"/>
  </head>
  <body>
    <center>
    <h2>Todo Overview</h2>

      
      <?php 
      
      $sql = 'select t.id, replace(replace(replace(t.`text`, "\'", "\""), ">", "&gt;"), "<", "&lt;") as text, t.deleted, t.created, t.updated, t.createdby, t.updatedby, t.deletedby
from todo t 
order by id desc
';
      
      $db_erg = mysqli_query( $db_link, $sql );
    $isRowODD = false;

    if ( ! $db_erg ){die('Ung&uuml;ltige Abfrage: ' . mysqli_error());}
    
    echo '<center><table cellspacing="0" style="border-style:none; width:90%; min-width:900px;"><form action="todo.php" method="post" id="delete_form">';
    echo '<tr class="tr_bht">
    <td><b>ID</b></td>
    <td><b>TODO</b></td>
    </tr>';
    
    $del_count = 0;
    
    $dels ="";
    $finished = "";
    
    
    while ($zeile = mysqli_fetch_array( $db_erg, MYSQL_ASSOC)){
        
      $a = '';
      $b = 'tr_b';
      $c = 'tr_w';
      $d = 'tr_d_';
      
      $e = '';
      $f = 'i_delete';
      $g = 'i_undelete';
      
      $a = "tr_d_n";
      $e = $g;



   
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
        
        $finished = $finished ."<tr class=\"$a\">
                 <td>
                 
                ".$zeile['id']."
                 
                 </td>
                 <td><div class=\"todo_text\" title=\"Erstellt am: ".date_format(date_create($zeile['created']), 'd.m.Y H:i:s')."\"><input type='text' name=\"edit_". $zeile['id'] ."\" value='". $zeile['text'] ."'>
                </div></td>
                 </tr>";
      }

    
    $finished = $finished;
    
    echo $finished;
    
    
    
    echo "</form></table>";
      ?>    
        
    </center>
  </body>
</html>