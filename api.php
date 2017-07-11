<?php
/*
|----------------------------
| Settings
|----------------------------
*/
define('DBHOST', 'localhost');
define('DBUSER', 'root');
define('DBPASS', 'root');
define('DBNAME', ‘test’);

define('DEBUG_LOG', '/tmp/debug.log');

/*
|----------------------------
| Functions
|----------------------------
*/
function dbh($host = DBHOST, $user = DBUSER, $pass = DBPASS, $name = DBNAME) {
    $mysqli = new mysqli($host, $user, $pass, $name);
    if ($mysqli->connect_error) {
        die(sprintf("Connect Error (%s) %s)",
            $mysqli->connect_errno, $mysqli->connect_error));
    }
    return $mysqli;
}

function get($table, $where, $binds) {
    $values = array();
    $result = array();

    foreach ($binds as $key => $value) {
        $values[] = &$binds[$key];
    }
    $sql = sprintf("SELECT * FROM %s WHERE %s", $table, $where);

    $dbh = dbh();
    $sth = $dbh->prepare($sql);
    array_unshift($values, str_repeat('s', count($values)));
    @call_user_func_array(array($sth, 'bind_param'), $values);
    $sth->execute();
    $resultset = $sth->get_result();
    while ($row = $resultset->fetch_assoc()) {
        $result[] = $row;
    }
    $sth->close();
    $dbh->close();
    return $result;
}

function insert($table, $params) {
    $keys   = array();
    $values = array();
    $count  = count($params);

    foreach ($params as $key => $value) {
        $keys[] = $key;
        $values[] = &$params[$key];
    }
    $sql = sprintf("INSERT INTO %s (%s) VALUES (%s)",
        $table,
        implode(',', $keys),
        implode(',', array_fill(0, $count, '?'))
    );

    $dbh = dbh();
    $sth = $dbh->prepare($sql);
    array_unshift($values, str_repeat('s', $count));
    @call_user_func_array(array($sth, 'bind_param'), $values);
    $sth->execute();
    $sth->close();
    $dbh->close();
    return;
}

function update($table, $params, $where, $binds) {
    $keys   = array();
    $values = array();

    foreach ($params as $key => $value) {
        $keys[] = "$key=?";
        $values[] = &$params[$key];
    }
    foreach ($binds as $key => $value) {
        $values[] = &$binds[$key];
    }
    $sql = sprintf("UPDATE %s SET (%s) WHERE (%s)",
        $table, implode(',', $keys), $where);

    $dbh = dbh();
    $sth = $dbh->prepare($sql);
    array_unshift($values, str_repeat('s', count($values)));
    @call_user_func_array(array($sth, 'bind_param'), $values);
    $sth->execute();
    $sth->close();
    $dbh->close();
    return;
}

function output_log($str) {
    $file = DEBUG_LOG;
    $current = file_get_contents($file);
    $current .= $str;
    file_put_contents($file, $current);
}

