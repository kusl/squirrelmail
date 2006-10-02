<?php

/**
 * Default SquirrelMail configuration file
 *
 * BEFORE EDITING THIS FILE!
 *
 * Don't edit this file directly.  Copy it to config.php before you
 * edit it.  However, it is best to use the configuration script
 * conf.pl if at all possible.  That is the easiest and cleanest way
 * to configure.
 *
 * Note on SECURITY: some options require putting a password in this file.
 * Please make sure that you adapt its permissions appropriately to avoid
 * passwords being leaked to e.g. other system users. Take extra care when
 * the webserver is shared with untrusted users.
 *
 * @copyright &copy; 2000-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 * @subpackage config
 */

/* Do not change this value. */
global $version;
global $config_version;
$config_version = '1.4.0';

/*** Organization preferences ***/
/**
 * Organization's name
 * @global string $org_name
 */
$org_name = "SquirrelMail";

/**
 * Organization's logo picture (blank if none)
 * @global string $org_logo
 */
$org_logo = SM_PATH . 'images/sm_logo.png';

/**
 * The width of the logo (0 for default)
 * @global string $org_logo_width
 */
$org_logo_width = '308';

/**
 * The height of the logo (0 for default)
 * @global string $org_logo_height
 */
$org_logo_height = '111';

/**
 * Webmail Title
 *
 * This is the web page title that appears at the top of the browser window.
 * @global string $org_title
 */
$org_title = "SquirrelMail";

/**
 * Signout page
 *
 * Rather than going to the signout.php page (which only allows you
 * to sign back in), setting signout_page allows you to sign the user
 * out and then redirect to whatever page you want. For instance,
 * the following would return the user to your home page:
 *   $signout_page = '/';
 * Set to the empty string to continue to use the default signout page.
 * @global string $signout_page
 */
$signout_page = '';

/**
 * Top frame
 *
 * By default SquirrelMail takes up the whole browser window,
 * this allows you to embed it within sites using frames. Set
 * this to the frame you want it to stay in.
 * @global string $frame_top
 */
$frame_top = '_top';

/**
 * Provider name
 *
 * Here you can set name of the link displayed on the right side of main page.
 *
 * Link will be displayed only if you have $hide_sm_attributions
 * option set to true.
 * @global string $provider_name
 */
$provider_name = 'SquirrelMail';

/**
 * Provider URI
 *
 * Here you can set URL of the link displayed on the right side of main page.
 * When empty, this refers to the SquirrelMail About page.
 * Link will be displayed only if you have $hide_sm_attributions
 * option set to true.
 * @global string $provider_uri
 */
$provider_uri = '';

/*** Server Settings ***/
/**
 * Default Domain
 *
 * The domain part of local email addresses.
 *   This is for all messages sent out from this server.
 *   Reply address is generated by $username@$domain
 * Example: In bob@example.com, example.com is the domain.
 * @global string $domain
 */
$domain = 'example.com';

/**
 * Time offset inversion
 *
 * If you are running on a machine that doesn't have the tm_gmtoff
 * value in your time structure and if you are in a time zone that
 * has a negative offset, you need to set this value to 1. This is
 * typically people in the US that are running Solaris 7.
 * @global bool $invert_time
 */
$invert_time = false;

/**
 * Default send transport
 *
 * What should be used when sending email.
 * If it is set to false, SquirrelMail will use SMTP server settings.
 * If it is set to true, SquirrelMail will use program defined in
 * $sendmail_path
 * @global bool $useSendmail
 */
$useSendmail = false;

/**
 * Your SMTP server (usually the same as the IMAP server).
 * @global string $smtpServerAddress
 */
$smtpServerAddress = 'localhost';
/**
 * Your SMTP port number (usually 25).
 * @global integer $smtpPort
 */
$smtpPort = 25;

/**
 * SquirrelMail header encryption
 *
 * Encryption key allows to hide SquirrelMail Received: headers
 * in outbound messages. Interface uses encryption key to encode
 * username, remote address and proxied address, then stores encoded
 * information in X-Squirrel-* headers.
 *
 * Warning: used encryption function is not bulletproof. When used
 * with static encryption keys, it provides only minimal security
 * measures and information can be decoded quickly.
 *
 * Encoded information can be decoded with decrypt_headers.php script
 * from SquirrelMail contrib/ directory.
 * @global string $encode_header_key
 * @since 1.5.1 and 1.4.5
 */
$encode_header_key = '';

/**
 * Path to Sendmail
 *
 * Program that should be used when sending email. SquirrelMail expects that
 * this program will follow options used by original sendmail
 * (http://www.sendmail.org). Support of -f argument is required.
 * @global string $sendmail_path
 */
$sendmail_path = '/usr/sbin/sendmail';

