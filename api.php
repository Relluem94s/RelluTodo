<?php
header("Content-type: application/json; charset=utf-8");

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set('display_errors', E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

include "./vendor/relluem94/relludatabase/Database.php";

$config = parse_ini_file("config.ini");

if (filter_has_var(INPUT_GET, "nav")) {
    echo file_get_contents("links.json");
}

if (filter_has_var(INPUT_GET, "jenkins")) {
    $jobs_json = json_decode(file_get_contents("jenkins.json"));
    if (filter_has_var(INPUT_GET, "job")) {
        $job = filter_input(INPUT_GET, "job", FILTER_SANITIZE_URL);
        foreach ($jobs_json as $k => $v) {
            if (str_replace("%20", "", $v->link) === $job . "api/json") {
                echo buildJob(str_replace("api/json", "", $v->link), $v->user, $v->token);
                break;
            }
        }
    } else {
        $urls = array();
        $users = array();
        $tokens = array();
        foreach ($jobs_json as $k => $v) {
            if ($v->link !== "https://JENKINSURL/job/JOB/api/json" && !isset($v->user)) {
                $urls[] = $v->link;
                $users[] = null;
                $tokens[] = null;
            }
            else if(isset($v->user) && $v->user !== "USERNAME"){
                $urls[] = $v->link;
                $users[] = (isset($v->user)) ? $v->user : null;
                $tokens[] = (isset($v->token)) ? $v->token : null;
            }
        }
        echo execCurls($urls, $users, $tokens);
    }
}

if (filter_has_var(INPUT_GET, "todo")) {
    $db = new Database($config["db_host"], $config["db_user"], $config["db_password"], $config["db_schema"]);
    $address = filter_input(INPUT_SERVER, "REMOTE_ADDR");
    if (filter_has_var(INPUT_POST, "id") && filter_has_var(INPUT_POST, "text")) {
        $id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);
        $text = stripslashes(filter_input(INPUT_POST, "text", FILTER_SANITIZE_SPECIAL_CHARS));
        if (!empty($id) && !empty($text)) {
            echo $db->update("assets/sql/updateTodo.sql", array($text, $address, $id));
        }
    }

    // Insert Todo
    else if (filter_has_var(INPUT_POST, "todo")) {
        $todo = stripslashes(filter_input(INPUT_POST, "todo", FILTER_SANITIZE_SPECIAL_CHARS));
        if (!empty($todo)) {
            echo $db->insert("assets/sql/insertTodo.sql", array($todo, $address));
        }
    }

    // Search Todo
    else if (filter_has_var(INPUT_POST, "search")) {
        $search = filter_input(INPUT_POST, "search", FILTER_SANITIZE_STRING);
        echo json_encode($db->select(is_numeric($search) ? "assets/sql/searchTodosById.sql" : "assets/sql/searchTodosByText.sql", is_numeric($search) ? array($search) : array("%" . $search . "%")), JSON_NUMERIC_CHECK);
    }

    // Delete or Restore Todo
    else if (filter_has_var(INPUT_POST, "id")) {
        $id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);
        if (!empty($id)) {
            $todo = $db->select("assets/sql/getTodoById.sql", array($id));
            if ($todo[0] !== null && ($todo[0]["deleted"] == 'null' or $todo[0]["deleted"] == '0' or $todo[0]["deleted"] == 0 or $todo[0]["deleted"] == "")) {
                echo $db->update("assets/sql/deleteTodo.sql", array($address, $id));
            } else {
                echo $db->update("assets/sql/restoreTodo.sql", array($address, $id));
            }
        }
    }

    // Load Todos
    if (filter_has_var(INPUT_GET, "todos")) {
        echo json_encode($db->select("assets/sql/getTodos.sql", array()), JSON_NUMERIC_CHECK);
    }

    //Load Stats
    if (filter_has_var(INPUT_GET, "stats")) {
        echo json_encode($db->select("assets/sql/getStatsTodos.sql" , array())[0], JSON_NUMERIC_CHECK);
    }
    $db->close();
}

function getJob(string $url, string $user, string $token) {
    return execCurl($url, $user, $token, false, "");
}

function buildJob(string $url, string $user, string $token) {
    return execCurl($url, $user, $token, true, "build");
}

function execCurls(array $urls, array $users, array $tokens) {



    $mh = curl_multi_init();
    $opts = array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 30, CURLOPT_CONNECTTIMEOUT => 60, CURLOPT_ENCODING => 'gzip', CURLOPT_IPRESOLVE);
    for ($i = 0; $i < sizeof($urls); $i++) {
        $ch = curl_init($urls[$i]);
        curl_setopt_array($ch, $opts);
        if(isset($users[$i]) && $users[$i] !== null && isset($tokens[$i]) && $tokens[$i] !== null){
            curl_setopt($ch, CURLOPT_USERPWD, $users[$i] . ":" . $tokens[$i]);
        }
        curl_multi_add_handle($mh, $ch);
    }
    
    return getCurlResults($mh);
}

function getCurlResults($mh){
    $results = array();
    do {
        while (($exec = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
        if ($exec != CURLM_OK) {
            break;
        }
        while ($mch = curl_multi_info_read($mh)) {
            $ch = $mch['handle'];
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
