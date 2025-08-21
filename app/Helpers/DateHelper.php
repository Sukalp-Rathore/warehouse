<?php

use MongoDB\BSON\UTCDateTime;
// use DateTime;

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

    return new \MongoDB\BSON\UTCDateTime(($timestamp + config('app.time_offset', 0)) * 1000);
}

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
