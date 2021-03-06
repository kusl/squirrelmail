<?php
/*
 *  day.php
 *
 *  Copyright (c) 2001 Michal Szczotka <michal@tuxy.org>
 *  Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 *  Displays the day page (day view).
 *
 * $Id$
 */

require_once('calendar_data.php');
require_once('functions.php');
chdir('..');
require_once('../src/validate.php');
require_once('../functions/strings.php');
require_once('../functions/date.php');
require_once('../config/config.php');
require_once('../functions/page_header.php');
require_once('../src/load_prefs.php');
require_once('../functions/html.php');

//displays head of day calendar view
function day_header() {
    global $color, $month, $day, $year, $prev_year, $prev_month, $prev_day,
           $prev_date, $next_month, $next_day, $next_year, $next_date;

    echo html_tag( 'tr', '', '', $color[0] ) . "\n".
                html_tag( 'td', '', 'left' ) .
                    html_tag( 'table', '', '', $color[0], 'width="100%" border="0" cellpadding="2" cellspacing="1"' ) ."\n" .
                        html_tag( 'tr', 
                            html_tag( 'th',
                                "<a href=\"day.php?year=$prev_year&month=$prev_month&day=$prev_day\">&lt;&nbsp;".
                                date_intl('D',$prev_date)."</a>",
                            'left' ) .
                            html_tag( 'th', date_intl( _("l, F j Y"), mktime(0, 0, 0, $month, $day, $year)) ,
                                '', '', 'width="75%"' ) .
                            html_tag( 'th',
                                "<a href=\"day.php?year=$next_year&month=$next_month&day=$next_day\">".
                                date_intl('D',$next_date)."&nbsp;&gt;</a>" ,
                            'right' )
                        );
}

//events for specific day  are inserted into "daily" array
function initialize_events() {
    global $daily_events, $calendardata, $month, $day, $year;

    for ($i=7;$i<23;$i++){
        if ($i<10){
            $evntime = '0' . $i . '00';
        } else {
            $evntime = $i . '00';
            }
        $daily_events[$evntime] = 'empty';
    }

    $cdate = $month . $day . $year;

    if (isset($calendardata[$cdate])){
        while ( $calfoo = each($calendardata[$cdate])){
            $daily_events["$calfoo[key]"] = $calendardata[$cdate][$calfoo['key']];
        }
    }
}

//main loop for displaying daily events
function display_events() {
    global $daily_events, $month, $day, $year, $color;

    ksort($daily_events,SORT_STRING);
    $eo=0;
    while ($calfoo = each($daily_events)){
        if ($eo==0){
            $eo=4;
        } else {
            $eo=0;
        }

        $ehour = substr($calfoo['key'],0,2);
        $eminute = substr($calfoo['key'],2,2);
        if (!is_array($calfoo['value'])){
            echo html_tag( 'tr',
                       html_tag( 'td', $ehour . ':' . $eminute, 'left' ) .
                       html_tag( 'td', '&nbsp;', 'left' ) .
                       html_tag( 'td',
                           "<font size=\"-1\"><a href=\"event_create.php?year=$year&month=$month&day=$day&hour=".substr($calfoo['key'],0,2)."\">".
                           _("ADD") . "</a></font>" ,
                       'center' ) ,
                   '', $color[$eo]);

        } else {
            $calbar=$calfoo['value'];
            if ($calbar['length']!=0){
                $elength = '-'.date('H:i',mktime($ehour,$eminute+$calbar['length'],0,1,1,0));
            } else {
                $elength='';
            }
            echo html_tag( 'tr', '', '', $color[$eo] ) .
                        html_tag( 'td', $ehour . ':' . $eminute . $elength, 'left' ) .
                        html_tag( 'td', '', 'left' ) . '[';
                            echo ($calbar['priority']==1) ? "<font color=\"$color[1]\">$calbar[title]</font>" : "$calbar[title]";
                            echo"] $calbar[message]&nbsp;" .
                        html_tag( 'td',
                            "<font size=\"-1\"><nobr>\n" .
                            "<a href=\"event_edit.php?year=$year&month=$month&day=$day&hour=".substr($calfoo['key'],0,2)."&minute=".substr($calfoo['key'],2,2)."\">".
                            _("EDIT") . "</a>&nbsp;|&nbsp;\n" .
                            "<a href=\"event_delete.php?dyear=$year&dmonth=$month&dday=$day&dhour=".substr($calfoo['key'],0,2)."&dminute=".substr($calfoo['key'],2,2)."&year=$year&month=$month&day=$day\">" .
                            _("DEL") . '</a>' .
                            "</nobr></font>\n" ,
                        'center' );
    }
}


}

if ($month <= 0){
    $month = date( 'm');
}
if ($year <= 0){
    $year = date( 'Y');
}
if ($day <= 0){
    $day = date( 'd');
}

$prev_date = mktime(0, 0, 0, $month , $day - 1, $year);
$next_date = mktime(0, 0, 0, $month , $day + 1, $year);
$prev_day = date ('d',$prev_date);
$prev_month = date ('m',$prev_date);
$prev_year = date ('Y',$prev_date);
$next_day = date ('d',$next_date);
$next_month = date ('m',$next_date);
$next_year = date ('Y',$next_date);

$calself=basename($PHP_SELF);

$daily_events = array();

displayPageHeader($color, 'None');
calendar_header();
readcalendardata();
day_header();
initialize_events();
display_events();
?>
</table></td></tr></table>
</body></html>
