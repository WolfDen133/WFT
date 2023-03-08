<?php

namespace WolfDen133\WFT\Utils;

class Time
{
    private static string $dateFormat;
    private static string $timeFormat;
    private static string $timezone;

    public function __construct(string $timezone, string $dateFormat, string $timeFormat)
    {
        self::$timezone = $timezone;
        self::$timeFormat = $timeFormat;
        self::$dateFormat = $dateFormat;
    }

    public static function getDate () : string
    {
        return (new \DateTime("now", new \DateTimeZone(self::$timezone)))->format(self::$dateFormat);
    }

    public static function getTime () : string
    {
        return (new \DateTime("now", new \DateTimeZone(self::$timezone)))->format(self::$timeFormat);
    }
}