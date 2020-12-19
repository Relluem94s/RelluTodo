<?php

error_reporting(~0);
ini_set('display_errors', 1);

$config = parse_ini_file("config.ini");


header("Content-type: application/json");

if (isset($_GET["ip"])) {

}

if (isset($_GET["nav"])) {
    echo loadFile("links.json");
}

if (isset($_GET["todo"])) {
    $db_link = mysqli_connect($config["db_host"], $config["db_user"], $config["db_password"], $config["db_schema"]);

    if (isset($_POST["id"], $_POST["text"])) {
        $id = mysqli_real_escape_string($db_link, $_POST["id"]);
        $text = utf8_decode(mysqli_real_escape_string($db_link, $_POST["text"]));
        $address = mysqli_real_escape_string($db_link, $_SERVER['REMOTE_ADDR']);

        if (!empty($id) && !empty($text)) {
            $statment = mysqli_prepare($db_link, loadFile("sql/updateTodo.sql"));
            $statment->bind_param("ssi", $text, $address, $id);
            $statment->execute();

            echo mysqli_error($db_link);
        }
    }

    // Insert Todo
    else if (isset($_POST["todo"])) {
        $todo = utf8_decode(mysqli_real_escape_string($db_link, $_POST["todo"]));
        $address = mysqli_real_escape_string($db_link, $_SERVER['REMOTE_ADDR']);

        if (!empty($todo)) {
            $sql = "INSERT INTO `todo` (`text`, `deleted`, `created`, `createdby`) VALUES('$todo', null, now(), '$address');";
            $db_erg = mysqli_query($db_link, $sql);
            
            echo mysqli_error($db_link);
        }
    }

    // Insert Todo
    else if (isset($_POST["search"])) {
        $search = utf8_decode(mysqli_real_escape_string($db_link, $_POST["search"]));
        $sql = 'select t.id, t.`text`, date_format(t.created, "%d.%m.%Y %H:%i:%s") as created, date_format(t.updated, "%d.%m.%Y %H:%i:%s") as updated , date_format(t.deleted, "%d.%m.%Y %H:%i:%s") as deleted, t.createdby, t.updatedby, t.deletedby from todo t';

        if (is_numeric($search)) {
            $sql .= ' where t.id =' . $search . ';';
        } else {
            $sql .= ' where t.text like("%' . $search . '%");';
        }

        $todos = loadSQL($sql);

        $todos_with_links = genLinks($todos);

        echo json_encode($todos_with_links);
    }

    // Delete or Restore Todo
    else if (isset($_POST["id"])) {
        $id = mysqli_real_escape_string($db_link, $_POST["id"]);
        $address = mysqli_real_escape_string($db_link, $_SERVER['REMOTE_ADDR']);

        if (!empty($id)) {
            $sql = "select * from todo WHERE id = $id";
            $db_erg = mysqli_query($db_link, $sql);
            while ($zeile = mysqli_fetch_array($db_erg, MYSQLI_ASSOC)) {
                $del = $zeile["deleted"];
            }

            if ($del == 'null' or $del == '0' or $del == 0) {
                $sql = "UPDATE `todo` SET `deleted` = now(), deletedby = '$address' WHERE `todo`.`id` = $id;";
                $db_erg = mysqli_query($db_link, $sql);

                echo mysqli_error($db_link);
            } else {
                $sql = "UPDATE `todo` SET `deleted` = null, `updated` = now(), deletedby = null, updatedby = '$address' WHERE `todo`.`id` = $id;";
                $db_erg = mysqli_query($db_link, $sql);

                echo mysqli_error($db_link);
            }
        }
    }


    // Load Todos
    if (isset($_GET["todos"])) {
        $todos = loadSQL(loadFile("sql/getTodos.sql"));

        $todos_with_links = genLinks($todos);

        echo json_encode($todos_with_links);
    }


    //Load Stats
    if (isset($_GET["stats"])) {
        echo json_encode(stats());
    }

    mysqli_close($db_link);
}

function loadFile($filename){
    return file_get_contents($filename);
}

function loadSQL($sql) {
    global $db_link;
    $result = mysqli_query($db_link, $sql);
    $todos = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $row_data = array();
            foreach ($row as $k => $v) {
                $row_data += array($k => (utf8_encode($v)));
            }
            $todos[] = $row_data;
        }
        $result->close();
    }
    return $todos;
}

