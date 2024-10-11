<?php

function dd($param) {
  echo '<pre><code>';
  var_dump($param); // var_export($param)
  print '</code></pre>'; // get_defined_constants(true)['user']'
  return die();
}

/**
This function takes in two parameters: $base and $path, which represent the base path and the path to be made relative, respectively.

It first detects the directory separator used in the base path, then splits both paths into arrays using that separator. It then removes the common base elements from the beginning of the path array, leaving only the difference.

Finally, it returns the relative path by joining the remaining elements in the path array using the separator detected earlier, with the separator prepended to the resulting string.

* Return a relative path to a file or directory using base directory. 
* When you set $base to /website and $path to /website/store/library.php
* this function will return /store/library.php
* 
* Remember: All paths have to start from "/" or "\" this is not Windows compatible.
* 
* @param   String   $base   A base path used to construct relative path. For example /website
* @param   String   $path   A full path to file or directory used to construct relative path. For example /website/store/library.php
* 
* @return  String
*/
function getRelativePath($base, $path) {
  // Detect directory separator
  $separator = substr($base, 0, 1);
  $base = array_slice(explode($separator, rtrim($base,$separator)),1);
  $path = array_slice(explode($separator, rtrim($path,$separator)),1);

  return $separator.implode($separator, array_slice($path, count($base)));
}

/* File: print_rtf.php */
function birth_age_calc($birthDate) {
  //date in mm/dd/yyyy format; or it can be in other formats as well
  //$birthDate = "12/17/1983";
  //explode the date to get month, day and year
  $birthDate = date('d/m/Y', strtotime(str_replace('-', '/', $birthDate)));
  $birthDate = explode("/", $birthDate);
  //get age from date or birthdate
  $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md")
    ? ((date("Y") - $birthDate[2]) - 1)
    : (date("Y") - $birthDate[2]));
  return $age; // - 1	
}

function files_identical($fn1, $fn2) {
    if(filetype($fn1) !== filetype($fn2))
        return FALSE;

    if(filesize($fn1) !== filesize($fn2))
        return FALSE;

    if(!$fp1 = fopen($fn1, 'rb'))
        return FALSE;

    if(!$fp2 = fopen($fn2, 'rb')) {
        fclose($fp1);
        return FALSE;
    }

    $same = TRUE;
    while (!feof($fp1) and !feof($fp2))
        if(fread($fp1, READ_LEN) !== fread($fp2, READ_LEN)) {
            $same = FALSE;
            break;
        }

    if(feof($fp1) !== feof($fp2))
        $same = FALSE;

    fclose($fp1);
    fclose($fp2);

    return $same;
}

/*
 * https://stackoverflow.com/questions/1735252/php-countdown-to-date
*/

function countdown($time, $up = true, $h = true, $m = true, $s = true) {
  $rem = ($up == true) ? time() - $time : $time - time();
  $day = floor($rem / 86400);
  $hr = floor(($rem % 86400) / 3600);
  $min = floor(($rem % 3600) / 60);
  $sec = $rem % 60;

  if ( $day && !$h )
    if ( $hr > 12 ) $day++; // round up if not displaying hours

  $ret = [];
  if ( $day && $h ) $ret[] = $day ? $day . " day" . ($day == 1 ? "" : "s") : "";
  if ( $day && !$h ) $ret[] = $day ? $day . " day" . ($day == 1 ? "" : "s") : "";
  if ( $hr && $h ) $ret[] = $hr ? $hr . " hr" . ($hr == 1 ? "" : "s") : "";
  if ( $min && $m && $h ) $ret[] = $min ? $min . " min" . ($min == 1 ? "" : "s") : "";
  if ( $sec && $s && $m && $h ) $ret[] = $sec ? $sec . " sec" . ($sec == 1 ? "" : "s") : "";

  $last = end($ret);
  array_pop($ret);
  $string = join(", ", $ret) . (count($ret) < 1? '' : ' and ') . $last;

  return $string;
}

// Snippet from PHP Share: http://www.phpshare.org

function formatSizeUnits($bytes)
{
  if ($bytes >= 1073741824)
    $bytes = number_format($bytes / 1073741824, 2) . ' GB';
  elseif ($bytes >= 1048576)
    $bytes = number_format($bytes / 1048576, 2) . ' MB';
  elseif ($bytes >= 1024)
    $bytes = number_format($bytes / 1024, 2) . ' KB';
  elseif ($bytes > 1)
    $bytes = $bytes . ' bytes';
  elseif ($bytes == 1)
    $bytes = $bytes . ' byte';
  else
    $bytes = '0 bytes';
  return $bytes;
}

function convertToBytes($value) {
    $unit = strtolower(substr($value, -1, 1));
    return (int) $value * pow(1024, array_search($unit, [1 => 'k', 'm', 'g']));
}

/**
* Converts bytes into human readable file size.
*
* @param string $bytes
* @return string human readable file size (2,87 ??)
* @author Mogilev Arseny
* @bug Does not handle 0 bytes
*/
function FileSizeConvert($bytes)
{
  $bytes = floatval($bytes);
  $arBytes = [
    0 => [
      "UNIT" => "TB",
      "VALUE" => pow(1024, 4)
    ],
    1 => [
      "UNIT" => "GB",
      "VALUE" => pow(1024, 3)
    ],
    2 => [
      "UNIT" => "MB",
      "VALUE" => pow(1024, 2)
    ],
    3 => [
      "UNIT" => "KB",
      "VALUE" => 1024
    ],
    4 => [
      "UNIT" => "B",
      "VALUE" => 1
    ],
  ];

  foreach($arBytes as $arItem)
  {
    if($bytes >= $arItem["VALUE"])
    {
      $result = $bytes / $arItem["VALUE"];
      $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
      break;
    }
  }
  return $result;
}

function getAge($date)
{
  $from = ($date == '0000-00-00') ? new DateTime() : new DateTime($date);
  $to   = new DateTime('today');

  return intval($from->diff($to)->y);
  //return intval(date('Y', time() - strtotime($date))) - 1970;
  //return intval(date("Y") - date("Y", strtotime($date))); 
}

function split_name($name)
{
    $name = trim($name);
    $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
    $first_name = trim( preg_replace("#$last_name#", '', $name ) );
    return [$first_name, $last_name];
}

//https://odan.github.io/2017/10/29/installing-an-ssl-certificate-under-apache-xampp.html

// Is the user using HTTPS?
//$baseurl = ;

function phpdate($format="Y-m-d")
{
  ob_start(); phpinfo(1);
  if (preg_match('~Build Date (?:=> )?\K.*~', strip_tags(ob_get_clean()), $out))
    return date($format, strtotime($out[0]));
}
