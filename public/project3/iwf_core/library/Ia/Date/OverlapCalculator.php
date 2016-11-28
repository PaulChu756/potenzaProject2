<?php
/**
 * Adapted from:
 * https://gist.githubusercontent.com/Lusitanian/3445102/raw/1fccea7b59a986827459d3b21e01b19056333d54/OverlapCalculator.php
 *
 * Calculates the amount of overlap between a source time range and a variable number of compared time ranges.
 * Implements a subset of Allen's Interval Algebra.
 * With contributions by rdlowery.
 */
class Ia_Date_OverlapCalculator {

    const UNIT_DAYS = 'days';
    
    const UNIT_MONTHS = 'months';
    
    /**
     * @var int
     */
    private $timeIn;

    /**
     * @var int
     */
    private $timeOut;

    /**
     * @var int
     */
    private $totalOverlapDays = 0;
    
    /**
     * @var int
     */
    private $totalOverlapMonths = 0;

    /**
     * @param DateTime|int $timeIn
     * @param DateTime|int $timeOut
     */
    public function __construct($timeIn, $timeOut) {
            
        if( $timeIn instanceOf DateTime ) {
            $this->timeIn = $timeIn->getTimestamp();
        } elseif ($timeIn===false) {
            $timeIn = 0; //beginning of time
        } else {
            $this->timeIn = $timeIn;
        }

        if( $timeOut instanceOf DateTime ) {
            $this->timeOut = $timeOut->getTimestamp();
        } elseif ($timeOut===false) {
            $this->timeOut = PHP_INT_MAX; //end of time
        } else {
            $this->timeOut = $timeOut;
        }
    }

    /**
     * @param DateTime|int $periodStart
     * @param DateTime|int $periodEnd
     */
    public function addOverlapFrom($periodStart, $periodEnd) {
        if( $periodStart instanceOf DateTime ) {
            $periodStart = $periodStart->getTimestamp();
        }
        
        if( $periodEnd instanceOf DateTime ) {
            $periodEnd = $periodEnd->getTimestamp();
        }

        $this->totalOverlapDays += $this->calculateOverlap($periodStart, $periodEnd);
        $this->totalOverlapMonths += $this->calculateOverlap($periodStart, $periodEnd, self::UNIT_MONTHS);
    }
    
    /**
     * @param int $ts1
     * @param int $ts2 
     */
    public static function numberOfMonths($ts1, $ts2)
    {
        $days = ceil(($ts2 - $ts1) / (60 * 60 * 24));
        if($days > 0){
            $year1 = date('Y', $ts1);
            $year2 = date('Y', $ts2);

            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);

            return (($year2 - $year1) * 12) + ($month2 - $month1) + 1;
        } else {
            return 0;
        }
    }

    /**
     * @param $periodStart
     * @param $periodEnd
     * @return int
     */
    private function calculateOverlap($periodStart, $periodEnd, $unit=self::UNIT_DAYS)
    {
        if($periodStart >= $this->timeIn && $periodEnd <= $this->timeOut) {
            // The compared time range can be contained within borders of the source time range, so the over lap is the entire compared time range
            switch($unit){
                case self::UNIT_MONTHS:
                    return self::numberOfMonths($periodStart, $periodEnd);
                    break;
                default:
                    return ceil(($periodEnd - $periodStart) / (60 * 60 * 24));
                    break;
            }
        } elseif ($periodStart >= $this->timeIn && $periodStart <= $this->timeOut) {
            // The compared time range starts after or at the source time range but also ends after it because it failed the condition above
            switch($unit){
                case self::UNIT_MONTHS:
                    return self::numberOfMonths($periodStart, $this->timeOut);
                    break;
                default:
                    return ceil(($this->timeOut - $periodStart) / (60 * 60 * 24));
                    break;
            }
        } elseif ($periodEnd >= $this->timeIn && $periodEnd <= $this->timeOut) {
            // The compared time range starts before the source time range and ends before the source end time
            switch($unit){
                case self::UNIT_MONTHS:
                    return self::numberOfMonths($this->timeIn, $periodEnd);
                    break;
                default:
                    return ceil(($periodEnd - $this->timeIn) / (60 * 60 * 24));
                    break;
            }
        } elseif($this->timeIn > $periodStart && $this->timeOut < $periodEnd) {
            // The compared time range is actually wider than the source time range, so the overlap is the entirety of the source range
            switch($unit){
                case self::UNIT_MONTHS:
                    return self::numberOfMonths($this->timeIn, $this->timeOut);
                    break;
                default:
                    return ceil(($this->timeOut - $this->timeIn) / (60 * 60 * 24));
                    break;
            }
        }
        return 0;
    }

    /**
     * @return int
     */
    public function getOverlap($unit=self::UNIT_DAYS) {
        switch($unit){
            case self::UNIT_MONTHS:
                return $this->totalOverlapMonths;
                break;
            default:
                return $this->totalOverlapDays;
                break;
        }
    }
}
