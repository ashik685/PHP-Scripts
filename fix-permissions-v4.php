<html>
<head>
</head>
<body>
<h1>Fix SugarCRM file and directory permissions...  <!- v4 Adapted by Chris Coleman www.ESPACEnetworks.com --> </h1>
<pre>
<?php
error_reporting(E_ALL | E_STRICT | E_NOTICE);
// Stop if not running from the Sugar main directory.
include dirname(__FILE__).'/config.php';
include dirname(__FILE__).'/config_override.php';
if (!isset($sugar_config))
{
    echo("Halting-\nYou must move this script to the SugarCRM main directory and run it from there-\n");
    echo("For example, on CentOS Linux, and your Sugar instance is accessed in your browser at http://www.yourcompany.com/crm\n");
    echo("then copy this script to /home/yourcompany/public_html/crm , and\n");
    echo('browse to http://www.yourcompany.com/crm/'.basename(__FILE__)."\n\n");
    exit;
}
// Purpose: Allows SugarCRM to run correctly on a Linux shared web hosting acount
// by fixing file permissions and owernship.
//
// Credits: Adapted by Chris Coleman of www.ESPACEnetworks.com
//
// Directions:
// 1) Save this file in the base folder of your SugarCRM installation.
// Example: Your company's SugarCRM instance is at the URL http://www.mycompany.com/crm
// SugarCRM files are installed on the Linux shared host on the folder: public_html/crm 
// So save this file at the location: public_html/crm/fix-permissions.php
// 2) To run this file, view the corresponding URL in your browswer:
//  http://www.mycompany.com/crm/fix-permissions.php
//
// -Chris Coleman
// Questions or feedback
// www.espacenetworks.com
/**
 * get execution time in seconds at current point of call in seconds
 * @return float Execution time at this point of call
 */
function get_execution_time()
{
    static $microtime_start = null;
    if($microtime_start === null)
    {
        $microtime_start = microtime(true);
        return 0.0; 
    }    
    return microtime(true) - $microtime_start; 
}

/**
 * Check is a PHP function enabled.
 * We require some functions to be enabled to succeed at setting permissions and ownership.
 * chmod, chgrp, chown, posix_getpwuid, posix_getgrgid, opendir, readdir, islink, php_sapi_name, clearstatcache
 *
 * @param $f
 * @return bool
 *
 */
