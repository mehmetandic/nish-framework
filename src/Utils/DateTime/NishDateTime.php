<?php
namespace Nish\Utils\DateTime;


class NishDateTime
{
    private static $timezone;

    /* @var \DateTime */
    private static $dateTimeObj = null;

    /* @var \DateTimeZone */
    private static $dateTimeZoneObj = null;

    /**
     * @return mixed
     */
    public static function getTimezone()
    {
        if (empty(self::$timezone)) {
            self::$timezone = date_default_timezone_get();
        }

        return self::$timezone;
    }

    /**
     * @param mixed $timezone
     */
    public static function setTimezone($timezone)
    {
        self::$timezone = $timezone;

        if(self::$dateTimeObj instanceof \DateTime)
        {
            self::setDateTimeZoneObj();

            self::$dateTimeObj->setTimezone(self::$dateTimeZoneObj);
        }
    }


    public static function format($UnixTimestamp, string $Format){
        self::setDateTimeObj();

        self::$dateTimeObj->setTimestamp($UnixTimestamp);
        return self::$dateTimeObj->format($Format);
    }

    public static function makeTimestamp($Hour, $Minute, $Second, $Month, $Day, $Year){
        self::setDateTimeObj();

        self::$dateTimeObj->setDate($Year, $Month, $Day);
        self::$dateTimeObj->setTime($Hour,$Minute,$Second);
        return self::$dateTimeObj->getTimestamp();
    }

    public static function getTimestamp(){
        self::setDateTimeObj();

        return self::$dateTimeObj->getTimestamp();
    }

    public static function getCurrentTimestamp(){
        $dtObj = new \DateTime('now');
        self::setDateTimeZoneObj();
        $dtObj->setTimezone(self::$dateTimeZoneObj);
        return $dtObj->getTimestamp();
    }

    public static function getTimezoneName(){
        self::setDateTimeZoneObj();

        return self::$dateTimeZoneObj->getName();
    }

    public static function getDayBoundariesOfTime($UnixTimeStamp = null){
        if(!is_numeric($UnixTimeStamp)){
            $UnixTimeStamp = self::getCurrentTimestamp();
        }
        $toks = explode('.', self::format($UnixTimeStamp,'d.m.Y'));

        return array(
            self::makeTimestamp(0,0,0,$toks[1], $toks[0], $toks[2]),
            self::makeTimestamp(23,59,59,$toks[1], $toks[0], $toks[2])
        );
    }
    
    private static function setDateTimeZoneObj()
    {
        try{
            self::$dateTimeZoneObj = new \DateTimeZone(self::getTimezone());
        }
        catch(Exception $e){
            self::$dateTimeZoneObj = new \DateTimeZone(date_default_timezone_get());
        }
    }

    /**
     * @throws \Exception
     */
    private static function setDateTimeObj(){
        if(self::$dateTimeObj == null){
            self::$dateTimeObj = new \DateTime();

            self::setDateTimeZoneObj();

            self::$dateTimeObj->setTimezone(self::$dateTimeZoneObj);
        }
    }
}