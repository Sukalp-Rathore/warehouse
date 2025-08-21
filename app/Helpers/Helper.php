<?php

namespace App\Helpers;
use MongoDB\BSON\UTCDateTime;
use DateTime;
use Carbon\Carbon;


function getEvents(){
    $events = ['Birthday' , 'Anniversary' , 'Wedding' , 'Engagement' , 'Housewarming' , 'Baby Shower' , 'Graduation' , 'Promotion' , 'Farewell' , 'Get Well Soon' , 'Thank You' , 'Congratulations' , 'Sympathy'];
    return $events;
}


function readCSVFile($filePath) {
    // Check if the file exists
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return false;
    }

    $data = [];
    
    // Open the CSV file
    if (($handle = fopen($filePath, 'r')) !== false) {
        // Loop through each line of the CSV file
        while (($row = fgetcsv($handle)) !== false) {
           
            $data[] = $row; // Store the row in the $data array
        }
        fclose($handle); // Close the file
    }

    return $data;
}

function isValidAlphanumericString($string) {
    // Check if the string is alphanumeric and within 10 to 30 characters
    return preg_match('/^[a-zA-Z0-9]{10,30}$/', $string);
}

function isValidMobileNumber($mobileNumber) {
    // Check if the number contains exactly 10 digits
    return preg_match('/^\d{10}$/', $mobileNumber);
}

function isValidDateFormat($date) {
    $format = 'Y-m-d';
    $d = DateTime::createFromFormat($format, $date);
    
    // Check if the date matches the format and is a valid date
    return $d && $d->format($format) === $date;
}

function convertToYMD($date) {
    // Define possible date formats
 
    $formats = [
        'd.m.Y',     // e.g., 17.07.2022
        'j/M/y',     // e.g., 9/Mar/27
        'j-M-y',     // e.g., 8-Oct-26
        'd-m-y-Y',   // e.g., 8-02-02-2027
        'j/M/y',     // e.g., 5/Oct/26
        'd/m/Y',
        'Y-m-d',
    ];

    foreach ($formats as $format) {
        $dateObject = DateTime::createFromFormat($format, $date);
        if ($dateObject) {
            return $dateObject->format('Y-m-d');
        }
    }

    // Fallback to strtotime() if no formats match
    $timestamp = strtotime($date);
    if ($timestamp) {
        return date('Y-m-d', $timestamp);
    }

    return "Invalid date format";
}


function conf(string $param){
   return Config::get("app.$param");
}

function setutc($date = null)
{
    $timestamp = null;

    if (is_null($date)) {
        $timestamp = time();
    } elseif ($date instanceof \DateTime) {
        $timestamp = $date->getTimestamp();
    } elseif (is_numeric($date)) {
        $timestamp = (int) $date;
    } elseif (is_string($date)) {
        // Handle dd.mm.yyyy or dd-mm-yyyy format
        if (preg_match("/^\d{2}[.\-\/]\d{2}[.\-\/]\d{4}$/", $date)) {
            $dt = \DateTime::createFromFormat('d.m.Y', $date) 
                ?: \DateTime::createFromFormat('d-m-Y', $date) 
                ?: \DateTime::createFromFormat('d/m/Y', $date);
            if ($dt) $timestamp = $dt->getTimestamp();
        } else {
            $timestamp = strtotime($date);
        }
    }

    if ($timestamp === false || $timestamp === null) {
        return null; // invalid date
    }

    return new \MongoDB\BSON\UTCDateTime(($timestamp + conf('time')) * 1000);
}


function checkIsAValidDate($myDateString){
    return (bool)strtotime($myDateString);
}

function objectId($oid)
{

    return new \MongoDB\BSON\ObjectId($oid);

}


function dateDiffInDays($date1, $date2) 
  {
     // dd($date2, $date1);
      // Calculating the difference in timestamps
      $diff = strtotime($date2) - strtotime($date1);
  
      // 1 day = 24 hours
      // 24 * 60 * 60 = 86400 seconds
      return (int)abs(round($diff / 86400));
  }

//new getutc sukalp
function getutc($date, string $format = "Y-m-d H:i:s")
{
    if (empty($date)) return "";

    try {
        if ($date instanceof \MongoDB\BSON\UTCDateTime) {
            $datetime = $date->toDateTime();
        } elseif ($date instanceof \DateTime) {
            $datetime = $date;
        } elseif (is_numeric($date)) {
            $datetime = (new \MongoDB\BSON\UTCDateTime((int)$date * 1000))->toDateTime();
        } elseif (is_string($date)) {
            // dd.mm.yyyy
            if (preg_match("/^\d{2}[.\-\/]\d{2}[.\-\/]\d{4}$/", $date)) {
                $dt = \DateTime::createFromFormat('d.m.Y', $date)
                    ?: \DateTime::createFromFormat('d-m-Y', $date)
                    ?: \DateTime::createFromFormat('d/m/Y', $date);
                if (!$dt) throw new \Exception("Invalid custom date format");
                $datetime = $dt;
            } else {
                $timestamp = strtotime($date);
                if ($timestamp === false) throw new \Exception("Invalid date string");
                $datetime = (new \MongoDB\BSON\UTCDateTime($timestamp * 1000))->toDateTime();
            }
        } else {
            throw new \Exception("Unsupported date format");
        }

        return $datetime->format($format);
    } catch (\Exception $e) {
        return "";
    }
}

// new end 

function getDatesFromRange($start, $end, $format = 'Y-m-d') {
    

    $array = array();
    $interval = new DateInterval('P1D');

    $realEnd = new DateTime($end);
    $realEnd->add($interval);
    

    $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

    foreach($period as $date) { 
        $array[] = $date->format($format); 
    }
    return $array;
}


function bulkWrite(){

$bulk = new \MongoDB\Driver\BulkWrite();

return $bulk;
}

function mongoDBManager(){

   return new \MongoDB\Driver\Manager(conf('mongodb'));
}


