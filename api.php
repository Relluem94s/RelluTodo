<?php

error_reporting(~0);
ini_set('display_errors', 1);

$config = parse_ini_file("config.ini");


header("Content-type: application/json; charset=utf-8");

if (filter_has_var(INPUT_GET, "nav")) {
    echo loadFile("links.json");
}

if (filter_has_var(INPUT_GET, "jenkins")) {
    
    $jobs_json = json_decode(loadFile("jenkins.json"));
    
    $jobs = array();
    foreach($jobs_json as $k => $v){
        if($v->user === "USERNAME"){
            break;
        }
        $jobs[] = getJob($v->link, $v->user, $v->token);
    }
    echo json_encode($jobs);
}

if (filter_has_var(INPUT_GET, "todo")) {
    $db_link = mysqli_connect($config["db_host"], $config["db_user"], $config["db_password"], $config["db_schema"]);
    $address = mysqli_real_escape_string($db_link, filter_input(INPUT_SERVER, "REMOTE_ADDR"));
    if (filter_has_var(INPUT_POST, "id") && filter_has_var(INPUT_POST, "text")) {
        $id = mysqli_real_escape_string($db_link, filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT));
        $text = stripslashes((mysqli_real_escape_string($db_link, filter_input(INPUT_POST, "text", FILTER_SANITIZE_SPECIAL_CHARS))));
        if (!empty($id) && !empty($text)) {
            $statment = mysqli_prepare($db_link, loadFile("assets/sql/updateTodo.sql"));
            $statment->bind_param("ssi", $text, $address, $id);
            $statment->execute();
            $statment->close();
            echo mysqli_error($db_link);
        }
    }

    // Insert Todo
    else if (filter_has_var(INPUT_POST, "todo")) {
        $todo = stripslashes((mysqli_real_escape_string($db_link, filter_input(INPUT_POST, "todo", FILTER_SANITIZE_SPECIAL_CHARS))));
        if (!empty($todo)) {
            $statment = mysqli_prepare($db_link, loadFile("assets/sql/insertTodo.sql"));
            $statment->bind_param("ss", $todo, $address);
            $statment->execute();
            $statment->close();
            echo mysqli_error($db_link);
        }
    }

    // Search Todo
    else if (filter_has_var(INPUT_POST, "search")) {
        $search = (mysqli_real_escape_string($db_link, filter_input(INPUT_POST, "search", FILTER_SANITIZE_STRING)));
        $sql = loadFile("assets/sql/searchTodos.sql");
        if (is_numeric($search)) {
            $sql .= ' where t.id =' . $search . ';';
        } else {
            $sql .= ' where t.text like("%' . $search . '%");';
        }

        $todos = loadSQL($sql);
        $todos_with_links = genLinks($todos);
        $todos_with_links_and_searchlabels = genSearchLabel($todos_with_links);
        echo json_encode($todos_with_links_and_searchlabels);
    }

    // Delete or Restore Todo
    else if (filter_has_var(INPUT_POST, "id")) {
        $id = mysqli_real_escape_string($db_link, filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT));
        if (!empty($id)) {
            $statment = mysqli_prepare($db_link, loadFile("assets/sql/getTodoById.sql"));
            $statment->bind_param("i", $id);
            $statment->execute();
            $result = $statment->get_result();
            $statment->close();
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $del = $row["deleted"];
            }
            if ($del == 'null' or $del == '0' or $del == 0) {
                $statment_del = mysqli_prepare($db_link, loadFile("assets/sql/deleteTodo.sql"));
                $statment_del->bind_param("si", $address, $id);
                $statment_del->execute();
                $statment_del->close();
            } else {
                $statment_del = mysqli_prepare($db_link, loadFile("assets/sql/restoreTodo.sql"));
                $statment_del->bind_param("si", $address, $id);
                $statment_del->execute();
                $statment_del->close();
            }
            echo mysqli_error($db_link);
        }
    }


    // Load Todos
    if (filter_has_var(INPUT_GET, "todos")) {
        $todos = loadSQL(loadFile("assets/sql/getTodos.sql"));
        $todos_with_links = genLinks($todos);
        $todos_with_links_and_searchlabels = genSearchLabel($todos_with_links);
        echo json_encode($todos_with_links_and_searchlabels);
    }

    
    //Load Stats
    if (filter_has_var(INPUT_GET, "stats")) {
        echo json_encode(stats());
    }

    mysqli_close($db_link);
}

