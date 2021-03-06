#!/usr/bin/perl
# conf.pl
# Luke Ehresman (luke@squirrelmail.org)
#
# A simple configure script to configure squirrelmail
#
# $Id$
############################################################              
$conf_pl_version = "1.2.0";

############################################################              
# Some people try to run this as a CGI. That's wrong!
############################################################              
if(defined($ENV{'PATH_INFO'}) || defined($ENV{'QUERY_STRING'}) ||
   defined($ENV{'REQUEST_METHOD'})) {
   print "Content-Type: text/html\n\n";
   print "You must run this script from the command line.";
   exit;
}

############################################################              
# First, lets read in the data already in there...
############################################################              
if ( -e "config.php") {
   open (FILE, "config.php");
   while ($line = <FILE>) {
      $line =~ s/^\s+//;
      $line =~ s/^\$//;
      $var = $line;
      
      $var =~ s/=/EQUALS/;
      if ($var =~ /^([a-z]|[A-Z])/) {
         @o = split(/\s*EQUALS\s*/, $var);
         if ($o[0] eq "config_version") {
            $o[1] =~ s/[\n|\r]//g;
            $o[1] =~ s/[\'|\"];\s*$//;
            $o[1] =~ s/;$//;
            $o[1] =~ s/^[\'|\"]//;

            $config_version = $o[1];
            close (FILE);
         }
      }   
   }
   close (FILE);

   if ($config_version ne $conf_pl_version) {
      system "clear";
      print $WHT."WARNING:\n".$NRM;
      print "  The file \"config/config.php\" was found, but it is for\n";
      print "  an older version of SquirrelMail. It is possible to still\n";
      print "  read the defaults from this file but be warned that many\n";
      print "  preferences change between versions. It is recommended that\n";
      print "  you start with a clean config.php for each upgrade that you\n";
      print "  do. To do this, just move config/config.php out of the way.\n";
      print "\n";
      print "Continue loading with the old config.php [y/N]? ";
      $ctu = <STDIN>;
      if (($ctu !~ /^y\n/i) || ($ctu =~ /^\n/)) {
         exit;
      }

      print "\nDo you want me to stop warning you [y/N]? ";
      $ctu = <STDIN>;
      if ($ctu =~ /^y\n/i) {
         $print_config_version = $conf_pl_version;
      } else {
         $print_config_version = $config_version;
      }
   } else {
      $print_config_version = $config_version;
   } 

   $config = 1;
   open (FILE, "config.php");
} elsif (-e "config_default.php") {
   open (FILE, "config_default.php");
   while ($line = <FILE>) {
      $line =~ s/^\s+//;
      $line =~ s/^\$//;
      $var = $line;
      
      $var =~ s/=/EQUALS/;
      if ($var =~ /^([a-z]|[A-Z])/) {
         @o = split(/\s*EQUALS\s*/, $var);
         if ($o[0] eq "config_version") {
            $o[1] =~ s/[\n|\r]//g;
            $o[1] =~ s/[\'|\"];\s*$//;
            $o[1] =~ s/;$//;
            $o[1] =~ s/^[\'|\"]//;

            $config_version = $o[1];
            close (FILE);
         }
      }   
   }
   close (FILE);

   if ($config_version ne $conf_pl_version) {
      system "clear";
      print $WHT."WARNING:\n".$NRM;
      print "  You are trying to use a 'config_default.php' from an older\n";
      print "  version of SquirrelMail. This is HIGHLY unrecommended. You\n";
      print "  should get the 'config_default.php' that matches the version\n";
      print "  of SquirrelMail that you are running. You can get this from\n";
      print "  the SquirrelMail web page by going to the following URL:\n";
      print "      http://www.squirrelmail.org.\n";
      print "\n";
      print "Continue loading with old config_default.php (a bad idea) [y/N]? ";
      $ctu = <STDIN>;
      if (($ctu !~ /^y\n/i) || ($ctu =~ /^\n/)) {
         exit;
      }

      print "\nDo you want me to stop warning you [y/N]? ";
      $ctu = <STDIN>;
      if ($ctu =~ /^y\n/i) {
         $print_config_version = $conf_pl_version;
      } else {
         $print_config_version = $config_version;
      }
   } else {
      $print_config_version = $config_version;
   }
   $config = 2;
   open (FILE, "config_default.php");
} else {
   print "No configuration file found. Please get config_default.php\n";
   print "or config.php before running this again. This program needs\n";
   print "a default config file to get default values.\n";
   exit;
}

# Read and parse the current configuration file
# (either config.php or config_default.php).
while ($line = <FILE>) {
   $line =~ s/^\s+//;
   $line =~ s/^\$//;
   $var = $line;
      
   $var =~ s/=/EQUALS/;
   if ($var =~ /^([a-z]|[A-Z])/) {
      @options = split(/\s*EQUALS\s*/, $var);
      $options[1] =~ s/[\n|\r]//g;
      $options[1] =~ s/[\'|\"];\s*$//;
      $options[1] =~ s/;$//;
      $options[1] =~ s/^[\'|\"]//;

      if ($options[0] =~ /^theme\[[0-9]+\]\[['|"]PATH['|"]\]/) {
         $sub = $options[0];
         $sub =~ s/\]\[['|"]PATH['|"]\]//;
         $sub =~ s/.*\[//; 
         if (-e "../themes") {
            $options[1] =~ s/^\.\.\/config/\.\.\/themes/;
         }   
         $theme_path[$sub] = $options[1];
      } elsif ($options[0] =~ /^theme\[[0-9]+\]\[['|"]NAME['|"]\]/) {
         $sub = $options[0];
         $sub =~ s/\]\[['|"]NAME['|"]\]//;
         $sub =~ s/.*\[//; 
         $theme_name[$sub] = $options[1];
      } elsif ($options[0] =~ /^plugins\[[0-9]+\]/) {
         $sub = $options[0];
         $sub =~ s/\]//; 
         $sub =~ s/^plugins\[//;
         $plugins[$sub] = $options[1];
      } elsif ($options[0] =~ /^ldap_server\[[0-9]+\]/) {
         $sub = $options[0];
         $sub =~ s/\]//; 
         $sub =~ s/^ldap_server\[//;
         $continue = 0;
         while (($tmp = <FILE>) && ($continue != 1)) {
            if ($tmp =~ /\);\s*$/) {
               $continue = 1;
            }
            
            if ($tmp =~ /^\s*[\'|\"]host[\'|\"]/i) {
               $tmp =~ s/^\s*[\'|\"]host[\'|\"]\s*=>\s*[\'|\"]//i;
               $tmp =~ s/[\'|\"],?\s*$//;
               $tmp =~ s/[\'|\"]\);\s*$//;
               $host = $tmp;
            } elsif ($tmp =~ /^\s*[\'|\"]base[\'|\"]/i) {
               $tmp =~ s/^\s*[\'|\"]base[\'|\"]\s*=>\s*[\'|\"]//i;
               $tmp =~ s/[\'|\"],?\s*$//;
               $tmp =~ s/[\'|\"]\);\s*$//;
               $base = $tmp;
            } elsif ($tmp =~ /^\s*[\'|\"]charset[\'|\"]/i) {
               $tmp =~ s/^\s*[\'|\"]charset[\'|\"]\s*=>\s*[\'|\"]//i;
               $tmp =~ s/[\'|\"],?\s*$//;
               $tmp =~ s/[\'|\"]\);\s*$//;
               $charset = $tmp;
            } elsif ($tmp =~ /^\s*[\'|\"]port[\'|\"]/i) {
               $tmp =~ s/^\s*[\'|\"]port[\'|\"]\s*=>\s*[\'|\"]//i;
               $tmp =~ s/[\'|\"],?\s*$//;
               $tmp =~ s/[\'|\"]\);\s*$//;
               $port = $tmp;
            } elsif ($tmp =~ /^\s*[\'|\"]maxrows[\'|\"]/i) {
               $tmp =~ s/^\s*[\'|\"]maxrows[\'|\"]\s*=>\s*[\'|\"]//i;
               $tmp =~ s/[\'|\"],?\s*$//;
               $tmp =~ s/[\'|\"]\);\s*$//;
               $maxrows = $tmp;
            } elsif ($tmp =~ /^\s*[\'|\"]name[\'|\"]/i) {
               $tmp =~ s/^\s*[\'|\"]name[\'|\"]\s*=>\s*[\'|\"]//i;
               $tmp =~ s/[\'|\"],?\s*$//;
               $tmp =~ s/[\'|\"]\);\s*$//;
               $name = $tmp;
            }
         }
         $ldap_host[$sub] = $host;
         $ldap_base[$sub] = $base;
         $ldap_name[$sub] = $name;
         $ldap_port[$sub] = $port;
         $ldap_maxrows[$sub] = $maxrows;
         $ldap_charset[$sub] = $charset;
      } else {
         ${$options[0]} = $options[1];
      }   
   }   
}
close FILE;
if ($useSendmail ne "true") {
   $useSendmail = "false";
}
if (!$sendmail_path) {
   $sendmail_path = "/usr/sbin/sendmail";
}
if (!$default_unseen_notify) {
   $default_unseen_notify = 2;
}
if (!$default_unseen_type) {
   $default_unseen_type = 1;
}
if (!$config_use_color) {
   $config_use_color = 0;
}
if (!$invert_time) {
   $invert_time = "false";
}
if (!$force_username_lowercase) {
    $force_username_lowercase = "false";
}
if (!$optional_delimiter) {
    $optional_delimiter = "detect";
}
if (!$use_authenticated_smtp) {
    $use_authenticated_smtp = "false";
}
if (!$auto_create_special) {
    $auto_create_special = "false";
}
if(!$default_use_priority) {
    $default_use_priority = "true";
}
if(!$hide_sm_attributions) {
    $hide_sm_attributions = "false";
}

#####################################################################################
if ($config_use_color == 1) {
   $WHT = "\x1B[37;1m";
   $NRM = "\x1B[0m";
} else {
   $WHT = "";
   $NRM = "";
   $config_use_color = 2;
}

while (($command ne "q") && ($command ne "Q")) {
   system "clear";
   print $WHT."SquirrelMail Configuration : ".$NRM;
   if ($config == 1) { print "Read: config.php"; }
   elsif ($config == 2) { print "Read: config_default.php"; }
   print " ($print_config_version)\n";
   print "---------------------------------------------------------\n";

   if ($menu == 0) {
      print $WHT."Main Menu --\n".$NRM;
      print "1.  Organization Preferences\n";
      print "2.  Server Settings\n";
      print "3.  Folder Defaults\n";
      print "4.  General Options\n";
      print "5.  Themes\n";
      print "6.  Address Books (LDAP)\n";
      print "7.  Message of the Day (MOTD)\n";
      print "8.  Plugins\n";
      print "\n";
      print "D.  Set pre-defined settings for specific IMAP servers\n";
      print "\n";
   } elsif ($menu == 1) {
      print $WHT."Organization Preferences\n".$NRM;
      print "1.  Organization Name    : $WHT$org_name$NRM\n";
      print "2.  Organization Logo    : $WHT$org_logo$NRM\n";
      print "3.  Organization Title   : $WHT$org_title$NRM\n";
      print "4.  Signout Page         : $WHT$signout_page$NRM\n";
      print "5.  Default Language     : $WHT$squirrelmail_default_language$NRM\n";
      print "\n";
      print "R   Return to Main Menu\n";
   } elsif ($menu == 2) {
      print $WHT."Server Settings\n".$NRM;
      print "1.  Domain               : $WHT$domain$NRM\n";
      print "2.  IMAP Server          : $WHT$imapServerAddress$NRM\n";
      print "3.  IMAP Port            : $WHT$imapPort$NRM\n";
      print "4.  Use Sendmail/SMTP    : $WHT";
      if ($useSendmail eq "true") {
         print "Sendmail";
      } else {
         print "SMTP";
      }
      print "$NRM\n";
      if ($useSendmail eq "true") {
         print "5.    Sendmail Path      : $WHT$sendmail_path$NRM\n";
      } else {
         print "6.    SMTP Server        : $WHT$smtpServerAddress$NRM\n";
         print "7.    SMTP Port          : $WHT$smtpPort$NRM\n";
         print "8.    Authenticated SMTP : $WHT$use_authenticated_smtp$NRM\n";
      }
      print "9.  Server               : $WHT$imap_server_type$NRM\n";
      print "10. Invert Time          : $WHT$invert_time$NRM\n";
      print "11. Delimiter            : $WHT$optional_delimiter$NRM\n";
      print "\n";
      print "R   Return to Main Menu\n";
   } elsif ($menu == 3) {
      print $WHT."Folder Defaults\n".$NRM;
      print "1.  Default Folder Prefix       : $WHT$default_folder_prefix$NRM\n";
      print "2.  Show Folder Prefix Option   : $WHT$show_prefix_option$NRM\n";
      print "3.  Trash Folder                : $WHT$trash_folder$NRM\n";
      print "4.  Sent Folder                 : $WHT$sent_folder$NRM\n";
      print "5.  Drafts Folder               : $WHT$draft_folder$NRM\n";
      print "6.  By default, move to trash   : $WHT$default_move_to_trash$NRM\n";
      print "7.  By default, move to sent    : $WHT$default_move_to_sent$NRM\n";
      print "8.  By default, save as draft   : $WHT$default_save_as_draft$NRM\n";
      print "9.  List Special Folders First  : $WHT$list_special_folders_first$NRM\n";
      print "10. Show Special Folders Color  : $WHT$use_special_folder_color$NRM\n";
      print "11. Auto Expunge                : $WHT$auto_expunge$NRM\n";
      print "12. Default Sub. of INBOX       : $WHT$default_sub_of_inbox$NRM\n";
      print "13. Show 'Contain Sub.' Option  : $WHT$show_contain_subfolders_option$NRM\n";
      print "14. Default Unseen Notify       : $WHT$default_unseen_notify$NRM\n";
      print "15. Default Unseen Type         : $WHT$default_unseen_type$NRM\n";
      print "16. Auto Create Special Folders : $WHT$auto_create_special$NRM\n";
      print "\n";
      print "R   Return to Main Menu\n";
   } elsif ($menu == 4) {
      print $WHT."General Options\n".$NRM;
      print "1.  Default Charset        : $WHT$default_charset$NRM\n";
      print "2.  Data Directory         : $WHT$data_dir$NRM\n";
      print "3.  Attachment Directory   : $WHT$attachment_dir$NRM\n";
      print "4.  Directory Hash Level   : $WHT$dir_hash_level$NRM\n";
      print "5.  Default Left Size      : $WHT$default_left_size$NRM\n";
      print "6.  Usernames in Lowercase : $WHT$force_username_lowercase$NRM\n";
      print "7.  Allow use of priority  : $WHT$default_use_priority$NRM\n";
      print "8.  Hide SM attributions   : $WHT$hide_sm_attributions$NRM\n";
      print "\n";
      print "R   Return to Main Menu\n";
   } elsif ($menu == 5) {
      print $WHT."Themes\n".$NRM;
      print "1.  Change Themes\n";
      for ($count = 0; $count <= $#theme_name; $count++) {
         print "    >  $theme_name[$count]\n";
      }
      print "2.  CSS File : $WHT$theme_css$NRM\n";
      print "\n";
      print "R   Return to Main Menu\n";
   } elsif ($menu == 6) {
      print $WHT."Address Books (LDAP)\n".$NRM;
      print "1.  Change Servers\n";
      for ($count = 0; $count <= $#ldap_host; $count++) {
         print "    >  $ldap_host[$count]\n";
      }
      print "2.  Use Javascript Address Book Search  : $WHT$default_use_javascript_addr_book$NRM\n";
      print "\n";
      print "R   Return to Main Menu\n";
   } elsif ($menu == 7) {
      print $WHT."Message of the Day (MOTD)\n".$NRM;
      print "\n$motd\n";
      print "\n";
      print "1   Edit the MOTD\n";
      print "\n";
      print "R   Return to Main Menu\n";
   } elsif ($menu == 8) {
      print $WHT."Plugins\n".$NRM;
      print "  Installed Plugins\n";
      $num = 0;
      for ($count = 0; $count <= $#plugins; $count++) {
         $num = $count + 1;
         print "    $num. $plugins[$count]\n";
      }
      print "\n  Available Plugins:\n";
      opendir(DIR, "../plugins");
      @files = readdir(DIR);
      $pos=0;
      @unused_plugins = ();
      for ($i=0; $i <= $#files; $i++) {
         if ( -d "../plugins/" . $files[$i] && 
	     $files[$i] !~ /^\./ && $files[$i] ne "CVS") {
            $match = 0;
            for ($k=0; $k<=$#plugins; $k++) {
               if ($plugins[$k] eq $files[$i]) {
                  $match = 1;
               }
            }
            if ($match == 0) {
               $unused_plugins[$pos] = $files[$i];
               $pos++;
            }
         }
      }
      
      for ($i=0; $i<=$#unused_plugins; $i++) {
         $num = $num + 1;
         print "    $num. $unused_plugins[$i]\n"; 
      }
      closedir DIR;
      
      print "\n";
      print "A   Sanitize all plugins for use with Squirrelmail 1.2\n";
      print "\n";
      print "R   Return to Main Menu\n";
   }
   if ($config_use_color == 1) {
      print "C.  Turn color off\n";
   } else {
      print "C.  Turn color on\n";
   }
   print "S   Save data\n";
   print "Q   Quit\n";

   print "\n";
   print "Command >> ".$WHT;
   $command = <STDIN>;
   $command =~ s/[\n|\r]//g;
   $command =~ tr/A-Z/a-z/;
   print "$NRM\n";

   # Read the commands they entered.
   if ($command eq "r") {
      $menu = 0;
   } elsif ($command eq "s") {
      save_data ();
      print "Data saved in config.php\n";
      print "Press enter to continue...";
      $tmp = <STDIN>;
      $saved = 1;
   } elsif (($command eq "q") && ($saved == 0)) {
      print "You have not saved your data.\n";
      print "Save?  [".$WHT."Y".$NRM."/n]: ";
      $save = <STDIN>;
      if (($save =~ /^y/i) || ($save =~ /^\s*$/)) {
         save_data ();
       }
   } elsif ($command eq "c") {
      if ($config_use_color == 1) {
         $config_use_color = 2;
         $WHT = "";
         $NRM = "";
      } else {
         $config_use_color = 1;
         $WHT = "\x1B[37;1m";
         $NRM = "\x1B[0m";
      }
   } elsif ($command eq "d" && $menu == 0) {
      set_defaults ();
   } else {
      $saved = 0;
      if ($menu == 0) {
         if (($command > 0) && ($command < 9)) {
            $menu = $command;
         }
      } elsif ($menu == 1) {
         if    ($command == 1) { $org_name   = command1 (); }
         elsif ($command == 2) { $org_logo   = command2 (); }
         elsif ($command == 3) { $org_title  = command3 (); }
         elsif ($command == 4) { $signout_page  = command4 (); }
	 elsif ($command == 5) { $squirrelmail_default_language = command5(); }
      } elsif ($menu == 2) {
         if    ($command == 1)  { $domain                 = command11 (); }
         elsif ($command == 2)  { $imapServerAddress      = command12 (); }
         elsif ($command == 3)  { $imapPort               = command13 (); }
         elsif ($command == 4)  { $useSendmail            = command14 (); }
         elsif ($command == 5)  { $sendmail_path          = command15 (); }
         elsif ($command == 6)  { $smtpServerAddress      = command16 (); }
         elsif ($command == 7)  { $smtpPort               = command17 (); }
         elsif ($command == 8)  { $use_authenticated_smtp = command18 (); }
         elsif ($command == 9)  { $imap_server_type       = command19 (); }
         elsif ($command == 10) { $invert_time            = command110 (); }
         elsif ($command == 11) { $optional_delimiter     = command111 (); }
      } elsif ($menu == 3) {
         if    ($command == 1)  { $default_folder_prefix          = command21 (); }
         elsif ($command == 2)  { $show_prefix_option             = command22 (); }
         elsif ($command == 3)  { $trash_folder                   = command23a (); }
         elsif ($command == 4)  { $sent_folder                    = command23b (); }
         elsif ($command == 5)  { $draft_folder                   = command23c (); }
         elsif ($command == 6)  { $default_move_to_trash          = command24a (); }
         elsif ($command == 7)  { $default_move_to_sent           = command24b (); }
         elsif ($command == 8)  { $default_save_as_draft          = command24c (); }
         elsif ($command == 9)  { $list_special_folders_first     = command27 (); }
         elsif ($command == 10) { $use_special_folder_color       = command28 (); }
         elsif ($command == 11) { $auto_expunge                   = command29 (); }
         elsif ($command == 12) { $default_sub_of_inbox           = command210(); }
         elsif ($command == 13) { $show_contain_subfolders_option = command211(); }
         elsif ($command == 14) { $default_unseen_notify          = command212(); }
         elsif ($command == 15) { $default_unseen_type            = command213(); }
         elsif ($command == 16) { $auto_create_special            = command214(); }
      } elsif ($menu == 4) {
         if    ($command == 1) { $default_charset          = command31 (); }
         elsif ($command == 2) { $data_dir                 = command33a (); }
         elsif ($command == 3) { $attachment_dir           = command33b (); }
         elsif ($command == 4) { $dir_hash_level           = command33c (); }
         elsif ($command == 5) { $default_left_size        = command35 (); }
	 elsif ($command == 6) { $force_username_lowercase = command36 (); }
	 elsif ($command == 7) { $default_use_priority     = command37 (); }
         elsif ($command == 8) { $hide_sm_attributions     = command38 (); }
      } elsif ($menu == 5) {
         if    ($command == 1) { command41 (); }
         elsif ($command == 2) { $theme_css = command42 (); }
      } elsif ($menu == 6) {
         if    ($command == 1) { command61(); }
         elsif ($command == 2) { command62(); }
      } elsif ($menu == 7) {
         if    ($command == 1) { $motd             = command71(); }
      } elsif ($menu == 8) {
         if    ($command =~ /^[0-9]+/) { @plugins = command81(); }
         elsif ($command eq "a") { command8s(); }
      }
   }   
}

####################################################################################

# org_name
sub command1 {
   print "We have tried to make the name SquirrelMail as transparent as\n";
   print "possible.  If you set up an organization name, most places where\n";
   print "SquirrelMail would take credit will be credited to your organization.\n";
   print "\n";
   print "[$WHT$org_name$NRM]: $WHT";
   $new_org_name = <STDIN>;
   if ($new_org_name eq "\n") {
      $new_org_name = $org_name;
   } else {
      $new_org_name =~ s/[\r|\n]//g;
   }
   return $new_org_name;
}


# org_logo
sub command2 {
   print "Your organization's logo is an image that will be displayed at\n";
   print "different times throughout SquirrelMail.  This is asking for the\n";
   print "literal (/usr/local/squirrelmail/images/logo.jpg) or relative\n";
   print "(../images/logo.jpg) path to your logo.\n";
   print "\n";
   print "[$WHT$org_logo$NRM]: $WHT";
   $new_org_logo = <STDIN>;
   if ($new_org_logo eq "\n") {
      $new_org_logo = $org_logo;
   } else {
      $new_org_logo =~ s/[\r|\n]//g;
   }
   return $new_org_logo;
}

# org_title
sub command3 {
   print "A title is what is displayed at the top of the browser window in\n";
   print "the titlebar.  Usually this will end up looking something like:\n";
   print "\"Netscape: $org_title\"\n";
   print "\n";
   print "[$WHT$org_title$NRM]: $WHT";
   $new_org_title = <STDIN>;
   if ($new_org_title eq "\n") {
      $new_org_title = $org_title;
   } else {
      $new_org_title =~ s/[\r|\n]//g;
   }
   return $new_org_title;
}

# signout_page
sub command4 {
   print "When users click the Sign Out button they will be logged out and\n";
   print "then sent to signout_page.  If signout_page is left empty,\n";
   print "(hit space and then return) they will be taken, as normal,\n";
   print "to the default and rather sparse SquirrelMail signout page.\n";
   print "\n";
   print "[$WHT$signout_page$NRM]: $WHT";
   $new_signout_page = <STDIN>;
   if ($new_signout_page eq "\n") {
      $new_signout_page = $signout_page;
   } else {
      $new_signout_page =~ s/[\r|\n]//g;
      $new_signout_page =~ s/^\s+$//g;
   }
   return $new_signout_page;
}

# Default language
sub command5 {
   print "SquirrelMail attempts to set the language in many ways.  If it\n";
   print "can not figure it out in another way, it will default to this\n";
   print "language.  Please use the two-letter code for the desired language.\n";
   print "\n";
   print "[$WHT$squirrelmail_default_language$NRM]: $WHT";
   $new_signout_page = <STDIN>;
   if ($new_signout_page eq "\n") {
      $new_signout_page = $squirrelmail_default_language;
   } else {
      $new_signout_page =~ s/[\r|\n]//g;
      $new_signout_page =~ s/^\s+$//g;
   }
   return $new_signout_page;
}

####################################################################################

# domain
sub command11 {
   print "The domain name is the suffix at the end of all email messages.  If\n";
   print "for example, your email address is jdoe\@myorg.com, then your domain\n";
   print "would be myorg.com.\n";
   print "\n";
   print "[$WHT$domain$NRM]: $WHT";
   $new_domain = <STDIN>;
   if ($new_domain eq "\n") {
      $new_domain = $domain;
   } else {
      $new_domain =~ s/[\r|\n]//g;
   }
   return $new_domain;
}

# imapServerAddress
sub command12 {
   print "This is the address where your IMAP server resides.\n";
   print "[$WHT$imapServerAddress$NRM]: $WHT";
   $new_imapServerAddress = <STDIN>;
   if ($new_imapServerAddress eq "\n") {
      $new_imapServerAddress = $imapServerAddress;
   } else {
      $new_imapServerAddress =~ s/[\r|\n]//g;
   }
   return $new_imapServerAddress;
}

# imapPort
sub command13 {
   print "This is the port that your IMAP server is on.  Usually this is 143.\n";
   print "[$WHT$imapPort$NRM]: $WHT";
   $new_imapPort = <STDIN>;
   if ($new_imapPort eq "\n") {
      $new_imapPort = $imapPort;
   } else {
      $new_imapPort =~ s/[\r|\n]//g;
   }
   return $new_imapPort;
}

# useSendmail
sub command14 {
   print "You now need to choose the method that you will use for sending\n";
   print "messages in SquirrelMail.  You can either connect to an SMTP server\n";
   print "or use sendmail directly.\n";
   if ($useSendmail eq "true") {
      $default_value = "1";
   } else {
      $default_value = "2";
   }
   print "\n";
   print "  1.  Sendmail\n";
   print "  2.  SMTP\n";
   print "Your choice [1/2] [$WHT$default_value$NRM]: $WHT";
   $use_sendmail = <STDIN>;
   if (($use_sendmail =~ /^1\n/i) || (($use_sendmail =~ /^\n/) && ($default_value eq "1"))) {
      $useSendmail = "true";
   } else {
      $useSendmail = "false";
   }
   return $useSendmail;
}

# sendmail_path
sub command15 {
   if ($sendmail_path[0] !~ /./) {
      $sendmail_path = "/usr/sbin/sendmail";
   }
   print "Specify where the sendmail executable is located.  Usually /usr/sbin/sendmail\n";
   print "[$WHT$sendmail_path$NRM]: $WHT";
   $new_sendmail_path = <STDIN>;
   if ($new_sendmail_path eq "\n") {
      $new_sendmail_path = $sendmail_path;
   } else {
      $new_sendmail_path =~ s/[\r|\n]//g;
   }
   return $new_sendmail_path;
}

# smtpServerAddress
sub command16 {
   print "This is the location of your SMTP server.\n";
   print "[$WHT$smtpServerAddress$NRM]: $WHT";
   $new_smtpServerAddress = <STDIN>;
   if ($new_smtpServerAddress eq "\n") {
      $new_smtpServerAddress = $smtpServerAddress;
   } else {
      $new_smtpServerAddress =~ s/[\r|\n]//g;
   }
   return $new_smtpServerAddress;
}

# smtpPort
sub command17 {
   print "This is the port to connect to for SMTP.  Usually 25.\n";
   print "[$WHT$smtpPort$NRM]: $WHT";
   $new_smtpPort = <STDIN>;
   if ($new_smtpPort eq "\n") {
      $new_smtpPort = $smtpPort;
   } else {
      $new_smtpPort =~ s/[\r|\n]//g;
   }
   return $new_smtpPort;
}

# authenticated server 
sub command18 {
   print "Do you wish to use an authenticated SMTP server?  Your server must\n";
   print "support this in order for SquirrelMail to work with it.  We implemented\n";
   print "it according to RFC 2554.\n";
   
   $YesNo = 'n';
   $YesNo = 'y' if ($use_authenticated_smtp eq "true");

   print "Use authenticated SMTP server (y/n) [$WHT$YesNo$NRM]: $WHT";

   $new_use_authenticated_smtp = <STDIN>;
   $new_use_authenticated_smtp =~ tr/yn//cd;
   return "true" if ($new_use_authenticated_smtp eq "y");
   return "false" if ($new_use_authenticated_smtp eq "n");
   return $use_authenticated_smtp;
}   

# imap_server_type 
sub command19 {
   print "Each IMAP server has its own quirks.  As much as we tried to stick\n";
   print "to standards, it doesn't help much if the IMAP server doesn't follow\n";
   print "the same principles.  We have made some work-arounds for some of\n";
   print "these servers.  If you would like to use them, please select your\n";
   print "IMAP server.  If you do not wish to use these work-arounds, you can\n";
   print "set this to \"other\", and none will be used.\n";
   print "    cyrus      = Cyrus IMAP server\n";
   print "    uw         = University of Washington's IMAP server\n";
   print "    exchange   = Microsoft Exchange IMAP server\n";
   print "    courier    = Courier IMAP server\n";
   print "    other      = Not one of the above servers\n";
   print "[$WHT$imap_server_type$NRM]: $WHT";
   $new_imap_server_type = <STDIN>;
   if ($new_imap_server_type eq "\n") {
      $new_imap_server_type = $imap_server_type;
   } else {
      $new_imap_server_type =~ s/[\r|\n]//g;
   }
   return $new_imap_server_type;
}

# invert_time
sub command110 {
   print "Sometimes the date of messages sent is messed up (off by a few hours\n";
   print "on some machines).  Typically this happens if the system doesn't support\n";
   print "tm_gmtoff.  It will happen only if your time zone is \"negative\".\n";
   print "This most often occurs on Solaris 7 machines in the United States.\n";
   print "By default, this is off.  It should be kept off unless problems surface\n";
   print "about the time that messages are sent.\n";
   print "    no  = Do NOT fix time -- almost always correct\n";
   print "    yes = Fix the time for this system\n";
   
   $YesNo = 'n';
   $YesNo = 'y' if ($invert_time eq "true");

   print "Fix the time for this system (y/n) [$WHT$YesNo$NRM]: $WHT";

   $new_invert_time = <STDIN>;
   $new_invert_time =~ tr/yn//cd;
   return "true" if ($new_invert_time eq "y");
   return "false" if ($new_invert_time eq "n");
   return $invert_time;
}   

sub command111 {
	print "This is the delimiter that your IMAP server uses to distinguish between\n";
	print "folders.  For example, Cyrus uses '.' as the delimiter and a complete\n";
	print "folder would look like 'INBOX.Friends.Bob', while UW uses '/' and would\n";
	print "look like 'INBOX/Friends/Bob'.  Normally this should be left at 'detect'\n";
	print "but if you are sure you know what delimiter your server uses, you can\n";
	print "specify it here.\n";
	print "\nTo have it autodetect the delimiter, set it to 'detect'.\n\n";
   print "[$WHT$optional_delimiter$NRM]: $WHT";
   $new_optional_delimiter = <STDIN>;
   if ($new_optional_delimiter eq "\n") {
      $new_optional_delimiter = $optional_delimiter;
   } else {
      $new_optional_delimiter =~ s/[\r|\n]//g;
   }
   return $new_optional_delimiter;
}

# MOTD
sub command71 {
   print "\nYou can now create the welcome message that is displayed\n";
   print "every time a user logs on.  You can use HTML or just plain\n";
   print "text.  If you do not wish to have one, just make it blank.\n\n(Type @ on a blank line to exit)\n";
   
   $new_motd = "";
   do {
      print "] ";
      $line = <STDIN>;
      $line =~ s/[\r|\n]//g;
      if ($line ne "@") {
         $line =~ s/  /\&nbsp;\&nbsp;/g;
         $line =~ s/\t/\&nbsp;\&nbsp;\&nbsp;\&nbsp;/g;
         $line =~ s/$/ /;
         $line =~ s/\"/\\\"/g;

         $new_motd = $new_motd . $line;
      }
   } while ($line ne "@");
   return $new_motd;
}

################# PLUGINS ###################

sub command81 {
   $command =~ s/[\s|\n|\r]*//g;
   if ($command > 0) {  
      $command = $command - 1;
      if ($command <= $#plugins) {
         @newplugins = ();
         $ct=0;
         while ($ct <= $#plugins) {
            if ($ct != $command) {
               @newplugins = (@newplugins, $plugins[$ct]);
            }
            $ct++;
         }
         @plugins = @newplugins;
      } elsif ($command <= $#plugins + $#unused_plugins + 1) {
         $num = $command - $#plugins - 1;
         @newplugins = @plugins;
         $ct=0;
         while ($ct <= $#unused_plugins) {
            if ($ct == $num) {
               @newplugins = (@newplugins, $unused_plugins[$ct]);
               # sanitize the plugin
	       $dir = $unused_plugins[$ct];
               `./ri_once.pl ../plugins/$dir`;
            }
            $ct++;
         }
         @plugins = @newplugins;
      }
   }   
   return @plugins;
}   

sub command8s {
    print "This command will sanitize all plugins for use with\n";
    print "Squirrelmail 1.2. That is, it will rewrite some php-\n";
    print "constructs that are *incompatible* with the 1.2 design\n";
    print "into ones that are *compatible*\n";
    print "Do you wish to issue this command [y/N]? ";
    $ctu = <STDIN>;
    if ($ctu =~ /^y\n/i) {
        `./ri_once.pl ../plugins`;
    }
}

################# FOLDERS ###################

# default_folder_prefix
sub command21 {
   print "Some IMAP servers (UW, for example) store mail and folders in\n";
   print "your user space in a separate subdirectory.  This is where you\n";
   print "specify what that directory is.\n";
   print "\n";
   print "EXAMPLE:  mail/";
   print "\n";
   print "NOTE:  If you use Cyrus, or some server that would not use this\n";
   print "       option, you must set this to 'none'.\n";
   print "\n";
   print "[$WHT$default_folder_prefix$NRM]: $WHT";
   $new_default_folder_prefix = <STDIN>;
   if ($new_default_folder_prefix eq "\n") {
      $new_default_folder_prefix = $default_folder_prefix;
   } else {
      $new_default_folder_prefix =~ s/[\r|\n]//g;
   }
   if (($new_default_folder_prefix =~ /^\s*$/) || ($new_default_folder_prefix =~ /none/i)) {
      $new_default_folder_prefix = "";
   } else {
      $new_default_folder_prefix =~ s/\/*$//g;
      $new_default_folder_prefix =~ s/$/\//g;
   }   
   return $new_default_folder_prefix;
}

# Show Folder Prefix
sub command22 {
   print "It is possible to set up the default folder prefix as a user\n";
   print "specific option, where each user can specify what their mail\n";
   print "folder is.  If you set this to false, they will never see the\n";
   print "option, but if it is true, this option will appear in the\n";
   print "'options' section.\n";
   print "\n";
   print "NOTE:  You set the default folder prefix in option '1' of this\n";
   print "       section.  That will be the default if the user doesn't\n";
   print "       specify anything different.\n";
   print "\n";
   
   if ($show_prefix_option eq "true") {
      $default_value = "y";
   } else {
      $default_value = "n";
   }
   print "\n";
   print "Show option (y/n) [$WHT$default_value$NRM]: $WHT";
   $new_show = <STDIN>;
   if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
      $show_prefix_option = "true";
   } else {
      $show_prefix_option = "false";
   }
   return $show_prefix_option;
}

# Trash Folder 
sub command23a {
   print "You can now specify where the default trash folder is located.\n";
   print "On servers where you do not want this, you can set it to anything\n";
   print "and set option 6 to false.\n";
   print "\n";
   print "This is relative to where the rest of your email is kept.  You do\n";
   print "not need to worry about their mail directory.  If this folder\n";
   print "would be ~/mail/trash on the filesystem, you only need to specify\n";
   print "that this is 'trash', and be sure to put 'mail/' in option 1.\n";
   print "\n";

   print "[$WHT$trash_folder$NRM]: $WHT";
   $new_trash_folder = <STDIN>;
   if ($new_trash_folder eq "\n") {
      $new_trash_folder = $trash_folder;
   } else {
      $new_trash_folder =~ s/[\r|\n]//g;
   }
   return $new_trash_folder;
}

# Sent Folder 
sub command23b {
   print "This is where messages that are sent will be stored.  SquirrelMail\n";
   print "by default puts a copy of all outgoing messages in this folder.\n";
   print "\n";
   print "This is relative to where the rest of your email is kept.  You do\n";
   print "not need to worry about their mail directory.  If this folder\n";
   print "would be ~/mail/sent on the filesystem, you only need to specify\n";
   print "that this is 'sent', and be sure to put 'mail/' in option 1.\n";
   print "\n";

   print "[$WHT$sent_folder$NRM]: $WHT";
   $new_sent_folder = <STDIN>;
   if ($new_sent_folder eq "\n") {
      $new_sent_folder = $sent_folder;
   } else {
      $new_sent_folder =~ s/[\r|\n]//g;
   }
   return $new_sent_folder;
}

# Draft Folder 
sub command23c {
   print "You can now specify where the default draft folder is located.\n";
   print "On servers where you do not want this, you can set it to anything\n";
   print "and set option 9 to false.\n";
   print "\n";
   print "This is relative to where the rest of your email is kept.  You do\n";
   print "not need to worry about their mail directory.  If this folder\n";
   print "would be ~/mail/drafts on the filesystem, you only need to specify\n";
   print "that this is 'drafts', and be sure to put 'mail/' in option 1.\n";
   print "\n";

   print "[$WHT$draft_folder$NRM]: $WHT";
   $new_draft_folder = <STDIN>;
   if ($new_draft_folder eq "\n") {
      $new_draft_folder = $draft_folder;
   } else {
      $new_draft_folder =~ s/[\r|\n]//g;
   }
   return $new_draft_folder;
}

# default move to trash 
sub command24a {
   print "By default, should messages get moved to the trash folder?  You\n";
   print "can specify the default trash folder in option 3.  If this is set\n";
   print "to false, messages will get deleted immediately without moving\n";
   print "to the trash folder.\n";
   print "\n";
   print "Trash folder is currently: $trash_folder\n";
   print "\n";
   
   if ($default_move_to_trash eq "true") {
      $default_value = "y";
   } else {
      $default_value = "n";
   }
   print "By default, move to trash (y/n) [$WHT$default_value$NRM]: $WHT";
   $new_show = <STDIN>;
   if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
      $default_move_to_trash = "true";
   } else {
      $default_move_to_trash = "false";
   }
   return $default_move_to_trash;
}

# default move to sent 
sub command24b {
   print "By default, should messages get moved to the sent folder?  You\n";
   print "can specify the default sent folder in option 4.  If this is set\n";
   print "to false, messages will get sent an no copy will be made.\n";
   print "\n";
   print "Trash folder is currently: $sent_folder\n";
   print "\n";
   
   if ($default_move_to_sent eq "true") {
      $default_value = "y";
   } else {
      $default_value = "n";
   }
   print "By default, move to sent (y/n) [$WHT$default_value$NRM]: $WHT";
   $new_show = <STDIN>;
   if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
      $default_move_to_sent = "true";
   } else {
      $default_move_to_sent = "false";
   }
   return $default_move_to_sent;
}

# default save as draft
sub command24c {
   print "By default, should the save to draft option be shown? You can\n";
   print "specify the default drafts folder in option 5. If this is set\n";
   print "to false, users will not be shown the save to draft option.\n";
   print "\n";
   print "Drafts folder is currently: $draft_folder\n";
   print "\n";
   
   if ($default_move_to_draft eq "true") {
      $default_value = "y";
   } else {
      $default_value = "n";
   }
   print "By default, save as draft (y/n) [$WHT$default_value$NRM]: $WHT";
   $new_show = <STDIN>;
   if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
      $default_save_as_draft = "true";
   } else {
      $default_save_as_draft = "false";
   }
   return $default_save_as_draft;
}

# List special folders first 
sub command27 {
   print "SquirrelMail has what we call 'special folders' that are not\n";
   print "manipulated and viewed like normal folders.  Some examples of\n";
   print "these folders would be INBOX, Trash, Sent, etc.  This option\n";
   print "Simply asks if you want these folders listed first in the folder\n";
   print "listing.\n";
   print "\n";
   
   if ($list_special_folders_first eq "true") {
      $default_value = "y";
   } else {
      $default_value = "n";
   }
   print "\n";
   print "List first (y/n) [$WHT$default_value$NRM]: $WHT";
   $new_show = <STDIN>;
   if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
      $list_special_folders_first = "true";
   } else {
      $list_special_folders_first = "false";
   }
   return $list_special_folders_first;
}

# Show special folders color 
sub command28 {
   print "SquirrelMail has what we call 'special folders' that are not\n";
   print "manipulated and viewed like normal folders.  Some examples of\n";
   print "these folders would be INBOX, Trash, Sent, etc.  This option\n";
   print "wants to know if we should display special folders in a\n";
   print "color than the other folders.\n";
   print "\n";
   
   if ($use_special_folder_color eq "true") {
      $default_value = "y";
   } else {
      $default_value = "n";
   }
   print "\n";
   print "Show color (y/n) [$WHT$default_value$NRM]: $WHT";
   $new_show = <STDIN>;
   if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
      $use_special_folder_color = "true";
   } else {
      $use_special_folder_color = "false";
   }
   return $use_special_folder_color;
}

# Auto expunge 
sub command29 {
   print "The way that IMAP handles deleting messages is as follows.  You\n";
   print "mark the message as deleted, and then to 'really' delete it, you\n";
   print "expunge it.  This option asks if you want to just have messages\n";
   print "marked as deleted, or if you want SquirrelMail to expunge the \n";
   print "messages too.\n";
   print "\n";
   
   if ($auto_expunge eq "true") {
      $default_value = "y";
   } else {
      $default_value = "n";
   }
   print "Auto expunge (y/n) [$WHT$default_value$NRM]: $WHT";
   $new_show = <STDIN>;
   if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
      $auto_expunge = "true";
   } else {
      $auto_expunge = "false";
   }
   return $auto_expunge;
}

# Default sub of inbox 
sub command210 {
   print "Some IMAP servers (Cyrus) have all folders as subfolders of INBOX.\n";
   print "This can cause some confusion in folder creation for users when\n";
   print "they try to create folders and don't put it as a subfolder of INBOX\n";
   print "and get permission errors.  This option asks if you want folders\n";
   print "to be subfolders of INBOX by default.\n";
   print "\n";
   
   if ($default_sub_of_inbox eq "true") {
      $default_value = "y";
   } else {
      $default_value = "n";
   }
   print "Default sub of INBOX (y/n) [$WHT$default_value$NRM]: $WHT";
   $new_show = <STDIN>;
   if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
      $default_sub_of_inbox = "true";
   } else {
      $default_sub_of_inbox = "false";
   }
   return $default_sub_of_inbox;
}

# Show contain subfolder option 
sub command211 {
   print "Some IMAP servers (UW) make it so that there are two types of\n";
   print "folders.  Those that contain messages, and those that contain\n";
   print "subfolders.  If this is the case for your server, set this to\n";
   print "true, and it will ask the user whether the folder they are\n";
   print "creating contains subfolders or messages.\n";
   print "\n";
   
   if ($show_contain_subfolders_option eq "true") {
      $default_value = "y";
   } else {
      $default_value = "n";
   }
   print "Show option (y/n) [$WHT$default_value$NRM]: $WHT";
   $new_show = <STDIN>;
   if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
      $show_contain_subfolders_option = "true";
   } else {
      $show_contain_subfolders_option = "false";
   }
   return $show_contain_subfolders_option;
}

# Default Unseen Notify 
sub command212 {
   print "This option specifies where the users will receive notification\n";
   print "about unseen messages by default.  This is of course an option that\n";
   print "can be changed on a user level.\n";
   print "  1 = No notification\n";
   print "  2 = Only on the INBOX\n";
   print "  3 = On all folders\n";
   print "\n";
   
   print "Which one should be default (1,2,3)? [$WHT$default_unseen_notify$NRM]: $WHT";
   $new_show = <STDIN>;
   if ($new_show =~ /^[1|2|3]\n/i) {
      $default_unseen_notify = $new_show;
   }
   $default_unseen_notify =~ s/[\r|\n]//g;
   return $default_unseen_notify;
}

# Default Unseen Type 
sub command213 {
   print "Here you can define the default way that unseen messages will be displayed\n";
   print "to the user in the folder listing on the left side.\n";
   print "  1 = Only unseen messages   (4)\n";
   print "  2 = Unseen and Total messages  (4/27)\n";
   print "\n";
   
   print "Which one should be default (1,2)? [$WHT$default_unseen_type$NRM]: $WHT";
   $new_show = <STDIN>;
   if ($new_show =~ /^[1|2]\n/i) {
      $default_unseen_type = $new_show;
   }
   $default_unseen_type =~ s/[\r|\n]//g;
   return $default_unseen_type;
}

# Auto create special folders
sub command214 {
   print "Would you like the Sent, Trash, and Drafts folders to be created\n";
   print "automatically print for you when a user logs in?  If the user\n";
   print "accidentally deletes their special folders, this option will\n";
   print "automatically create it again for them.\n";
   print "\n";
   
   if ($auto_create_special eq "true") {
      $default_value = "y";
   } else {
      $default_value = "n";
   }
   print "Auto create special folders? (y/n) [$WHT$default_value$NRM]: $WHT";
   $new_show = <STDIN>;
   if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
      $auto_create_special = "true";
   } else {
      $auto_create_special = "false";
   }
   return $auto_create_special;
}


############# GENERAL OPTIONS #####################

# Default Charset
sub command31 {
   print "This option controls what character set is used when sending\n";
   print "mail and when sending HTML to the browser.  Do not set this\n";
   print "to US-ASCII, use ISO-8859-1 instead.  For cyrillic, it is best\n";
   print "to use KOI8-R, since this implementation is faster than most\n";
   print "of the alternatives\n";
   print "\n";

   print "[$WHT$default_charset$NRM]: $WHT";
   $new_default_charset = <STDIN>;
   if ($new_default_charset eq "\n") {
      $new_default_charset = $default_charset;
   } else {
      $new_default_charset =~ s/[\r|\n]//g;
   }
   return $new_default_charset;
}

# Data directory
sub command33a {
   print "It is a possible security hole to have a writable directory\n";
   print "under the web server's root directory (ex: /home/httpd/html).\n";
   print "For this reason, it is possible to put the data directory\n";
   print "anywhere you would like.  The path name can be absolute or\n";
   print "relative (to the config directory).  It doesn't matter.  Here\n";
   print "are two examples:\n";
   print "  Absolute:    /usr/local/squirrelmail/data/\n";
   print "  Relative:    ../data/\n";
   print "\n";

   print "[$WHT$data_dir$NRM]: $WHT";
   $new_data_dir = <STDIN>;
   if ($new_data_dir eq "\n") {
      $new_data_dir = $data_dir;
   } else {
      $new_data_dir =~ s/[\r|\n]//g;
   }
   if ($new_data_dir =~ /^\s*$/) {
      $new_data_dir = "";
   } else {
      $new_data_dir =~ s/\/*$//g;
      $new_data_dir =~ s/$/\//g;
   }   
   return $new_data_dir;
}

# Attachment directory
sub command33b {
   print "Path to directory used for storing attachments while a mail is\n";
   print "being sent.  There are a few security considerations regarding this\n";
   print "directory:\n";
   print "  1.  It should have the permission 733 (rwx-wx-wx) to make it\n";
   print "      impossible for a random person with access to the webserver\n";
   print "      to list files in this directory.  Confidential data might\n";
   print "      be laying around in there.\n";
   print "  2.  Since the webserver is not able to list the files in the\n";
   print "      content is also impossible for the webserver to delete files\n";
   print "      lying around there for too long.\n";
   print "  3.  It should probably be another directory than the data\n";
   print "      directory specified in option 3.\n";
   print "\n";

   print "[$WHT$attachment_dir$NRM]: $WHT";
   $new_attachment_dir = <STDIN>;
   if ($new_attachment_dir eq "\n") {
      $new_attachment_dir = $attachment_dir;
   } else {
      $new_attachment_dir =~ s/[\r|\n]//g;
   }
   if ($new_attachment_dir =~ /^\s*$/) {
      $new_attachment_dir = "";
   } else {
      $new_attachment_dir =~ s/\/*$//g;
      $new_attachment_dir =~ s/$/\//g;
   }   
   return $new_attachment_dir;
}

sub command33c {
   print "The directory hash level setting allows you to configure the level\n";
   print "of hashing that Squirremail employs in your data and attachment\n";
   print "directories. This value must be an integer ranging from 0 to 4.\n";
   print "When this value is set to 0, Squirrelmail will simply store all\n";
   print "files as normal in the data and attachment directories. However,\n";
   print "when set to a value from 1 to 4, a simple hashing scheme will be\n";
   print "used to organize the files in this directory. In short, the crc32\n";
   print "value for a username will be computed. Then, up to the first 4\n";
   print "digits of the hash, as set by this configuration value, will be\n";
   print "used to directory hash the files for that user in the data and\n";
   print "attachment directory. This allows for better performance on\n";
   print "servers with larger numbers of users.\n";
   print "\n";

   print "[$WHT$dir_hash_level$NRM]: $WHT";
   $new_dir_hash_level = <STDIN>;
   if ($new_dir_hash_level eq "\n") {
      $new_dir_hash_level = $dir_hash_level;
   } else {
      $new_dir_hash_level =~ s/[\r|\n]//g;
   }
   if ((int($new_dir_hash_level) < 0) || 
       (int($new_dir_hash_level) > 4) ||
       !(int($new_dir_hash_level) eq $new_dir_hash_level )) {
      print "Invalid Directory Hash Level.\n";
      print "Value must be an integer ranging from 0 to 4\n";
      print "Hit enter to continue.\n";
      $enter_key = <STDIN>;

      $new_dir_hash_level = $dir_hash_level;
   }

   return $new_dir_hash_level;
}

sub command35 {
   print "This is the default size (in pixels) of the left folder list.\n";
   print "Default is 200, but you can set it to whatever you wish.  This\n";
   print "is a user preference, so this will only show up as their default.\n";
   print "\n";
   print "[$WHT$default_left_size$NRM]: $WHT";
   $new_default_left_size = <STDIN>;
   if ($new_default_left_size eq "\n") {
      $new_default_left_size = $default_left_size;
   } else {
      $new_default_left_size =~ s/[\r|\n]//g;
   }
   return $new_default_left_size;
}


sub command36 {
   print "Some IMAP servers only have lowercase letters in the usernames\n";
   print "but they still allow people with uppercase to log in.  This\n";
   print "causes a problem with the user's preference files.  This option\n";
   print "transparently changes all usernames to lowercase.";
   print "\n";
   
   if ($force_username_lowercase eq "true") {
      $default_value = "y";
   } else {
      $default_value = "n";
   }
   print "Convert usernames to lowercase (y/n) [$WHT$default_value$NRM]: $WHT";
   $new_show = <STDIN>;
   if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
      return "true";
   }
   return "false";
}

sub command37 {
   print "";
   print "\n";
   
   if ($default_use_priority eq "true") {
      $default_value = "y";
   } else {
      $default_value = "n";
   }
   
   print "Allow users to specify priority of outgoing mail (y/n) [$WHT$default_value$NRM]: $WHT";
      $new_show = <STDIN>;
      if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
         return "true";
      }
      return "false";
}
							 

sub command38 {
   print "";
   print "\n";
   
   if ($default_hide_attribution eq "true") {
      $default_value = "y";
   } else {
      $default_value = "n";
   }
   
   print "Hide SM attributions (y/n) [$WHT$default_value$NRM]: $WHT";
      $new_show = <STDIN>;
      if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
         return "true";
      }
      return "false";
}


sub command41 {
   print "\nNow we will define the themes that you wish to use.  If you have added\n";
   print "a theme of your own, just follow the instructions (?) about how to add\n";
   print "them.  You can also change the default theme.\n";
   print "[theme] command (?=help) > ";
   $input = <STDIN>;
   $input =~ s/[\r|\n]//g;
   while ($input ne "d") {
      if ($input =~ /^\s*l\s*/i) {
         $count = 0;
         while ($count <= $#theme_name) {
            if ($count == $theme_default) {
               print " *";
            } else {
               print "  ";
            }
            $name = $theme_name[$count];
            $num_spaces = 25 - length($name);
            for ($i = 0; $i < $num_spaces;$i++) {
               $name = $name . " ";
            }
            
            print " $count.  $name";
            print "($theme_path[$count])\n";
            
            $count++;
         }
      } elsif ($input =~ /^\s*m\s*[0-9]+/i) {
         $old_def = $theme_default;
         $theme_default = $input;
         $theme_default =~ s/^\s*m\s*//;
         if (($theme_default > $#theme_name) || ($theme_default < 0)) {
            print "Cannot set default theme to $theme_default.  That theme does not exist.\n";
            $theme_default = $old_def;
         }
      } elsif ($input =~ /^\s*\+/) {
         print "What is the name of this theme: ";
         $name = <STDIN>;
         $name =~ s/[\r|\n]//g;
         $theme_name[$#theme_name+1] = $name;
         print "Be sure to put ../themes/ before the filename.\n";
         print "What file is this stored in (ex: ../themes/default_theme.php): ";
         $name = <STDIN>;
         $name =~ s/[\r|\n]//g;
         $theme_path[$#theme_path+1] = $name;
      } elsif ($input =~ /^\s*-\s*[0-9]?/) {
         if ($input =~ /[0-9]+\s*$/) {
            $rem_num = $input;
            $rem_num =~ s/^\s*-\s*//g;
            $rem_num =~ s/\s*$//;
         } else {
            $rem_num = $#theme_name;
         }
         if ($rem_num == $theme_default) {
            print "You cannot remove the default theme!\n";
         } else {
            $count = 0;
            @new_theme_name = ();
            @new_theme_path = ();
            while ($count <= $#theme_name) {
               if ($count != $rem_num) {
                  @new_theme_name = (@new_theme_name, $theme_name[$count]);
                  @new_theme_path = (@new_theme_path, $theme_path[$count]);
               }
               $count++;
            }
            @theme_name = @new_theme_name;
            @theme_path = @new_theme_path;
            if ($theme_default > $rem_num) {
               $theme_default--;
            }   
         }
      } elsif ($input =~ /^\s*t\s*/i) {
         print "\nStarting detection...\n\n";
         
         opendir(DIR, "../themes");
         @files = grep { /\.php$/i } readdir(DIR);
         $cnt = 0;
         while ($cnt <= $#files) {
            $filename = "../themes/" . $files[$cnt];
               if ($filename ne "../themes/index.php") {
               $found = 0;
               for ($x=0; $x <= $#theme_path; $x++) {
                  if ($theme_path[$x] eq $filename) {
                     $found = 1;
                  }
               }
               if ($found != 1) {
                  print "** Found theme: $filename\n";
                  print "   What is its name? ";
                  $nm = <STDIN>;
                  $nm =~ s/[\n|\r]//g;
                  $theme_name[$#theme_name+1] = $nm;
                  $theme_path[$#theme_path+1] = $filename;
               }
            }
            $cnt++;
         }
         print "\n";
         for ($cnt=0; $cnt <= $#theme_path; $cnt++) {
            $filename = $theme_path[$cnt];
            if (! (-e $filename)) {
               print "  Removing $filename (file not found)\n";
               $offset = 0;
               @new_theme_name = ();
               @new_theme_path = ();
               for ($x=0; $x < $#theme_path; $x++) {
                  if ($theme_path[$x] eq $filename) {
                     $offset = 1;
                  }
                  if ($offset == 1) {
                     $new_theme_name[$x] = $theme_name[$x+1];
                     $new_theme_path[$x] = $theme_path[$x+1];
                  } else {
                     $new_theme_name[$x] = $theme_name[$x];
                     $new_theme_path[$x] = $theme_path[$x];
                  }
               }
               @theme_name = @new_theme_name;
               @theme_path = @new_theme_path;
            }
         }
         print "\nDetection complete!\n\n";
         
         closedir DIR;  
      } elsif ($input =~ /^\s*\?\s*/) {
         print ".-------------------------.\n";
         print "| t       (detect themes) |\n";
         print "| +           (add theme) |\n";
         print "| - N      (remove theme) |\n";
         print "| m N      (mark default) |\n";
         print "| l         (list themes) |\n";
         print "| d                (done) |\n";
         print "`-------------------------'\n";
      }
      print "[theme] command (?=help) > ";
      $input = <STDIN>;
      $input =~ s/[\r|\n]//g;
   }
}   


# Theme - CSS file
sub command42 {
   print "You may specify a cascading style-sheet (CSS) file to be included\n";
   print "on each html page generated by SquirrelMail. The CSS file is useful\n";
   print "for specifying a site-wide font. If you're not familiar with CSS\n";
   print "files, leave this blank.\n";
   print "\n";
   print "To clear out an existing value, just type a space for the input.\n";
   print "\n";
   print "[$WHT$theme_css$NRM]: $WHT";
   $new_theme_css = <STDIN>;
   if ($new_theme_css eq "\n") {
      $new_theme_css = $theme_css;
   } else {
      $new_theme_css =~ s/[\r|\n]//g;
   }
   $new_theme_css =~ s/^\s*//;
   return $new_theme_css;
}


sub command61 {
   print "You can now define different LDAP servers.\n";
   print "[ldap] command (?=help) > ";
   $input = <STDIN>;
   $input =~ s/[\r|\n]//g;
   while ($input ne "d") {
      if ($input =~ /^\s*l\s*/i) {
         $count = 0;
         while ($count <= $#ldap_host) {
            print "$count. $ldap_host[$count]\n";
            print "        base: $ldap_base[$count]\n";
            if ($ldap_charset[$count]) {
               print "     charset: $ldap_charset[$count]\n";
            }
            if ($ldap_port[$count]) {
               print "        port: $ldap_port[$count]\n";
            }
            if ($ldap_name[$count]) {
               print "        name: $ldap_name[$count]\n";
            }
            if ($ldap_maxrows[$count]) {
               print "     maxrows: $ldap_maxrows[$count]\n";
            }
            print "\n";
            $count++;
         }
      } elsif ($input =~ /^\s*\+/) {
         $sub = $#ldap_host + 1;
         
         print "First, we need to have the hostname or the IP address where\n";
         print "this LDAP server resides.  Example: ldap.bigfoot.com\n";
         print "hostname: ";
         $name = <STDIN>;
         $name =~ s/[\r|\n]//g;
         $ldap_host[$sub] = $name;
         
         print "\n";

         print "Next, we need the server root (base dn).  For this, an empty\n";
         print "string is allowed.\n";
         print "Example: ou=member_directory,o=netcenter.com\n";
         print "base: ";
         $name = <STDIN>;
         $name =~ s/[\r|\n]//g;
         $ldap_base[$sub] = $name;

         print "\n";

         print "This is the TCP/IP port number for the LDAP server.  Default\n";
         print "port is 389.  This is optional.  Press ENTER for default.\n";
         print "port: ";
         $name = <STDIN>;
         $name =~ s/[\r|\n]//g;
         $ldap_port[$sub] = $name;

         print "\n";

         print "This is the charset for the server.  Default is utf-8.  This\n";
         print "is also optional.  Press ENTER for default.\n";
         print "charset: ";
         $name = <STDIN>;
         $name =~ s/[\r|\n]//g;
         $ldap_charset[$sub] = $name;

         print "\n";

         print "This is the name for the server, used to tag the results of\n";
         print "the search.  Default it \"LDAP: hostname\".  Press ENTER for default\n";
         print "name: ";
         $name = <STDIN>;
         $name =~ s/[\r|\n]//g;
         $ldap_name[$sub] = $name;

         print "\n";

         print "You can specify the maximum number of rows in the search result.\n";
         print "Default is unlimited.  Press ENTER for default.\n";
         print "maxrows: ";
         $name = <STDIN>;
         $name =~ s/[\r|\n]//g;
         $ldap_maxrows[$sub] = $name;

         print "\n";

      } elsif ($input =~ /^\s*-\s*[0-9]?/) {
         if ($input =~ /[0-9]+\s*$/) {
            $rem_num = $input;
            $rem_num =~ s/^\s*-\s*//g;
            $rem_num =~ s/\s*$//;
         } else {
            $rem_num = $#ldap_host;
         }
         $count = 0;
         @new_ldap_host = ();
         @new_ldap_base = ();
         @new_ldap_port = ();
         @new_ldap_name = ();
         @new_ldap_charset = ();
         @new_ldap_maxrows = ();
         while ($count <= $#ldap_host) {
            if ($count != $rem_num) {
               @new_ldap_host = (@new_ldap_host, $ldap_host[$count]);
               @new_ldap_base = (@new_ldap_base, $ldap_base[$count]);
               @new_ldap_port = (@new_ldap_port, $ldap_port[$count]);
               @new_ldap_name = (@new_ldap_name, $ldap_name[$count]);
               @new_ldap_charset = (@new_ldap_charset, $ldap_charset[$count]);
               @new_ldap_maxrows = (@new_ldap_maxrows, $ldap_maxrows[$count]);
            }
            $count++;
         }
         @ldap_host = @new_ldap_host;
         @ldap_base = @new_ldap_base;
         @ldap_port = @new_ldap_port;
         @ldap_name = @new_ldap_name;
         @ldap_charset = @new_ldap_charset;
         @ldap_maxrows = @new_ldap_maxrows;
      } elsif ($input =~ /^\s*\?\s*/) {
         print ".-------------------------.\n";
         print "| +            (add host) |\n";
         print "| - N       (remove host) |\n";
         print "| l          (list hosts) |\n";
         print "| d                (done) |\n";
         print "`-------------------------'\n";
      }
      print "[ldap] command (?=help) > ";
      $input = <STDIN>;
      $input =~ s/[\r|\n]//g;
   }
}   

sub command62 {
   print "Some of our developers have come up with very good javascript interface\n";
   print "for searching through address books, however, our original goals said\n";
   print "that we would be 100% HTML.  In order to make it possible to use their\n";
   print "interface, and yet stick with our goals, we have also written a plain\n";
   print "HTML version of the search.  Here, you can choose which version to use.\n";
   print "\n";
   print "This is just the default value.  It is also a user option that each\n";
   print "user can configure individually\n";
   print "\n";
   
   if ($default_use_javascript_addr_book eq "true") {
      $default_value = "y";
   } else {
      $default_use_javascript_addr_book = "false";
      $default_value = "n";
   }
   print "Use javascript version by default (y/n) [$WHT$default_value$NRM]: $WHT";
   $new_show = <STDIN>;
   if (($new_show =~ /^y\n/i) || (($new_show =~ /^\n/) && ($default_value eq "y"))) {
      $default_use_javascript_addr_book = "true";
   } else {
      $default_use_javascript_addr_book = "false";
   }
   return $default_use_javascript_addr_book;
}


sub save_data {
   $tab = "    ";
   open (CF, ">config.php");

   print CF "<?php\n";
   print CF "\n";

   print CF "/**\n";
   print CF " * SquirrelMail Configuration File\n";
   print CF " * Created using the configure script, conf.pl\n";
   print CF " */\n";
   print CF "\n";
   
   print CF "global \$config_version, \$config_use_color;\n";
   if ($print_config_version) {
      print CF "\$config_version = '$print_config_version';\n";
   }
   print CF "\$config_use_color = $config_use_color;\n"; 
   print CF "\n";

   print CF "global \$org_name, \$org_logo, \$org_title, \$signout_page;\n";
   print CF "\$org_name      = \"$org_name\";\n";
   print CF "\$org_logo      = '$org_logo';\n";
   print CF "\$org_title     = \"$org_title\";\n";
   print CF "\$signout_page  = '$signout_page';\n";
   print CF "\n";

   print CF "global \$motd;\n";
   print CF "\$motd = '$motd';\n";
   print CF "\n";

   print CF "global \$squirrelmail_default_language;\n";
   print CF "\$squirrelmail_default_language = '$squirrelmail_default_language';\n";
   print CF "\n";

   print CF "global \$domain, \$imapServerAddress, \$imapPort;\n";
   print CF "global \$useSendmail, \$smtpServerAddress, \$smtpPort;\n";
   print CF "global \$sendmail_path, \$use_authenticated_smtp;\n";
   print CF "global \$imap_server_type, \$invert_time;\n";
   print CF "global \$optional_delimiter;\n";
   print CF "\$domain                 = '$domain';\n";
   print CF "\$imapServerAddress      = '$imapServerAddress';\n";
   print CF "\$imapPort               = $imapPort;\n";
   print CF "\$useSendmail            = $useSendmail;\n";
   print CF "\$smtpServerAddress      = '$smtpServerAddress';\n";
   print CF "\$smtpPort               = $smtpPort;\n";
   print CF "\$sendmail_path          = '$sendmail_path';\n";
   print CF "\$use_authenticated_smtp = $use_authenticated_smtp;\n";
   print CF "\$imap_server_type       = '$imap_server_type';\n";
   print CF "\$invert_time            = $invert_time;\n";
   print CF "\$optional_delimiter     = '$optional_delimiter';\n";
   print CF "\n";

   print CF "global \$default_folder_prefix;\n";
   print CF "global \$trash_folder, \$default_move_to_trash;\n";
   print CF "global \$sent_folder, \$default_move_to_sent;\n";
   print CF "global \$draft_folder, \$default_save_to_draft;\n";
   print CF "global \$show_prefix_option, \$list_special_folders_first;\n";
   print CF "global \$use_special_folder_color, \$auto_expunge;\n";
   print CF "global \$default_sub_of_inbox;\n";
   print CF "global \$show_contain_subfolders_option;\n";
   print CF "global \$default_unseen_notify;\n";
   print CF "global \$default_unseen_type, \$auto_create_special;\n";
   print CF "\$default_folder_prefix          = '$default_folder_prefix';\n";
   print CF "\$trash_folder                   = '$trash_folder';\n";
   print CF "\$sent_folder                    = '$sent_folder';\n";
   print CF "\$draft_folder                   = '$draft_folder';\n";
   print CF "\$default_move_to_trash          = $default_move_to_trash;\n";
   print CF "\$default_move_to_sent           = $default_move_to_sent;\n";
   print CF "\$default_save_as_draft          = $default_save_as_draft;\n";
   print CF "\$show_prefix_option             = $show_prefix_option;\n";
   print CF "\$list_special_folders_first     = $list_special_folders_first;\n";
   print CF "\$use_special_folder_color       = $use_special_folder_color;\n";
   print CF "\$auto_expunge                   = $auto_expunge;\n";
   print CF "\$default_sub_of_inbox           = $default_sub_of_inbox;\n";
   print CF "\$show_contain_subfolders_option = $show_contain_subfolders_option;\n";
   print CF "\$default_unseen_notify          = $default_unseen_notify;\n";
   print CF "\$default_unseen_type            = $default_unseen_type;\n";
   print CF "\$auto_create_special            = $auto_create_special;\n";
   print CF "\n";

   print CF "global \$default_charset;\n";
   print CF "global \$data_dir, \$attachment_dir, \$dir_hash_level;\n";
   print CF "global \$default_left_size, \$force_username_lowercase;\n";
   print CF "global \$default_use_priority, \$hide_sm_attributions;\n";
   print CF "\$default_charset          = '$default_charset';\n";
   print CF "\$data_dir                 = '$data_dir';\n";
   print CF "\$attachment_dir           = \"$attachment_dir\";\n";
   print CF "\$dir_hash_level           = $dir_hash_level;\n";
   print CF "\$default_left_size        = $default_left_size;\n";
   print CF "\$force_username_lowercase = $force_username_lowercase;\n";
   print CF "\$default_use_priority     = $default_use_priority;\n";
   print CF "\$hide_sm_attributions     = $hide_sm_attributions;\n";
   print CF "\n";

   print CF "global \$plugins;\n";
   for ($ct=0; $ct <= $#plugins; $ct++) {
      print CF "\$plugins[$ct] = '$plugins[$ct]';\n";
   }
   print CF "\n";

   print CF "global \$theme_css, \$theme;\n";
   print CF "\$theme_css = '$theme_css';\n";
   for ($count=0; $count <= $#theme_name; $count++) {
      print CF "\$theme[$count]['PATH'] = '$theme_path[$count]';\n";
      print CF "\$theme[$count]['NAME'] = '$theme_name[$count]';\n";
   }
   print CF "\n";

   if ($default_use_javascript_addr_book ne "true") {
      $default_use_javascript_addr_book = "false";
   }   
   print CF "global \$default_use_javascript_addr_book, \$ldap_server;\n";
   print CF "\$default_use_javascript_addr_book = $default_use_javascript_addr_book;\n";
   for ($count=0; $count <= $#ldap_host; $count++) {
      print CF "\$ldap_server[$count] = array(\n";
      print CF "    'host' => '$ldap_host[$count]',\n";
      print CF "    'base' => '$ldap_base[$count]'";
      if ($ldap_name[$count]) {
         print CF ",\n";
         print CF "    'name' => '$ldap_name[$count]'";
      }
      if ($ldap_port[$count]) {
         print CF ",\n";
         print CF "    'port' => '$ldap_port[$count]'";
      }
      if ($ldap_charset[$count]) {
         print CF ",\n";
         print CF "    'charset' => '$ldap_charset[$count]'";
      }
      if ($ldap_maxrows[$count]) {
         print CF ",\n";
         print CF "    'maxrows' => '$ldap_maxrows[$count]'";
      }
      print CF "\n";
      print CF ");\n";
      print CF "\n";
   }

   print CF "/**\n";
   print CF " * Make sure there are no characters after the PHP closing\n";
   print CF " * tag below (including newline characters and whitespace).\n";
   print CF " * Otherwise, that character will cause the headers to be\n";
   print CF " * sent and regular output to begin, which will majorly screw\n";
   print CF " * things up when we try to send more headers later.\n";
   print CF " */\n";
   print CF "?>";

   close CF;
}

sub set_defaults {
   system "clear";
   print $WHT."SquirrelMail Configuration : ".$NRM;
   if ($config == 1) { print "Read: config.php"; }
   elsif ($config == 2) { print "Read: config_default.php"; }
   print "\n";
   print "---------------------------------------------------------\n";

   print "While we have been building SquirrelMail, we have discovered some\n";
   print "preferences that work better with some servers that don't work so\n";
   print "well with others.  If you select your IMAP server, this option will\n";
   print "set some pre-defined settings for that server.\n";
   print "\n";
   print "Please note that you will still need to go through and make sure\n";
   print "everything is correct.  This does not change everything.  There are\n";
   print "only a few settings that this will change.\n";
   print "\n";

   $continue = 0;
   while ($continue != 1) {
      print "Please select your IMAP server:\n";
      print "    cyrus      = Cyrus IMAP server\n";
      print "    uw         = University of Washington's IMAP server\n";
      print "    exchange   = Microsoft Exchange IMAP server\n";
      print "    courier    = Courier IMAP server\n";
      print "    quit       = Do not change anything\n";
      print "Command >> ";
      $server = <STDIN>;
      $server =~ s/[\r|\n]//g;

      print "\n";
      if ($server eq "cyrus") { 
         $imap_server_type = "cyrus";
         $default_folder_prefix = "";
         $trash_folder = "INBOX.Trash";
         $sent_folder = "INBOX.Sent";
         $draft_folder = "INBOX.Drafts";
         $show_prefix_option = false;
         $default_sub_of_inbox = true;
         $show_contain_subfolders_option = false;
         $optional_delimiter = ".";
         $disp_default_folder_prefix = "<none>";

         $continue = 1;
      } elsif ($server eq "uw") {
         $imap_server_type = "uw";
         $default_folder_prefix = "mail/";
         $trash_folder = "Trash";
         $sent_folder = "Sent";
         $draft_folder = "Drafts";
         $show_prefix_option = true;
         $default_sub_of_inbox = false;
         $show_contain_subfolders_option = true;
         $optional_delimiter = "/";
         $disp_default_folder_prefix = $default_folder_prefix;

         $continue = 1;
      } elsif ($server eq "exchange") {
         $imap_server_type = "exchange";
         $default_folder_prefix = "";
         $default_sub_of_inbox = true;
         $trash_folder = "INBOX/Deleted Items";
         $sent_folder = "INBOX/Sent Items";
         $drafts_folder = "INBOX/Drafts";
         $show_prefix_option = false;
         $show_contain_subfolders_option = false;
         $optional_delimiter = "detect";
         $disp_default_folder_prefix = "<none>";
         
         $continue = 1;
      } elsif ($server eq "courier") {
         $imap_server_type = "courier";
         $default_folder_prefix = "INBOX.";
         $trash_folder = "Trash";
         $sent_folder = "Sent";
         $draft_folder = "Drafts";
         $show_prefix_option = false;
         $default_sub_of_inbox = false;
         $show_contain_subfolders_option = false;
         $optional_delimiter = ".";
         $disp_default_folder_prefix = $default_folder_prefix;

         $continue = 1;
      } elsif ($server eq "quit") {
         $continue = 1;
      } else {
         $disp_default_folder_prefix = $default_folder_prefix;
         print "Unrecognized server: $server\n";
         print "\n";
      }

      print "              imap_server_type = $imap_server_type\n";
      print "         default_folder_prefix = $disp_default_folder_prefix\n";
      print "                  trash_folder = $trash_folder\n";
      print "                   sent_folder = $sent_folder\n";
      print "                  draft_folder = $draft_folder\n";
      print "            show_prefix_option = $show_prefix_option\n";
      print "          default_sub_of_inbox = $default_sub_of_inbox\n";
      print "show_contain_subfolders_option = $show_contain_subfolders_option\n";
      print "            optional_delimiter = $optional_delimiter\n";
   }   
   print "\nPress any key to continue...";
   $tmp = <STDIN>;
}