/**
 * Extra sendmail command arguments.
 *
 * Sets additional sendmail command arguments. Make sure that arguments are
 * supported by your sendmail program. -f argument is added automatically by
 * SquirrelMail scripts. Variable defaults to standard /usr/sbin/sendmail
 * arguments. If you use qmail-inject, nbsmtp or any other sendmail wrapper,
 * which does not support -t and -i arguments, set variable to empty string
 * or use arguments suitable for your mailer.
 * @global string $sendmail_args
 * @since 1.5.1 and 1.4.8
 */
$sendmail_args = '-i -t';

/**
 * IMAP server address
 *
 * The dns name (or IP address) for your imap server.
 * @global string $imapServerAddress
 */
$imapServerAddress = 'localhost';

/**
 * IMAP server port
 *
 * Port used by your imap server. (Usually 143)
 * @global integer $imapPort
 */
$imapPort = 143;

/**
 * IMAP server type
 *
 * The type of IMAP server you are running.
 * Valid type are the following (case is important):
 *   bincimap
 *   courier
 *   cyrus
 *   dovecot
 *   exchange
 *   hmailserver
 *   macosx
 *   mercury32
 *   uw
 *   other
 *
 * Please note that this changes only some of server settings.
 *
 * In order to set everything correctly, you need to adjust several
 * SquirrelMail options. These options are listed in doc/presets.txt
 * @global string $imap_server_type
 */
$imap_server_type = 'other';

/**
 * Secure IMAP connection controls
 *
 * 0 - use plain text connection,
 * 1 - use imaps (adds tls:// prefix to hostname),
 * 2 - use IMAP STARTTLS extension (rfc2595).
 *
 * Was boolean before 1.5.1.
 * @global integer $use_imap_tls
 * @since 1.4.0
 */
$use_imap_tls = 0;

/**
 * Secure SMTP connection controls
 *
 * 0 - use plain text connection,
 * 1 - use ssmtp (adds tls:// prefix to hostname),
 * 2 - use SMTP STARTTLS extension (rfc2487).
 *
 * Was boolean before 1.5.1.
 * @global integer $use_smtp_tls
 * @since 1.4.0
 */
$use_smtp_tls = 0;

/**
 * SMTP authentication mechanism
 *
 * auth_mech can be either 'none', 'login','plain', 'cram-md5', or 'digest-md5'
 * @global string $smtp_auth_mech
 */
$smtp_auth_mech = 'none';

/**
 * Custom SMTP authentication username
 *
 * IMAP username is used if variable is set to empty string.
 * Variable is included in main configuration file only from 1.5.2 version.
 * Older versions stored it in config_local.php.
 * @global string $smtp_sitewide_user
 * @since 1.5.0
 */
$smtp_sitewide_user = '';

/**
 * Custom SMTP authentication password
 *
 * IMAP password is used if $smtp_sitewide_user global is set to empty string.
 * Variable is included in main configuration file only from 1.5.2 version.
 * Older versions stored it in config_local.php.
 * @global string $smtp_sitewide_pass
 * @since 1.5.0
 */
$smtp_sitewide_pass = '';

/**
 * IMAP authentication mechanism
 *
 * auth_mech can be either 'login','plain', 'cram-md5', or 'digest-md5'
 * @global string $imap_auth_mech
 */
$imap_auth_mech = 'login';

/**
 * IMAP folder delimiter
 *
 * This is the delimiter that your IMAP server uses to distinguish between
 * folders.  For example, Cyrus uses '.' as the delimiter and a complete
 * folder would look like 'INBOX.Friends.Bob', while UW uses '/' and would
 * look like 'INBOX/Friends/Bob'.  Normally this should be left at 'detect'
 * but if you are sure you know what delimiter your server uses, you can
 * specify it here.
 *
 * To have it autodetect the delimiter, set it to 'detect'.
 * @global string $optional_delimiter
 */
$optional_delimiter = 'detect';

/**
 * POP before SMTP setting
 *
 * Do you wish to use POP3 before SMTP?  Your server must
 * support this in order for SquirrelMail to work with it.
 * @global bool $pop_before_smtp
 */
$pop_before_smtp = false;


/*** Folder Settings ***/
/**
 * Default IMAP folder prefix
 *
 * Many servers store mail in your home directory. With this, they
 * store them in a subdirectory: mail/ or Mail/, etc. If your server
 * does this, please set this to what the default mail folder should
 * be. This is still a user preference, so they can change it if it
 * is different for each user.
 *
 * Example:
 *     $default_folder_prefix = 'mail/';
 *        -- or --
 *     $default_folder_prefix = 'Mail/folders/';
 *
 * If you do not use this, set it to the empty string.
 * @global string $default_folder_prefix
 */
$default_folder_prefix = '';

