<?php

function db_query($query) {
        global $config;
        if ($query != "") {
                $link = mysqli_connect($config['dbhost'], $config['dbuser'], $config['dbpass'],$config['dbname']);
                if (!$link) {
                    die('ERR: Could not connect to the database');
                }
                mysqli_set_charset($link, 'utf8');
                $resopt = mysqli_query($link, "SET sql_mode = ''");
                if (!$resopt) {
                    error_log('Could not set sql mode');
                }
                $result = mysqli_query($link, $query);
                if (!$result) {
                    die('ERR: Invalid query: ' . mysqli_error($link));
                }
                mysqli_close($link);
                return($result);
        } else die('empty query');
}

function ellipse($string, $width) {
  $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
  $parts_count = count($parts);

  $length = 0;
  $last_part = 0;
  for (; $last_part < $parts_count; ++$last_part) {
    $length += strlen($parts[$last_part]);
    if ($length > $width) { break; }
  }

  return preg_replace("/\n/", ' ', join(array_slice($parts, 0, $last_part)))."...";
}

?>
