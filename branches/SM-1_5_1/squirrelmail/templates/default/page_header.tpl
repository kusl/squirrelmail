<?php

/**
 * page_header.tpl
 *
 * Template to create the header for each page.
 *
 * @copyright &copy; 1999-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 * @subpackage templates
 */

/* retrieve the template vars */
extract($t);


$current_folder_str = '';
if ( $shortBoxName <> '' && strtolower( $shortBoxName ) <> 'none' ) {
    $current_folder_str .= _("Current Folder") . ": <em>$shortBoxName&nbsp;</em>\n";
} else {
    $current_folder_str .= '&nbsp;';
}

// Define our default link text.
$signout_link_default = _("Sign Out");
$compose_link_default = _("Compose");
$address_link_default = _("Addresses");
$folders_link_default = _("Folders");
$options_link_default = _("Options");
$search_link_default = _("Search");
$help_link_default = _("Help");

/*
 * Create strings to use for links.  If tempalte authors
 * wish to use images instead, they may change the values
 * below to img tags.

 * Example w/ image:
 * $compose_str = '<img src="compose.png" border="0" ' .
 *				  'alt="'.$compose_link_default.'" ' .
 *				  'title="'.$compose_link_default.'" />';
 */

$signout_str = $signout_link_default;
$compose_str = $compose_link_default;
$address_str = $address_link_default;
$folders_str = $folders_link_default;
$options_str = $options_link_default;
$search_str = $search_link_default;
$help_str = $help_link_default;

$compose_link	= makeComposeLink ('src/compose.php?mailbox='.$urlMailbox.'&amp;startMessage='.$startMessage, $compose_str);
$signout_link	= makeInternalLink ('src/signout.php', $signout_str, $frame_top);
$address_link	= makeInternalLink ('src/addressbook.php', $address_str);
$folders_link	= makeInternalLink ('src/folders.php', $folders_str);
$search_link	= makeInternalLink ('src/search.php?mailbox='.$urlMailbox, $search_str);
$options_link	= makeInternalLink ('src/options.php', $options_str);
$help_link		= makeInternalLink ('src/help.php', $help_str);

?>
<body <?php echo $body_tag_js; ?>>
<div id="page_header">
<a name="pagetop"></a>
<!-- Begin Header Navigation Table -->
<table class="table_empty" cellspacing="0">
 <tr>
  <td class="sqm_currentFolder">
   <?php echo $current_folder_str; ?>
  </td>
  <td class="sqm_headerSignout">
   <?php echo $signout_link; ?>
  </td>
 </tr>
 <tr>
  <td class="sqm_topNavigation"<?php echo ($hide_sm_attributions ? ' colspan="2"' : ''); ?>>
   <?php echo $compose_link; ?>&nbsp;&nbsp;
   <?php echo $address_link; ?>&nbsp;&nbsp;
   <?php echo $folders_link; ?>&nbsp;&nbsp;
   <?php echo $options_link; ?>&nbsp;&nbsp;
   <?php echo $search_link; ?>&nbsp;&nbsp;
   <?php echo $help_link; ?>&nbsp;&nbsp;
   <?php do_hook('menuline'); ?>
  </td>
  <?php echo $sm_attribute_str."\n"; ?>
 </tr>
</table>
</div>
<br />
<!-- End Header Navigation Table -->