/**
 * User level prefix control
 *
 * If you do not wish to give them the option to change this, set it
 * to false. Otherwise, if it is true, they can change the folder prefix
 * to be anything.
 * @global bool $show_prefix_option
 */
$show_prefix_option = false;

/**
 * The following are related to deleting messages.
 *   $default_move_to_trash
 *      If this is set to 'true', when 'delete' is pressed, it
 *      will attempt to move the selected messages to the folder
 *      named $trash_folder. If it's set to 'false', we won't even
 *      attempt to move the messages, just delete them.
 *   $default_move_to_sent
 *      If this is set to 'true', sent messages will be stored in
 *      $sent_folder by default.
 *   $default_save_as_draft
 *      If this is set to 'true', users are able to use $draft_folder
 *      to store their unfinished messages.
 *   $trash_folder
 *      This is the path to the default trash folder. For Cyrus
 *      IMAP, it would be 'INBOX.Trash', but for UW it would be
 *      'Trash'. We need the full path name here.
 *   $draft_folder
 *      This is the patch to where Draft messages will be stored.
 *   $auto_expunge
 *      If this is true, when a message is moved or copied, the
 *      source mailbox will get expunged, removing all messages
 *      marked 'Deleted'.
 *   $sent_folder
 *      This is the path to where Sent messages will be stored.
 *   $delete_folder
 *      If this is true, when a folder is deleted then it will
 *      not get moved into the Trash folder.
 * @global bool $default_move_to_trash
 * @global bool $default_move_to_sent
 * @global bool $default_save_as_draft
 * @global string $trash_folder
 * @global string $sent_folder
 * @global string $draft_folder
 * @global bool $auto_expunge
 * @global bool $delete_folder
 */
$default_move_to_trash = true;
$default_move_to_sent  = true;
$default_save_as_draft = true;
$trash_folder = 'INBOX.Trash';
$sent_folder  = 'INBOX.Sent';
$draft_folder = 'INBOX.Drafts';
$auto_expunge = true;
$delete_folder = false;

/**
 * Special Folder Color Control
 *
 * Whether or not to use a special color for special folders. If not,
 * special folders will be the same color as the other folders.
 * @global bool $use_special_folder_color
 */
$use_special_folder_color = true;

/**
 * Create Special Folders Control
 *
 * Should I create the Sent and Trash folders automatically for
 * a new user that doesn't already have them created?
 * @global bool $auto_create_special
 */
$auto_create_special = true;

/**
 * List Special Folders First Control
 *
 * Whether or not to list the special folders first (true/false).
 * @global bool $list_special_folders_first
 */
$list_special_folders_first = true;

/**
 * Subfolder Layout Control
 *
 * Are all your folders subfolders of INBOX (i.e. cyrus IMAP server).
 * If you are unsure, set it to false.
 * @global bool $default_sub_of_inbox
 */
$default_sub_of_inbox = true;

/**
 * Subfolder Format Control
 *
 * Some IMAP daemons (UW) handle folders weird. They only allow a
 * folder to contain either messages or other folders, not both at
 * the same time. This option controls whether or not to display an
 * option during folder creation. The option toggles which type of
 * folder it should be.
 *
 * If this option confuses you, just set it to 'true'. You can not hurt
 * anything if it's true, but some servers will respond weird if it's
 * false. (Cyrus works fine whether it's true OR false).
 * @global bool $show_contain_subfolders_option
 */
$show_contain_subfolders_option = false;

/**
 * These next two options set the defaults for the way that the
 * users see their folder list.
 *   $default_unseen_notify
 *       Specifies whether or not the users will see the number of
 *       unseen in each folder by default and also which folders to
 *       do this to. Valid values are: 1=none, 2=inbox, 3=all.
 *   $default_unseen_type
 *       Specifies the type of notification to give the users by
 *       default. Valid choice are: 1=(4), 2=(4,25).
 * @global integer $default_unseen_notify
 * @global integer $default_unseen_type
 */
$default_unseen_notify = 2;
$default_unseen_type   = 1;

/**
 * NoSelect Fix Control
 *
 * This enables the no select fix for Cyrus when subfolders
 * exist but parent folders do not
 * @global bool $noselect_fix_enable
 */
$noselect_fix_enable = false;

/*** General options ***/
/**
 * Path to the data/ directory
 *
 *   You need to create this directory yourself (see INSTALL).
 *
 *   It is a possible security hole to have a writable directory
 *   under the web server's root directory (ex: /home/httpd/html).
 *   The path name can be absolute or relative (to the config directory).
 *   If path is relative, it must use SM_PATH constant.
 *   Here are two examples:
 *
 * Absolute:
 *   $data_dir = '/var/local/squirrelmail/data/';
 *
 * Relative (to main SM directory):
 *   $data_dir = SM_PATH . 'data/';
 *   (NOT recommended: you need to secure apache to make sure these
 *   files are not world readable)
 *
 * @global string $data_dir
 */
