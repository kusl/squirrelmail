<?php
/*
 *  event_delete.php
 *
 * Copyright (c) 2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Originally contrubuted by Michal Szczotka <michal@tuxy.org>
 *
 *  Functions to delete a event. 
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


/* get globals */
if (isset($_GET['month'])) {
    $month = $_GET['month'];
}
elseif (isset($_POST['month'])) {
    $month = $_POST['month'];
}
if (isset($_GET['year'])) {
    $year = $_GET['year'];
}
elseif (isset($_POST['year'])) {
    $year = $_POST['year'];
}
if (isset($_GET['day'])) {
    $day = $_GET['day'];
}
elseif (isset($_POST['day'])) {
    $day = $_POST['day'];
}
if (isset($_GET['dyear'])) {
    $dyear = $_GET['dyear'];
}
elseif (isset($_POST['dyear'])) {
    $dyear = $_POST['dyear'];
}
if (isset($_GET['dmonth'])) {
    $dmonth = $_GET['dmonth'];
}
elseif (isset($_POST['dmonth'])) {
    $dmonth = $_POST['dmonth'];
}
if (isset($_GET['dday'])) {
    $dday = $_GET['dday'];
}
elseif (isset($_POST['dday'])) {
    $dday = $_POST['dday'];
}
if (isset($_GET['dhour'])) {
    $dhour = $_GET['dhour'];
}
elseif (isset($_POST['dhour'])) {
    $dhour = $_POST['dhour'];
}
if (isset($_GET['dminute'])) {
    $dminute = $_GET['dminute'];
}
elseif (isset($_POST['dminute'])) {
    $dminute = $_POST['dminute'];
}
if (isset($_POST['confirmed'])) {
    $confirmed = $_POST['confirmed'];
}
/* got 'em */

function confirm_deletion()
{
    global $calself, $dyear, $dmonth, $dday, $dhour, $dminute, $calendardata, $color, $year, $month, $day;

    $tmparray = $calendardata["$dmonth$dday$dyear"]["$dhour$dminute"];

    echo "  <TABLE BORDER=0 CELLPADDING=2 CELLSPACING=1 BGCOLOR=\"$color[0]\">\n".
         "    <TR><TH COLSPAN=2 BGCOLOR=\"$color[4]\">\n".
         _("Do you really want to delete this event?") . "<br></th>\n".
         "    <TR><TD ALIGN=RIGHT BGCOLOR=\"$color[4]\">" . _("Date:") . "</TD>\n".
         "    <TD ALIGN=LEFT BGCOLOR=\"$color[4]\">$dmonth/$dday/$dyear</TD></TR>\n".
         "    <TR><TD ALIGN=RIGHT BGCOLOR=\"$color[4]\">" . _("Time:") . "</TD>\n".
         "    <TD ALIGN=LEFT BGCOLOR=\"$color[4]\">$dhour:$dminute</TD></TR>\n".
         "    <TR><TD ALIGN=RIGHT BGCOLOR=\"$color[4]\">" . _("Title:") . "</TD>\n".
         "    <TD ALIGN=LEFT BGCOLOR=\"$color[4]\">$tmparray[title]</TD></TR>\n".
         "    <TR><TD ALIGN=RIGHT BGCOLOR=\"$color[4]\">" . _("Message:") . "</TD>\n".
         "    <TD ALIGN=LEFT BGCOLOR=\"$color[4]\">$tmparray[message]</TD></TR>\n".
         "    <TR><TD ALIGN=RIGHT BGCOLOR=\"$color[4]\">\n".
         "    <FORM NAME=\"delevent\" METHOD=POST ACTION=\"$calself\">\n".
         "       <INPUT TYPE=HIDDEN NAME=\"dyear\" VALUE=\"$dyear\">\n".
         "       <INPUT TYPE=HIDDEN NAME=\"dmonth\" VALUE=\"$dmonth\">\n".
         "       <INPUT TYPE=HIDDEN NAME=\"dday\" VALUE=\"$dday\">\n".
         "       <INPUT TYPE=HIDDEN NAME=\"year\" VALUE=\"$year\">\n".
         "       <INPUT TYPE=HIDDEN NAME=\"month\" VALUE=\"$month\">\n".
         "       <INPUT TYPE=HIDDEN NAME=\"day\" VALUE=\"$day\">\n".
         "       <INPUT TYPE=HIDDEN NAME=\"dhour\" VALUE=\"$dhour\">\n".
         "       <INPUT TYPE=HIDDEN NAME=\"dminute\" VALUE=\"$dminute\">\n".
         "       <INPUT TYPE=HIDDEN NAME=\"confirmed\" VALUE=\"yes\">\n".
         '       <INPUT TYPE=SUBMIT VALUE="' . _("Yes") . "\">\n".
         "    </FORM>\n".
         "    </TD><TD ALIGN=LEFT BGCOLOR=\"$color[4]\">\n".
         "    <FORM NAME=\"nodelevent\" METHOD=POST ACTION=\"day.php\">\n".
         "       <INPUT TYPE=HIDDEN NAME=\"year\" VALUE=\"$year\">\n".
         "       <INPUT TYPE=HIDDEN NAME=\"month\" VALUE=\"$month\">\n".
         "       <INPUT TYPE=HIDDEN NAME=\"day\" VALUE=\"$day\">\n".
         '       <INPUT TYPE=SUBMIT VALUE="' . _("No") . "\">\n".
         "    </FORM>\n".
         "    </TD></TR>\n".
         "  </TABLE>\n";


}

if ($month <= 0){
    $month = date( 'm' );
}
if ($year <= 0){
    $year = date( 'Y' );
}
if ($day <= 0){
    $day = date( 'd' );
}

$calself=basename($PHP_SELF);

displayPageHeader($color, 'None');
//load calendar menu
calendar_header();

echo "<TR BGCOLOR=\"$color[0]\"><TD>".
     "<TABLE WIDTH=100% BORDER=0 CELLPADDING=2 CELLSPACING=1 BGCOLOR=\"$color[0]\">".
     '<tr><td>'.
     date_intl( _("l, F j Y"), mktime(0, 0, 0, $month, $day, $year));
if (isset($dyear) && isset($dmonth) && isset($dday) && isset($dhour) && isset($dminute)){
    if (isset($confirmed)){
        delete_event("$dmonth$dday$dyear", "$dhour$dminute");
        echo '<br><br>' . _("Event deleted!") . "<BR>\n";
        echo "<A HREF=\"day.php?year=$year&month=$month&day=$day\">" .
          _("Day View") . "</A>\n";
    } else {
        readcalendardata();
        confirm_deletion();
    }
} else {
    echo '<br>' . _("Nothing to delete!");
}

?>
</table></td></tr></table>
</body></html>
