<?php
/**
 * @author 	Dean Marshall - http://www.deanmarshall.co.uk/
 * @copyright 	2007 Dean Marshall 
 * @version 	2.0
 * @abstract 	
 *		Protects a Joomla powered site from 3 forms of abuse
 *		hack attempts, site rippers/rogue bots and robots.txt sniffers
 *		Take IP Addresses of guilty users and logs them, e-mails site admin. 
 *		Also adds IP address to .htaccess 'deny from' list automagically.
 *
 * Based heavily upon (and impossible without) the work of the Joomla team
 * and the work of  Rob Schley (RobS) in the Joomla Forums.

# =======================
#	Requirements: This block should be present at the end of the .htaccess file.
#	If you are using one of the pre-configured .htaccess files from 
#	http://www.deanmarshall.co.uk/ you are good to go.
# =======================
<Limit GET POST>
order allow,deny
allow from all
</Limit>


# =======================
#	Requirements: The bad bots / spiders block should be present in the .htaccess file.
#	If you are using one of the pre-configured .htaccess files from 
#	http://www.deanmarshall.co.uk/ you are good to go.
#	If you are rolling your own - see this page: http://www.deanmarshall.co.uk/<some-page-here>
# =======================


# =======================
#	Requirements: This block should be present in your robots.txt file.
# 	Note: you can just add this line (without quotes) 
#	Disallow: /bot-trap/ 
#	if you already have a suitable block in robots.txt
#	A robots.txt file with this line included is available from http://www.deanmarshall.co.uk/
# =======================

User-agent: * 
Crawl-delay: 3
Disallow: /bot-trap/

 ***
 *** This is the end of the setup notes / instructions 
 *** Enjoy: DeanMarshall - http://www.deanmarshall.co.uk/
 *** Don't forget to double-check the config variables below
 *** The defaults should work 'out of the box' for most people
 */

// Define locations of .htaccess file, output log file and the logfile format
$debug_this_script = 0;			// debugging  1 = on | 0 = off (default).
$htaccess = '.htaccess';		// name of apache control files - default = '.htaccess'
$logfile = 'hacker_beware_log.txt';	// filename for logging exploit attempts.

// if set this email address will receive alerts. 
//Leave blank to use mail contact from Joomla configuration.php file.
$email_override = '';

// Some users find they get a deluge of emails - this option (not recommended) will prevent this.
$suppress_email = 0;			// 0 = send emails as normal | 1 = don't send emails.

/********** 		No User Configurable Options Beyond This Point  *************
 **********		Do Not Mess - Here be dragons			************* 
 **********/


$reason = "hacking attempt";   // set the default reason for blocking access.

// extract 'reason' variable passed from .htaccess redirects.
$reason = $_GET['reason'];	// this probably needs sanitising - although it doesn't get output to screen??
switch ($reason)
{
	case 'badbot':
		$reason_string = "attempting to access folder deemed off limits in robots.txt";
		break;
	case 'useragent':
		$reason_string = "spider / bad bot / offline browser";
		break;
	default:
		$reason_string = "attempting to hack Joomla.";
		break;
}


$the_date = gmdate("M d Y");
$the_time = gmdate("H:i:s");

// Define the log entry.
$logentry = <<<DOWN_TO_HERE

Date:		{$the_date}
Time: 		{$the_time}
IP Address: 	{$_SERVER['REMOTE_ADDR']}
User Agent: 	{$_SERVER['HTTP_USER_AGENT']}
Request Method:	{$_SERVER['REQUEST_METHOD']}
Request URI: 	{$_SERVER['REQUEST_URI']}
Reason: 	{$reason}

DOWN_TO_HERE;

// Save the log entry to the logfile
file_save_contents($logfile, $logentry);


// Read in the .htaccess file, find </Limit> (case insensitive match

if(!file_exists($htaccess))
{
	// .htaccess file doesn't exist 
	die_friendly("Sorry you don't have a .htaccess file");	
}
if(! is_readable($htaccess))
{
	// file exists but can't be read.
	die_friendly("You have a .htaccess file but I can't read it");	
	
}
if(! is_writeable($htaccess))
{
	// file can't be written to - no point proceeding.
	die_friendly("You have a .htaccess file - I can read it, but I can't write to it.");	
}

$htaccess_contents = file_get_contents($htaccess);
if(!$htaccess_contents)
{
	// .htaccess wasn't successful
	die_friendly("Your .htaccess file was detected as being readable and writeable - but I failed to read it");	
}

// Okay - we've read in the .htaccess file 
// Insert new deny line before </Limit>
$htaccess_output = preg_replace('@</Limit>@i', "deny from {$_SERVER['REMOTE_ADDR']}\n</Limit>", $htaccess_contents);

// Save the updated .htaccess file back to the server.
file_save_contents($htaccess, $htaccess_output, false);

if(! $suppress_email)
{
	send_mail("Security Advisory: Your website just banned someone", "IP address {$_SERVER['REMOTE_ADDR']} has been banned from your site\r\n\t\tReason:  {$reason_string}\r\n\t\tTo counter this the following line has been added to the .htaccess file\r\n\t\tDeny from {$_SERVER['REMOTE_ADDR']}");
}



// Helper function - not all versions of php have file_put_contents - so write our own.

function file_save_contents($filename, $contents, $append=true){
	$method = ($append) ? 'a' : 'w';
	$fp = fopen($filename, $method);
	fwrite($fp, $contents);
	fclose($fp);
}

function die_friendly($error_msg){
	global $debug_this_script;
	if($debug_this_script){
		// only output to screen if debugging is active.
		echo "<h1>Sorry an error occured</h1><p>$error_msg</p>";
	}
	send_mail('Hacker Beware - Error', $error_msg);
	file_save_contents('hacker_beware_errorlog.txt', 'Hacking attempt from IP Address: ' . $_SERVER['REMOTE_ADDR']. "\n" . $error_msg);
	exit();	
}


function send_mail($subject, $message){


	// Send an e-mail
	if(file_exists('configuration.php')&& is_readable('configuration.php')){
		require_once('configuration.php');
		global $the_date, $the_time, $email_override;

	$email_template_top =<<<DOWN_TO_HERE
	This is a security alert from the automated 'Hacker Beware Script'
	installed on your Joomla powered website: 
		{$mosConfig_sitename}
		{$mosConfig_live_site}

		Date: {$the_date}	Time: {$the_time}

DOWN_TO_HERE;
		
		
	$email_template_bottom =<<<DOWN_TO_HERE
	
	Hacker Beware is a script from 
		Dean Marshall
		http://www.deanmarshall.co.uk/
			
DOWN_TO_HERE;


		// concatenate the email 'parts' and send
		$message = $email_template_top . "\t\t" . $message . "\r\n" . $email_template_bottom;

		// Additional headers
		$str_from = 'Hacker Beware Script' . ' <' . $mosConfig_mailfrom . '>';
		$additional_headers = "From: " . $str_from . "\r\n";


		// decide who should receive the e-mail: mailfrom address in configuration.php unless $email_override is set
		$email_recipient = ($email_override == '') ? $mosConfig_mailfrom : $email_override;



		// send the email.
		mail($email_recipient,  $subject, $message, $additional_headers);

		// Only uncomment the following line on my EXPRESS authority.
		//mail('hacker.beware@deanmarshall.co.uk',  $subject, $message, $additional_headers);

	}

}




?>