$data_dir = '/var/local/squirrelmail/data/';

/**
 * Attachments directory
 *
 * Path to directory used for storing attachments while a mail is
 * being sent. There are a few security considerations regarding
 * this directory:
 *    + It should have the permission 733 (rwx-wx-wx) to make it
 *      impossible for a random person with access to the webserver to
 *      list files in this directory. Confidential data might be laying
 *      around there.
 *    + Since the webserver is not able to list the files in the content
 *      is also impossible for the webserver to delete files lying around
 *      there for too long. You should have some script that deletes
 *      left over temp files.
 *    + It should probably be another directory than data_dir.
 * @global string $attachment_dir
 */
$attachment_dir = '/var/local/squirrelmail/attach/';

/**
 * Hash level used for data directory.
 *
 * This option allows spliting file based SquirrelMail user
 * data storage directory into several subfolders. Number from
 * 0 to 4 allows allows having up to four subfolder levels.
 *
 * Hashing should speed up directory access if you have big number
 * of users (500 and more).
 * @global integer $dir_hash_level
 */
$dir_hash_level = 0;

/**
 * Default Size of Folder List
 *
 * This is the default size of the folder list. Default
 * is 150, but you can set it to whatever you wish.
 * @global string $default_left_size
 */
$default_left_size = '150';

/**
 * Username Case Control
 *
 * Some IMAP servers allow a username (like 'bob') to log in if they use
 * uppercase in their name (like 'Bob' or 'BOB'). This creates extra
 * preference files.  Toggling this option to true will transparently
 * change all usernames to lowercase.
 * @global bool $force_username_lowercase
 */
$force_username_lowercase = false;

/**
 * Email Priority Control
 *
 * This option enables use of email priority flags by end users.
 * @global bool $default_use_priority
 */
$default_use_priority = true;

/**
 * SquirrelMail Attributions Control
 *
 * This option disables display of "created by SquirrelMail developers"
 * strings and provider link
 * @global bool $hide_sm_attributions
 * @since 1.2.0
 */
$hide_sm_attributions = false;

/**
 * Delivery Receipts Control
 *
 * This option enables use of read/delivery receipts by end users.
 * @global bool $default_use_mdn
 */
$default_use_mdn = true;

/**
 * Identity Controls
 *
 * If you don't want to allow users to change their email address
 * then you can set $edit_identity to false, if you want them to
 * not be able to change their full name too then set $edit_name
 * to false as well. $edit_name has no effect unless $edit_identity
 * is false;
 * @global bool $edit_identity
 * @global bool $edit_name
 */
$edit_identity = true;
$edit_name = true;

/**
 * SquirrelMail adds username information to every sent email.
 * It is done in order to prevent possible sender forging when
 * end users are allowed to change their email and name
 * information.
 *
 * You can disable this header, if you think that it violates
 * user's privacy or security. Please note, that setting will
 * work only when users are not allowed to change their identity.
 *
 * See SquirrelMail bug tracker #847107 for more details about it.
 * @global bool $hide_auth_header
 * @since 1.5.1 and 1.4.5
 */
$hide_auth_header = false;

/**
 * Server Side Threading Control
 *
 * Set it to true, if you want to disable server side thread 
 * sorting options. Your IMAP server must support the THREAD 
 * extension for this to have any effect.
 * 
 * Older SquirrelMail versions used $allow_thread_sort option.
 * @global bool $disable_thread_sort
 * @since 1.5.1
 */
$disable_thread_sort = false;

/**
 * Server Side Sorting Control
 *
 * Set it to true, if you want to disable server side sorting 
 * and use SM client side sorting instead (client side sorting 
 * can be slow). Your IMAP server must support the SORT extension 
 * for this to have any effect.
 * 
 * Older SquirrelMail versions used $allow_server_sort option.
 * @global bool $disable_server_sort
 * @since 1.5.1
 */
$disable_server_sort = false;

/**
 * IMAP Charset Use Control
 *
 * This option allows you to choose if SM uses charset search
 * Your imap server should support SEARCH CHARSET command for
 * this to work.
 * @global bool $allow_charset_search
 */
$allow_charset_search = true;

/**
 * Search functions control
 *
 * This option allows you to control the use of advanced search form.
 * Set to 0 to enable basic search only, 1 to enable advanced search only
 * or 2 to enable both.
 * @global integer $allow_advanced_search
 */
$allow_advanced_search = 0;

/**
 * PHP session name.
 *
 * Leave this alone unless you know what you are doing.
 * @global string $session_name
 */
$session_name = 'SQMSESSID';


