<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set('display_errors', E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

$config = parse_ini_file("config.ini");


header("Content-type: application/json; charset=utf-8");

if (filter_has_var(INPUT_GET, "nav")) {
    echo loadFile("links.json");
}

if (filter_has_var(INPUT_GET, "jenkins")) {
    $jobs_json = json_decode(loadFile("jenkins.json"));
    if (filter_has_var(INPUT_GET, "job")) {
        $job = filter_input(INPUT_GET, "job", FILTER_SANITIZE_URL);
        foreach ($jobs_json as $k => $v) {
            if ($v->link === $job . "api/json") {
                echo buildJob($job, $v->user, $v->token);
                break;
            }
        }
    } else {
        $urls = array();
        $users = array();
        $tokens = array();
        foreach ($jobs_json as $k => $v) {
            if ($v->user === "USERNAME") {
                break;
            }
            $urls[] = $v->link;
            $users[] = $v->user;
            $tokens[] = $v->token;
        }
        echo execCurls($urls, $users, $tokens);
    }
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

function decodeRow(array $row) {
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

function getJob(string $url, string $user, string $token) {
    return execCurl($url, $user, $token, false, "");
}

function buildJob(string $url, string $user, string $token) {
    return execCurl($url, $user, $token, true, "build");
}

function execCurls(array $urls, array $users, array $tokens) {
    $mh = curl_multi_init();
    $opts = array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 30, CURLOPT_CONNECTTIMEOUT => 60, CURLOPT_ENCODING => 'gzip', CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    $j = 0;
    for ($i = 0; $i < sizeof($urls); $i++) {
        $ch = curl_init($urls[$i]);
        curl_setopt_array($ch, $opts);
        curl_setopt($ch, CURLOPT_USERPWD, $users[$i] . ":" . $tokens[$i]);
        curl_multi_add_handle($mh, $ch);
    }
    $results = array();
    do {
        while (($exec = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
        if ($exec != CURLM_OK) {
            break;
        }
        while ($ch = curl_multi_info_read($mh)) {
            $j++;
            $ch = $ch['handle'];
            $results[] = json_decode(curl_multi_getcontent($ch));
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
    } while ($running);
    curl_multi_close($mh);

    return json_encode($results);
}

function execCurl(string $url, string $user, string $token, bool $post, string $param) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . $param);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

    if ($post) {
        curl_setopt($ch, CURLOPT_POST, 1);
    }
    curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $token);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output);
}

function startsWith($string, $startString) {
    $len = strlen($startString);
    return substr($string, 0, $len) === $startString;
}
