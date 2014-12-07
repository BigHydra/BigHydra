<?php
namespace Hydra\BigHydraBundle\Library;

class TimeCalculator
{
    /**
     * @param int $seconds
     * @return string
     */
    public static function secondsToHumanReadableTime($seconds)
    {
        $minutes = $seconds / 60;
        $hours = $minutes / 60;
        if ($hours >= 1) {
            $time = number_format($hours, 2) . " hours";
        } else {
            $time = "$minutes minutes";
        }
        return $time;
    }
}