/**
 * Themes
 *   You can define your own theme and put it in this directory.
 *   You must call it as the example below. You can name the theme
 *   whatever you want. For an example of a theme, see the ones
 *   included in the config directory.
 *
 * To add a new theme to the options that users can choose from, just
 * add a new number to the array at the bottom, and follow the pattern.
 *
 * $theme_default sets theme that will be used by default
 * used by default.
 * @global integer $theme_default
 * @global string $theme_css
 */
$theme_default = 0;

/**
 * Listing of installed themes
 * @global array $theme
 */
$theme[0]['PATH'] = SM_PATH . 'themes/default_theme.php';
$theme[0]['NAME'] = 'Default';

$theme[1]['PATH'] = SM_PATH . 'themes/plain_blue_theme.php';
$theme[1]['NAME'] = 'Plain Blue';

$theme[2]['PATH'] = SM_PATH . 'themes/sandstorm_theme.php';
$theme[2]['NAME'] = 'Sand Storm';

$theme[3]['PATH'] = SM_PATH . 'themes/deepocean_theme.php';
$theme[3]['NAME'] = 'Deep Ocean';

$theme[4]['PATH'] = SM_PATH . 'themes/slashdot_theme.php';
$theme[4]['NAME'] = 'Slashdot';

$theme[5]['PATH'] = SM_PATH . 'themes/purple_theme.php';
$theme[5]['NAME'] = 'Purple';

$theme[6]['PATH'] = SM_PATH . 'themes/forest_theme.php';
$theme[6]['NAME'] = 'Forest';

$theme[7]['PATH'] = SM_PATH . 'themes/ice_theme.php';
$theme[7]['NAME'] = 'Ice';

$theme[8]['PATH'] = SM_PATH . 'themes/seaspray_theme.php';
$theme[8]['NAME'] = 'Sea Spray';

$theme[9]['PATH'] = SM_PATH . 'themes/bluesteel_theme.php';
$theme[9]['NAME'] = 'Blue Steel';

$theme[10]['PATH'] = SM_PATH . 'themes/dark_grey_theme.php';
$theme[10]['NAME'] = 'Dark Grey';

$theme[11]['PATH'] = SM_PATH . 'themes/high_contrast_theme.php';
$theme[11]['NAME'] = 'High Contrast';

$theme[12]['PATH'] = SM_PATH . 'themes/black_bean_burrito_theme.php';
$theme[12]['NAME'] = 'Black Bean Burrito';

$theme[13]['PATH'] = SM_PATH . 'themes/servery_theme.php';
$theme[13]['NAME'] = 'Servery';

$theme[14]['PATH'] = SM_PATH . 'themes/maize_theme.php';
$theme[14]['NAME'] = 'Maize';

$theme[15]['PATH'] = SM_PATH . 'themes/bluesnews_theme.php';
$theme[15]['NAME'] = 'BluesNews';

$theme[16]['PATH'] = SM_PATH . 'themes/deepocean2_theme.php';
$theme[16]['NAME'] = 'Deep Ocean 2';

$theme[17]['PATH'] = SM_PATH . 'themes/blue_grey_theme.php';
$theme[17]['NAME'] = 'Blue Grey';

$theme[18]['PATH'] = SM_PATH . 'themes/dompie_theme.php';
$theme[18]['NAME'] = 'Dompie';

$theme[19]['PATH'] = SM_PATH . 'themes/methodical_theme.php';
$theme[19]['NAME'] = 'Methodical';

$theme[20]['PATH'] = SM_PATH . 'themes/greenhouse_effect.php';
$theme[20]['NAME'] = 'Greenhouse Effect (Changes)';

$theme[21]['PATH'] = SM_PATH . 'themes/in_the_pink.php';
$theme[21]['NAME'] = 'In The Pink (Changes)';

$theme[22]['PATH'] = SM_PATH . 'themes/kind_of_blue.php';
$theme[22]['NAME'] = 'Kind of Blue (Changes)';

$theme[23]['PATH'] = SM_PATH . 'themes/monostochastic.php';
$theme[23]['NAME'] = 'Monostochastic (Changes)';

$theme[24]['PATH'] = SM_PATH . 'themes/shades_of_grey.php';
$theme[24]['NAME'] = 'Shades of Grey (Changes)';

$theme[25]['PATH'] = SM_PATH . 'themes/spice_of_life.php';
$theme[25]['NAME'] = 'Spice of Life (Changes)';

$theme[26]['PATH'] = SM_PATH . 'themes/spice_of_life_lite.php';
$theme[26]['NAME'] = 'Spice of Life - Lite (Changes)';

$theme[27]['PATH'] = SM_PATH . 'themes/spice_of_life_dark.php';
$theme[27]['NAME'] = 'Spice of Life - Dark (Changes)';

$theme[28]['PATH'] = SM_PATH . 'themes/christmas.php';
$theme[28]['NAME'] = 'Holiday - Christmas';

