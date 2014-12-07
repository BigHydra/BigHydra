<?php
namespace Hydra\BigHydraBundle\Library;

class DateCalculator
{
    /**
     * @return int
     */
    public function getCurrentWeekNumber ()
    {
        $week = date('W');

        return (int) $week;
    }

    /**
     * @return int
     */
    public function getLastWeekNumber ()
    {
        $week = date('W')-1;

        return (int) $week;
    }

    /**
     * @param int $weekNumber
     * @return string Y-m-d
     */
    public function getFirstDayOfWeek ($weekNumber)
    {
        $weekDate = new \DateTime();
        $weekDate->setISODate($weekDate->format('Y'), $weekNumber);

        return $weekDate->format('Y-m-d');
    }

    /**
     * @param int $weekNumber
     * @return string Y-m-d
     */
    public function getLastDayOfWeek ($weekNumber)
    {
        $weekDate = new \DateTime();
        $weekDate->setISODate($weekDate->format('Y'), $weekNumber);

        return $weekDate->add(new \DateInterval('P6D'))->format('Y-m-d');
    }

    /**
     * @param int $weekNumber
     * @return string[]
     */
    public function getDaysOfWeek ($weekNumber)
    {
        $date = new \DateTime();
        $date->setISODate($date->format('Y'), $weekNumber);

        $result = array();
        $result[] = $date->format('Y-m-d');
        for ($i = 1; $i < 7; $i++) {
            $result[] = $date->add(new \DateInterval('P1D'))->format('Y-m-d');
        }

        return $result;
    }

    /**
     * @param int $weekNumber
     *
     * @return string[]
     */
    public function getDayRangesOfWeek ($weekNumber)
    {
        $date = new \DateTime();
        $date->setISODate($date->format('Y'), $weekNumber);
        $date->setTime(0, 0, 0);
        $date->sub(new \DateInterval('P1D'));

        $result = array();
        for ($i = 1; $i < 7; $i++) {
            $day = clone $date->add(new \DateInterval('P1D'));
            $result[] = [
                $day->format('Y-m-d'),
                $day->add(new \DateInterval('P1D'))->format('Y-m-d'),
            ];
        }

        return $result;
    }
}
