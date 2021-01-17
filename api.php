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

        echo json_encode(loadSQL($sql));
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
        echo json_encode(loadSQL(loadFile("assets/sql/getTodos.sql")));
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
            $todos[] = decodeRow($row);
        }
        $result->close();
    }
    return $todos;
}

function decodeRow(array $row){
    $row_data = array();
    foreach ($row as $k => $v) {
        $row_data += array($k => html_entity_decode(($v), ENT_QUOTES | ENT_HTML5));
    }
    return $row_data;
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
    return loadSQL(loadFile("assets/sql/getStatsTodos.sql"))[0];
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