$theme[29]['PATH'] = SM_PATH . 'themes/darkness.php';
$theme[29]['NAME'] = 'Darkness (Changes)';

$theme[30]['PATH'] = SM_PATH . 'themes/random.php';
$theme[30]['NAME'] = 'Random (Changes every login)';

$theme[31]['PATH'] = SM_PATH . 'themes/midnight.php';
$theme[31]['NAME'] = 'Midnight';

$theme[32]['PATH'] = SM_PATH . 'themes/alien_glow.php';
$theme[32]['NAME'] = 'Alien Glow';

$theme[33]['PATH'] = SM_PATH . 'themes/dark_green.php';
$theme[33]['NAME'] = 'Dark Green';

$theme[34]['PATH'] = SM_PATH . 'themes/penguin.php';
$theme[34]['NAME'] = 'Penguin';

$theme[35]['PATH'] = SM_PATH . 'themes/minimal_bw.php';
$theme[35]['NAME'] = 'Minimal BW';

$theme[36]['PATH'] = SM_PATH . 'themes/redmond.php';
$theme[36]['NAME'] = 'Redmond';

$theme[37]['PATH'] = SM_PATH . 'themes/netstyle_theme.php';
$theme[37]['NAME'] = 'Net Style';

$theme[38]['PATH'] = SM_PATH . 'themes/silver_steel_theme.php';
$theme[38]['NAME'] = 'Silver Steel';

$theme[39]['PATH'] = SM_PATH . 'themes/simple_green_theme.php';
$theme[39]['NAME'] = 'Simple Green';

$theme[40]['PATH'] = SM_PATH . 'themes/wood_theme.php';
$theme[40]['NAME'] = 'Wood';

$theme[41]['PATH'] = SM_PATH . 'themes/bluesome.php';
$theme[41]['NAME'] = 'Bluesome';

$theme[42]['PATH'] = SM_PATH . 'themes/simple_green2.php';
$theme[42]['NAME'] = 'Simple Green 2';

$theme[43]['PATH'] = SM_PATH . 'themes/simple_purple.php';
$theme[43]['NAME'] = 'Simple Purple';

$theme[44]['PATH'] = SM_PATH . 'themes/autumn.php';
$theme[44]['NAME'] = 'Autumn';

$theme[45]['PATH'] = SM_PATH . 'themes/autumn2.php';
$theme[45]['NAME'] = 'Autumn 2';

$theme[46]['PATH'] = SM_PATH . 'themes/blue_on_blue.php';
$theme[46]['NAME'] = 'Blue on Blue';

$theme[47]['PATH'] = SM_PATH . 'themes/classic_blue.php';
$theme[47]['NAME'] = 'Classic Blue';

$theme[48]['PATH'] = SM_PATH . 'themes/classic_blue2.php';
$theme[48]['NAME'] = 'Classic Blue 2';

$theme[49]['PATH'] = SM_PATH . 'themes/powder_blue.php';
$theme[49]['NAME'] = 'Powder Blue';

$theme[50]['PATH'] = SM_PATH . 'themes/techno_blue.php';
$theme[50]['NAME'] = 'Techno Blue';

$theme[51]['PATH'] = SM_PATH . 'themes/turquoise.php';
$theme[51]['NAME'] = 'Turquoise';

/**
 * Templates
 *   You can define your own template and put it in a new directory
 *   under SM_PATH/templates.  The ID must match the name of
 *   the template directory as the example below. You can name the 
 *   template whatever you want. For an example of a template, see 
 *   the ones included in the SM_PATH/templates directory.
 *
 * To add a new template to the options that users can choose from, just
 * add a new number to the array at the bottom, and follow the pattern.
 *
 * $templateset_default sets theme that will be used by default.
 *
 * @global integer $templateset_default
 */
$templateset_default = 0;
$templateset_fallback = 0;

$aTemplateSet[0]['ID'] = 'default';
$aTemplateSet[0]['NAME'] = 'Default';
$aTemplateSet[1]['ID'] = 'default_advanced';
$aTemplateSet[1]['NAME'] = 'Advanced';

/**
 * Default interface font size.
 * @global string $default_fontsize
 * @since 1.5.1
 */
$default_fontsize = '';

/**
 * Default font set
 * @global string $default_fontset
 * @since 1.5.1
 */
$default_fontset = '';

/**
 * List of available fontsets.
 * @global array $fontsets
 * @since 1.5.1
 */
$fontsets = array();
$fontsets['serif'] = 'serif';
$fontsets['sans'] = 'helvetica,arial,sans-serif';
$fontsets['comicsans'] = 'comic sans ms,sans-serif';
$fontsets['verasans'] = 'bitstream vera sans,verdana,sans-serif';
$fontsets['tahoma'] = 'tahoma,sans-serif';

