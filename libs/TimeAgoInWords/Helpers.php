<?php

/**
 * Nette Framework Extras
 *
 * This source file is subject to the New BSD License.
 *
 * For more information please see http://addons.nette.org
 *
 * @copyright  Copyright (c) 2008, 2009 David Grudl
 * @license    New BSD License
 * @link       http://addons.nette.org
 * @package    Nette Extras
 */

/**
 * My helpers collection.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2008, 2009 David Grudl
 * @package    Nette Extras
 */
class Helpers
{
    /**
     * Czech helper time ago in words.
     * @param  int
     * @return string
     */
    public static function timeAgoInWords($time)
    {
        if (!$time) {
            return FALSE;
        } elseif (is_numeric($time)) {
            $time = (int) $time;
        } elseif ($time instanceof \DateTime) {
            $time = $time->format('U');
        } else {
            $time = strtotime($time);
        }

        $delta = time() - $time;

        if ($delta < 0) {
            $delta = round(abs($delta) / 60);
            if ($delta == 0) return ['inAMinute']; //'za okamžik';
            if ($delta == 1) return ['inMinutes', 'time' => 1]; //'za minutu';
            if ($delta < 45) return ['inMinutes', 'time' => $delta]; //'za ' . $delta . ' ' . self::plural($delta, 'minuta', 'minuty', 'minut');
            if ($delta < 90) return ['inHours', 'time' => 1]; //'za hodinu';
            if ($delta < 1440) return ['inHours', 'time' => round($delta / 60)]; //'za ' . round($delta / 60) . ' ' . self::plural(round($delta / 60), 'hodina', 'hodiny', 'hodin');
            if ($delta < 2880) return ['tomorrow']; //'zítra';
            if ($delta < 43200) return ['inDays', 'time' => round($delta / 1440)]; //'za ' . round($delta / 1440) . ' ' . self::plural(round($delta / 1440), 'den', 'dny', 'dní');
            if ($delta < 86400) return ['inMonths', 'time' => 1]; //'za měsíc';
            if ($delta < 525960) return ['inMonths', 'time' => round($delta / 43200)]; //'za ' . round($delta / 43200) . ' ' . self::plural(round($delta / 43200), 'měsíc', 'měsíce', 'měsíců');
            if ($delta < 1051920) return ['inYears', 'time' => 1]; //'za rok';
            return ['inYears', 'time' => round($delta / 525960)]; //'za ' . round($delta / 525960) . ' ' . self::plural(round($delta / 525960), 'rok', 'roky', 'let');
        }

        $delta = round($delta / 60);
        if ($delta == 0) return ['aMinuteAgo']; //'před okamžikem';
        if ($delta == 1) return ['minutesAgo', 'time' => 1]; //'před minutou';
        if ($delta < 45) return ['minutesAgo', 'time' => $delta]; //"před $delta minutami";
        if ($delta < 90) return ['hoursAgo', 'time' => 1]; //'před hodinou';
        if ($delta < 1440) return ['hoursAgo', 'time' => round($delta / 60)]; //'před ' . round($delta / 60) . ' hodinami';
        if ($delta < 2880) return ['yesterday']; //'včera';
        if ($delta < 43200) return ['daysAgo', 'time' => round($delta / 1440)]; //'před ' . round($delta / 1440) . ' dny';
        if ($delta < 86400) return ['monthsAgo', 'time' => 1]; //'před měsícem';
        if ($delta < 525960) return ['monthsAgo', 'time' => round($delta / 43200)]; //'před ' . round($delta / 43200) . ' měsíci';
        if ($delta < 1051920) return ['oneYearAgo', 'time' => 1]; //'před rokem';
        return ['yearsAgo', 'time' => round($delta / 525960)]; //'před ' . round($delta / 525960) . ' lety';
    }


    /**
     * Plural: three forms, special cases for 1 and 2, 3, 4.
     * (Slavic family: Slovak, Czech)
     * @param  int
     * @return mixed
     */
    private static function plural($n)
    {
        $args = func_get_args();
        return $args[($n == 1) ? 1 : (($n >= 2 && $n <= 4) ? 2 : 3)];
    }
}