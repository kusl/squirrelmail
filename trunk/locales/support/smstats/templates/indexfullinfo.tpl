<?php
ob_start("ob_gzhandler");
$title="Translation statistics";
$location="/ KDE Internationalization Home / GUI Statistics";
include("header.php");
?>


<h2>Compact translation statistics for {TXT_REV} branch from {TXT_DATE}</h2>

<table cellpadding="1" cellspacing="0" border="0" width="100%" bgcolor="#8b898b"><tr><td>
<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#ececec"><tr><td>
<table cellpadding="2" cellspacing="0" border="0" width="100%">
<tr>
  <td><font size="2"><b>
  
    <a class="package" href="../index.php">top</a> <font color="#ff0000">&gt;</font>
      {TXT_REV} <font color="#ff0000">&gt;</font>
      
  </b></font></td>
  <td align="center"><font size="2">
    <a href="index.php">info by team</a> | 
    <a href="packages.php">info by package</a> |
    full info page |
    <a href="top.php">top</a> |
    <a href="essential.php">essential</a> |
    <a href="partial/index.php">partial</a> |
    <a href="general.php">general info</a>
  </font></td>
  <td align="right"><font size="2">
    last update: <b>{TXT_RUNDATE}</b> &nbsp;
  </font></td>
</tr>
</table>
</td></tr></table>
</td></tr></table>
                        
<img src="../img/px.png" height="5" width="1"><br>
                        
<table cellspacing="0" cellpadding="0" border="0" align="center" bgcolor="#8b898b" width="100%"><tr><td>
<table cellspacing="1" cellpadding="2" border="0" width="100%">

{TABLE}

</table>
</td></tr>
</table>

<font size="2">
<br>
<b>Note:</b><br>
&nbsp;&nbsp;<b>1.</b> Numbers in cells represent translated per total number of messages ratio given in percents.
Only existing PO files count for total number of messages.<br>
&nbsp;&nbsp;<b>2.</b> A cell with red background show that coresponding team have an
package which contain at least one bad PO file.<br>

<br>
</font>


<img src="../img/px.png" height="5" width="1"><br>
<table cellpadding="1" cellspacing="0" border="0" width="100%" bgcolor="#8b898b">
<tr><td>
<table cellpadding="2" cellspacing="0" border="0" width="100%" bgcolor="#ececec">
<tr align="center"><td><font size="2">

<a href="../index.php">index</a> |
<a href="../about.php">about</a> |
<a href="../help.php">help</a>

</font></td></tr></table>
</td></tr></table>

<?php include("footer.php"); ob_end_flush(); ?>