function loadTodosCount() {
    global $db_link;
    $sql = 'select count(t.id) as amount from todo t';
    $result = mysqli_query($db_link, $sql);
    $amount = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $amount = $row['amount'];
        }
        $result->close();
    }
    return $amount;
}

function stats() {
    $open_now = 0;
    $open_this_day = 0;
    $closed_this_day = 0;
    $open_this_week = 0;
    $closed_this_week = 0;
    $open_this_month = 0;
    $closed_this_month = 0;
    $all_todos = loadTodosCount();


    $week = grk_Week_Range(date("Y-m-d h:i:sa"), -1);
    $month = grk_Month_Range(date("Y-m-d h:i:sa"), -1);


    foreach (loadSQL('select t.id, t.deleted, t.created, t.updated, t.createdby, t.updatedby, t.deletedby
from todo t 
where t.deleted is null or ( MONTH(t.deleted) = MONTH(now()) and YEAR(t.deleted) = YEAR(now()))') as $k => $v) {
        $datetime1 = new DateTime($v['created']);
        $datetime3 = new DateTime('today');

        $datetime1->setTime(0, 0, 0);
        $datetime3->setTime(0, 0, 0);

        $i1 = date_diff($datetime1, $datetime3);

        if ($v['deleted'] != null) {
            $datetime2 = new DateTime($v['deleted']);

            $datetime2->setTime(0, 0, 0);

            $i2 = date_diff($datetime2, $datetime3);

            if ($i2->format('%a') == 0) {
                $closed_this_day++;
            }
            if ($datetime2->format('Y-m-d') <= $week[1] && $datetime2->format('Y-m-d') >= $week[0]) {
                $closed_this_week++;
            }
            if ($datetime2->format('Y-m-d') <= $month[1] && $datetime2->format('Y-m-d') >= $month[0]) {
                $closed_this_month++;
            }
        } else {
            $open_now++;
        }

        if ($i1->format('%a') == 0) {
            $open_this_day++;
        }

        if ($v['created'] < $week[1] && $v['created'] > $week[0]) {
            $open_this_week++;
        }

        if ($v['created'] < $month[1] && $v['created'] > $month[0]) {
            $open_this_month++;
        }
    }

    return array(
        array("title" => "Open: ", "amount" => $open_now, "label" => "label-danger"),
        array("title" => "New Today: ", "amount" => $open_this_day, "label" => "label-warning"),
        array("title" => "Done Today: ", "amount" => $closed_this_day, "label" => "label-success"),
        array("title" => "New This Week: ", "amount" => $open_this_week, "label" => "label-warning"),
        array("title" => "Done This Week: ", "amount" => $closed_this_week, "label" => "label-success"),
        array("title" => "New This Month: ", "amount" => $open_this_month, "label" => "label-warning"),
        array("title" => "Done This Month: ", "amount" => $closed_this_month, "label" => "label-success"),
        array("title" => "All: ", "amount" => $all_todos, "label" => "label-info")
    );
}

function grk_Week_Range($DateString, $FirstDay = 6) {
    if (empty($DateString) === TRUE) {
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
    $DT_Min = new DateTime('last ' . (isset($Days[$FirstDay]) === TRUE ? $Days[$FirstDay] : $Days[6]) . ' ' . $DateString);
    $DT_Max = clone($DT_Min);
    return array(
        $DT_Min->modify('+1 day')->format('Y-m-d'),
        $DT_Max->modify('+7 days')->format('Y-m-d')
    );
}

function grk_Month_Range($DateString, $FirstDay = 1) {
    if (empty($DateString) === TRUE) {
        $DateString = date('Y-m-d');
    }
    $DT_Min = new DateTime('now');
    $DT_Max = clone($DT_Min);
    return array(
        $DT_Min->format('Y-m-01'),
        $DT_Max->format('Y-m-t')
    );
}

function genLinks($array) {
    $out = array();
    foreach ($array as $k => $v) {

        $links = array();

        if (isset($v["text"])) {
            $temp = explode(" ", str_replace("\r", " ", str_replace("\n", " ", $v["text"])));
            if (is_array($temp)) {
                foreach ($temp as $k2 => $v2) {
                    if (startsWith($v2, "http")) {
                        $short_temp = explode("//", $v2);

                        if (isset($short_temp[1])) {
                            $short = $short_temp[1];
                            $links[] = array("link" => $v2, "short" => $short);
                        }
                    }
                }
            }
        }
        $v["links"] = $links;
        $out[] = $v;
    }

    return $out;
}

function startsWith($string, $startString) {
    $len = strlen($startString);
}

?>