/**
 * LDAP server(s)
 *   Array of arrays with LDAP server parameters. See
 *   functions/abook_ldap_server.php for a list of possible
 *   parameters
 *
 * EXAMPLE:
 *   $ldap_server[0] = Array(
 *       'host' => 'memberdir.netscape.com',
 *       'name' => 'Netcenter Member Directory',
 *       'base' => 'ou=member_directory,o=netcenter.com'
 *   );
 *
 *   NOTE: please see security note at the top of this file when
 *   entering a password.
 */
// Add your ldap server options here

/**
 * Javascript in Addressbook Control
 *
 * Users may search their addressbook via either a plain HTML or Javascript
 * enhanced user interface. This option allows you to set the default choice.
 * Set this default choice as either:
 *    true  = javascript
 *    false = html
 * @global bool $default_use_javascript_addr_book
 */
$default_use_javascript_addr_book = false;

/**
 * Shared filebased address book
 * @global string $abook_global_file
 * @since 1.5.1 and 1.4.4
 */
$abook_global_file = '';

/**
 * Writing into shared address book control
 * @global bool $abook_global_file_writeable
 * @since 1.5.1 and 1.4.4
 */
$abook_global_file_writeable = false;

/**
 * Listing of shared address book control
 * @global bool $abook_global_file_listing
 * @since 1.5.1
 */
$abook_global_file_listing = true;

/**
 * Controls file based address book entry size
 * 
 * This setting controls space allocated to file based address book records.
 * End users will be unable to save address book entry, if total entry size 
 * (quoted address book fields + 4 delimiters + linefeed) exceeds allowed
 * address book length size.
 *
 * Same setting is applied to personal and global file based address books.
 *
 * It is strongly recommended to keep default setting value. Change it only
 * if you really want to store address book entries that are bigger than two
 * kilobytes (2048).
 * @global integer $abook_file_line_length
 * @since 1.5.2
 */
$abook_file_line_length = 2048;

/**
 * MOTD
 *
 * This is a message that is displayed immediately after a user logs in.
 * @global string $motd
 */
$motd = "";


/**
 * To install plugins, just add elements to this array that have
 * the plugin directory name relative to the /plugins/ directory.
 * For instance, for the 'sqclock' plugin, you'd put a line like
 * the following.
 *    $plugins[] = 'sqclock';
 *    $plugins[] = 'attachment_common';
 */
// Add list of enabled plugins here


/*** Database ***/
/**
 * Read doc/database.txt in order to get more information
 * about these settings.
 */
/**
 * Database-driven private addressbooks
 *   DSN (Data Source Name) for a database where the private
 *   addressbooks are stored.  See doc/db-backend.txt for more info.
 *   If it is not set, the addressbooks are stored in files
 *   in the data dir.
 *   The DSN is in the format: mysql://user:pass@hostname/dbname
 *   The table is the name of the table to use within the
 *   specified database.
 *
 *   NOTE: please see security note at the top of this file when
 *   entering a password.
 */
$addrbook_dsn = '';
$addrbook_table = 'address';
/**
 * Database used to store user data
 */
$prefs_dsn = '';
$prefs_table = 'userprefs';
/**
 * Preference key field
 * @global string $prefs_key_field
 */
$prefs_key_field = 'prefkey';
/**
 * Size of preference key field
 * @global integer $prefs_key_size
 * @since 1.5.1
 */
$prefs_key_size = 64;
/**
 * Preference owner field
 * @global string $prefs_user_field
 */
$prefs_user_field = 'user';
/**
 * Size of preference owner field
 * @global integer $prefs_user_size
 * @since 1.5.1
 */
$prefs_user_size = 128;
/**
 * Preference value field
 * @global string $prefs_val_field
 */
$prefs_val_field = 'prefval';
/**
 * Size of preference key field
 * @global integer $prefs_val_size
 * @since 1.5.1
 */
$prefs_val_size = 65536;

/*** Global sql database options ***/
/**
 * DSN of global address book database
 * @global string $addrbook_global_dsn
 * @since 1.5.1 and 1.4.4
 */
$addrbook_global_dsn = '';
/**
 * Table used for global database address book
 * @global string $addrbook_global_table
 * @since 1.5.1 and 1.4.4
 */
$addrbook_global_table = 'global_abook';
/**
 * Control writing into global database address book
 * @global boolean $addrbook_global_writeable
 * @since 1.5.1 and 1.4.4
 */
$addrbook_global_writeable = false;
/**
 * Control listing of global database address book
 * @global boolean $addrbook_global_listing
 * @since 1.5.1 and 1.4.4
 */
$addrbook_global_listing = false;