function loadFile($filename) {
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
                $row_data += array($k => html_entity_decode(($v), ENT_QUOTES | ENT_HTML5));
            }
            $todos[] = $row_data;
        }
        $result->close();
    }
    return $todos;
}

function loadTodosCount() {
    global $db_link;

    $statment = mysqli_prepare($db_link, loadFile("assets/sql/getTotalTodoCount.sql"));
    $statment->execute();

    $result = $statment->get_result();
    $statment->close();

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
    $open_now = $open_this_day = $closed_this_day = $open_this_week = $closed_this_week = $open_this_month = $closed_this_month = 0;
    $all_todos = loadTodosCount();

    $week = grk_Week_Range(date("Y-m-d h:i:sa"), -1);
    $month = grk_Month_Range(date("Y-m-d h:i:sa"), -1);

    $sql = loadFile("assets/sql/getStatsTodos.sql");
    $x = loadSQL($sql);

    foreach ($x as $k => $v) {
        $datetime1 = new DateTime($v['created']);
        $datetime3 = new DateTime('today');

        $datetime1->setTime(0, 0, 0); // Bug if it is midnight
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

    return getStats($open_now, $open_this_day, $closed_this_day, $open_this_week, $closed_this_week, $open_this_month, $closed_this_month, $all_todos);
}

function getStats($open_now, $open_this_day, $closed_this_day, $open_this_week, $closed_this_week, $open_this_month, $closed_this_month, $all_todos){
    return array(
        array("title" => "Todos: ", "amount" => $open_now, "label" => "label-danger", "icon" => 'fas fa-folder-open'),
        array("title" => "Today: ", "amount" => $open_this_day, "label" => "label-warning", "icon" => "fas fa-folder-plus"),
        array("title" => "Today: ", "amount" => $closed_this_day, "label" => "label-success", "icon" => "fas fa-folder"),
        array("title" => "This Week: ", "amount" => $open_this_week, "label" => "label-warning", "icon" => "fas fa-folder-plus"),
        array("title" => "This Week: ", "amount" => $closed_this_week, "label" => "label-success", "icon" => "fas fa-folder"),
        array("title" => "This Month: ", "amount" => $open_this_month, "label" => "label-warning", "icon" => "fas fa-folder-plus"),
        array("title" => "This Month: ", "amount" => $closed_this_month, "label" => "label-success", "icon" => "fas fa-folder"),
        array("title" => "All: ", "amount" => $all_todos, "label" => "label-info", "icon" => "fas fa-globe-europe")
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

function genSearchLabel($array) {
    $out = array();
    foreach ($array as $k => $v) {

        $labels = array();

        if (isset($v["text"])) {
            $temp = explode(" ", str_replace("\r", " ", str_replace("\n", " ", str_replace("\t", " ", $v["text"]))));
            if (is_array($temp)) {
                foreach ($temp as $k2 => $v2) {
                    if (startsWith($v2, "#")) {
                        $start = strpos($v2, "?");
                        $label = $v2;
                        $label = str_replace("#", "", $label);

                        $labels[] = $label;
                    }
                }
            }
        }
        $v["searchlabels"] = $labels;
        $out[] = $v;
    }
    return $out;
}


function getJob(string $url, string $user, string $token){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, $user.":".$token);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output);
}


function startsWith($string, $startString) {
    $len = strlen($startString);
    return substr($string, 0, $len) === $startString;
}
