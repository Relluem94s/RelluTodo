<?php 

$stats = json_decode(file_get_contents("assets/js/stats.json"));

foreach($stats as $key => $entry){
     echo '<span class="stats label label-' . $entry->class . '"><i class="fas ' . $entry->icon . '"></i> ' . $entry->text . '</span>';
}

?>