/*** Language settings ***/
/**
 * Default language
 *
 *   This is the default language. It is used as a last resort
 *   if SquirrelMail can't figure out which language to display.
 *   Language names usually consist of language code, undercore
 *   symbol and country code
 * @global string $squirrelmail_default_language
 */
$squirrelmail_default_language = 'en_US';

/**
 * Default Charset
 *
 * This option controls what character set is used when sending
 * mail and when sending HTML to the browser. Option works only
 * with US English (en_US) translation. Other translations use
 * charsets that are set in translation settings.
 *
 * @global string $default_charset
 */
$default_charset = 'iso-8859-1';

/**
 * Alternative Language Names Control
 *
 * This options allows displaying native language names in language
 * selection box.
 * @global bool $show_alternative_names
 * @since 1.5.0
 */
$show_alternative_names   = false;

/**
 * Aggressive Decoding Control
 *
 * This option enables reading of Eastern multibyte encodings.
 * Functions that provide this support are very cpu and memory intensive.
 * Don't enable this option unless you really need it.
 * @global bool $aggressive_decoding
 * @since 1.5.1
 */
$aggressive_decoding = false;

/**
 * Lossy Encoding Control
 *
 * This option allows charset conversions when output charset does not support
 * all symbols used in original charset. Symbols unsupported by output charset
 * will be replaced with question marks.
 * @global bool $lossy_encoding
 * @since 1.5.1
 */
$lossy_encoding = false;

/**
 * Controls use of time zone libraries
 *
 * Possible values:
 * <ul>
 *  <li>0 - default, SquirrelMail uses GNU C timezone names in
 *          TZ environment variables
 *  <li>1 - strict, SquirrelMail uses 'TZ' subkey values in TZ
 *          environment variables
 *  <li>2 - custom, SquirrelMail loads time zone data from
 *          config/timezones.php and uses time zone array keys in
 *          TZ enviroment variables
 *  <li>3 - custom strict, SquirrelMail loads time zone data from
 *          config/timezones.php and uses TZ subkey values in
 *          TZ enviroment variables
 * </ul>
 * Use of any other value switches to default SquirrelMail time zone
 * handling ($time_zone_type).
 * @global integer $time_zone_type
 * @since 1.5.1
 */
$time_zone_type = 0;

/**
 * Location base
 * 
 * This is used to build the URL to the SquirrelMail location.
 * It should contain only the protocol and hostname/port parts
 * of the URL; the full path will be appended automatically.
 *
 * If not specified or empty, it will be autodetected.
 *
 * Examples:
 * http://webmail.example.org
 * http://webmail.example.com:8080
 * https://webmail.example.com:6691
 *
 * To be clear: do not include any of the path elements, so if
 * SquirrelMail is at http://www.example.net/web/mail/src/login.php, you
 * write: http://www.example.net
 *
 * @global string $config_location_base
 * @since 1.5.2 and 1.4.8
 */
$config_location_base = '';

/*** Tweaks ***/
/**
 * Iframe sandbox code control
 *
 * Use iframe to render html emails
 * (temp option used during debuging of new code)
 * @global bool $use_iframe
 * @since 1.5.1
 */
$use_iframe = false;

/**
 * Message Icons control
 *
 * Use icons for message and folder markers
 * @global bool $use_icons
 * @since 1.5.1
 */
$use_icons = false;

/**
 * PHP recode functions control
 *
 * Use experimental code with php recode functions when reading messages with
 * different encoding. This code is faster that original SM functions,
 * but it require php with recode support.
 *
 * Don't enable this option if you are not sure about availability of
 * recode support.
 * @global bool $use_php_recode
 * @since 1.5.0
 */
$use_php_recode = false;

/**
 * PHP iconv functions control
 *
 * Use experimental code with php iconv functions when reading messages with
 * different encoding. This code is faster that original SM functions,
 * but it require php with iconv support and works only with some translations.
 *
 * Don't enable this option if you are not sure about availability of
 * iconv support.
 * @global bool $use_php_iconv
 * @since 1.5.0
 */
$use_php_iconv = false;

/**
 * Controls remote configuration checks
 * @global boolean $allow_remote_configtest
 * @since 1.5.1
 */
$allow_remote_configtest = false;

/**
 * Subscribe Listing Control
 *
 * this disables listing all of the folders on the IMAP Server to
 * generate the folder subscribe listbox (this can take a long time
 * when you have a lot of folders).  Instead, a textbox will be
 * displayed allowing users to enter a specific folder name to subscribe to
 *
 * This option can't be changed by conf.pl
 * @global bool $no_list_for_subscribe
 */
$no_list_for_subscribe = false;

/**
 * Color in config control
 *
 * This option is used only by conf.pl script to generate configuration
 * menu with some colors and is provided here only as reference.
 * @global integer $config_use_color
 */
$config_use_color = 2;
