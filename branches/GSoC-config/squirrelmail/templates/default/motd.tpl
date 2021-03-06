<?php
/**
 * motd.tpl
 *
 * Tempalte for display Message of the day
 * 
 * Variables available in this template:
 *      $motd - string containing the MOTD to be displayed
 *
 * @copyright &copy; 1999-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 * @subpackage templates
 */
 
// Get variables from template
extract($t);
?>
<!-- Begin MOTD -->
<div class="sqm_motdWrapper">
 <table class="sqm_motd" cellspacing="3">
  <tr>
   <td>
    <?php echo $motd; ?>
   </td>
  </tr>
 </table>
</div>
<!-- End MOTD -->