function is_func_enabled($f)
{
    if($f=='ini_get')return@ini_get('a')===false;
    return(($l=@ini_get('disable_functions'))===null||!is_callable($f)||!function_exists($f)||!in_array($f,array_walk('trim',explode(',',$l)));
}
//Usage example:
//print_r(is_func_enabled('str_split'));//true or null if ini_get() is disabled

if ( ( (is_func_enabled('chmod')) && (is_func_enabled('chgrp')) && (is_func_enabled('chown')) ) != true)
{
    echo("Halting. chmod, chown, and chgrp functions are required for this module to work correctly, but they're disabled.")
    exit();
}

function get_path_owner_name($path)
{
	$owner = posix_getpwuid(fileowner($path));
	$owner_name = $owner['name'] ;
	return $owner_name;
}

function get_path_group_name($path)
{
	$group = posix_getpwuid(filegroup($path));
	$group_name = posix_getgrgid($group) ;
	return $group_name;
}

function get_path_permissions_decimal($path)
{
	$perms = fileperms($path);
	return ($perms & 07777);	// 07777
}

function is_permission_different($current_perm, $new_perm)
{
	$diff = ( $current_perm != $new_perm ) ? true : false;
	return $diff;
}

function chmod_R($path, $filemode = 0644, $dirmode = 0755, $print_modified_items = false) { 
    $old_perm = get_path_permissions_decimal($path);
    $old_perm_str = decoct($old_perm);
    if (is_dir($path))
    {
        if (is_permission_different( $old_perm, $dirmode) )
        {
          $dirmode_str=decoct($dirmode); 
          if (( $path != '.' ) && (!chmod($path, $dirmode))) { 
              print "FAILED modifying 0$old_perm_str to 0$dirmode_str on directory $path\n"; 
              print "  The directory $path will be skipped from recursive chmod\n"; 
              return;
          }
          else {  // chmod on dir succeeded.
            if ($print_modified_items)
            {
              print "Modified 0$old_perm_str to 0$dirmode_str on dir $path\n";
            }
          }
        }
        $dh = opendir($path); 
        while (($file = readdir($dh)) !== false) { 
            if(($file != '.') && ($file != '..')) {  // skip self and parent pointing directories 
                $fullpath = $path.'/'.$file; 
                chmod_R($fullpath, $filemode,$dirmode, true); 
            } 
        }
        closedir($dh);
    } else {
        if (is_link($path)) {
            print "link $path is skipped\n";
            return;
        }
        if (is_permission_different( $old_perm, $filemode) ) {
          $filemode_str=decoct($filemode);
          if (!chmod($path, $filemode)) {
            print "FAILED modifying 0$old_perm_str to 0$filemode_str on file $path\n";
            return;
          }
          if ($print_modified_items)
          {
            print "Modified 0$old_perm_str to 0$filemode_str on file $path\n";
          }
        }
    }
}

//Recursive chown chgrp.  
//Syntax: chown_chgrp_R ("uploads", "unsider", "unsider") ; 
function chown_chgrp_R($mypath, $uid, $gid) 
{
    $d = opendir ($mypath) ;
    while(($file = readdir($d)) !== false) {
        if (($file != ".") && ($file != "..")) {
            $typepath = $mypath . "/" . $file ;
            //print $typepath. " : " . filetype ($typepath). "<BR>" ; 
            if (filetype ($typepath) == 'dir') { 
                chown_chgrp_R ($typepath, $uid, $gid); 
            }
            if (!chown($typepath, $uid)) { echo "\nERROR chown $typepath to $uid\n"; }
		    clearstatcache();
            if (!chgrp($typepath, $gid)) { echo "\nERROR chgrp $typepath to $gid\n"; }
		    clearstatcache();
        }
    }
 }

function get_php_mode() {
	$sapi_type = php_sapi_name();
	$php_mode = 'undefined';
	if (stripos($sapi_type, 'cgi') !== false) {
		//echo "You are using CGI PHP\n";
		$php_mode = 'cgi';
	} else if (stripos($sapi_type, 'cli') !== false) {
		$php_mode = 'cli';
	} else {
		//echo "You are using DSO PHP\n";
		$php_mode = "dso";
	}
	return $php_mode;
}

get_execution_time();	//set start point to now.

$current_user = get_current_user();
echo ' Current user: ' . $current_user . '<br/>';
//$current_group = get_current_group();
//echo ' Current group: ' . $current_group . '<br/>';
$processUser = posix_getpwuid(posix_geteuid());
$processUserName = $processUser['name'];
$processGroupID= $processUser['gid'];
$processGroup=posix_getgrgid($processGroupID);
$processGroupName=$processGroup['name'];
echo 'Process user name: '. $processUserName .'<br/>';
echo 'Process group name:'. $processGroupName.'<br/>';

$php_mode = get_php_mode();
echo 'PHP mode: '. $php_mode .'<br/>';
if ($php_mode == 'cli')
{
    echo('Halting- running from command line unsupported.\Must run via browser. Copy this script to SugarCRM web server main directory\n');
    echo('Typical location, on Debian/Ubuntu: /home/(accoutname)/public_html\n on CentOS/Redhat: /var/www or /home/(accoutname)/public_html/(crm) \nReplace (accountname) with the login name for your hosting account, (crm) with name of your SugarCRM directory.\n');
    exit;
}

if ($php_mode != 'cgi')
{
    echo("Halting- this script should be run from a browser pointing to apache web server running SugarCRM in PHP CGI mode\n");
    exit;
}

echo("Verified running PHP CGI mode, from SugarCRM main directory.\n");

// FIX for bug with SUGAR CLEAN INSTALL on shared hosting: cache, custom, data, modules, and upload permissions and ownership are blank. Should be standard.
echo"<h2>Checking for PHP running in a mode typical of shared hosting- if found, fix empty ownership of 5 folders.</h2>";
if ($php_mode == 'cgi') {
	echo("Shared hosting (PHP CGI mode) detected. Fixing empty permissions and ownership of 5 folders: cache, custom, data, modules, upload... ");

	$file_perm = 0664;	// was 0664 worked. 0660/0775 failed. 0660/02775 failed.
	$dir_perm = 02775;	// was 02775/0664 worked. 02770/0664 fails.  0775/0664 seems to work.  02755/0644 seems work - 

	echo "<br/>Fixing cache dir (all files and subdirs)<br/>";
	chmod_R ( "./cache", $file_perm, $dir_perm  );
	echo "<br/>Fixing custom dir (all files and subdirs)<br/>";
	chmod_R ( "./custom", $file_perm, $dir_perm  );
	echo "<br/>Fixing data dir (all files and subdirs)<br/>";
	chmod_R ( "./data", $file_perm, $dir_perm  );
	echo "<br/>Fixing modules dir (all files and subdirs)<br/>";
	chmod_R ( "./modules", $file_perm, $dir_perm  );
	echo "<br/>Fixing upload dir (all files and subdirs) - new, moved to base Sugar directory as of Sugar 6.5<br/>";
	chmod_R ( "./upload", $file_perm, $dir_perm  );

	chown_chgrp_R ("cache", $current_user, $current_user ) ;
	chown_chgrp_R ("custom", $current_user, $current_user ) ;
	chown_chgrp_R ("data", $current_user, $current_user ) ;
	chown_chgrp_R ("modules", $current_user, $current_user ) ;
	chown_chgrp_R ("upload", $current_user, $current_user ) ;
	echo("done.\n\n");
}

// Phase I: 644 all files, 755 all folders.

$file_perm = 0644;
$dir_perm = 0755;
echo "<h2>Phase I: Permissions. ". decoct($file_perm) ." all files, ". decoct($dir_perm) ." all folders</h2>Fixing recursively.<br/>";
chmod_R ( ".", $file_perm, $dir_perm, true );

// Phase II: 664 files, 775 folders.

$file_perm = 0664;	// was 0664 worked. 0660/0775 failed. 0660/02775 failed.
$dir_perm = 02775;	// was 02775/0664 worked. 02770/0664 fails.  0775/0664 seems to work.  02755/0644 seems work - 
//To be sure it works- gotta test a clean install, document uploads, upgrades, downloads, logo uploads, email attachments.
echo "<h2>Phase II: Special permissions. Some files " . decoct($file_perm) . " and some folders " . decoct($dir_perm) . ".</h2>Fixing config.php<br/>";
chmod_R ( "./config.php", 0640, $dir_perm );
echo "<br/>Fixing config_override.php<br/>";
chmod_R ( "./config_override.php", $file_perm, $dir_perm  );
echo "<br/>Fixing sugarcrm.log<br/>";
chmod_R ( "./sugarcrm.log", $file_perm, $dir_perm  );
echo "<br/>Fixing cache dir (all files and subdirs)<br/>";
chmod_R ( "./cache", $file_perm, $dir_perm  );
echo "<br/>Fixing custom dir (all files and subdirs)<br/>";
chmod_R ( "./custom", $file_perm, $dir_perm  );
echo "<br/>Fixing data dir (all files and subdirs)<br/>";
chmod_R ( "./data", $file_perm, $dir_perm  );
echo "<br/>Fixing modules dir (all files and subdirs)<br/>";
chmod_R ( "./modules", $file_perm, $dir_perm  );
echo "<br/>Fixing upload dir (all files and subdirs) - new, moved to base Sugar directory as of Sugar 6.5<br/>";
chmod_R ( "./upload", $file_perm, $dir_perm  );

//Phase III: Fix settings inside ./config.php and ./include/utils.php

echo "<h2>Phase III: Do the following manually.  <br/>
Add this to<br/></h2>
<h3> ./config_override.php</h3>";

// out of the box sugar 6.x default permissions setting 493 (0755) dirs, 420 (0644) files.
// fresh install sugar 6.x default permissions setting 1517 (02755) dirs, 420 (0644) files.
// more relaxed permissions would be 1533 (02775) dirs, 436 (0664) files.

$fixed_config = "\$sugar_config['default_permissions']['dir_mode'] = 0" . decoct($dir_perm) . ";\n";
$fixed_config .="\$sugar_config['default_permissions']['file_mode'] = 0" . decoct($file_perm) . ";\n";
$fixed_config .="\$sugar_config['default_permissions']['user'] = '';\n"; //'$current_user'
$fixed_config .="\$sugar_config['default_permissions']'group'] = '';\n"; //'$current_user'
echo $fixed_config;  

echo "<br/>Done.<br/>";
echo "<br/>Execution time: ".get_execution_time()." seconds.<br/>"
?>
</pre>
</body>
</html>
