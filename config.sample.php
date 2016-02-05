<?php
// Fill out these settings and rename this to config.php
// The config.php file is ignored by git (see .gitignore) so 
// your private data won't be included in the repository. 

date_default_timezone_set("Africa/Johannesburg");

define ("CONNECTION", "/path/to/connections/connection.php"); // See sample in connection folder
define ("URL", "http://localhost/openstreets/");
define ("LOCAL", true); // or FALSE when in production
define ("PRODUCTION", false); // or TRUE when local
define ("PATH", "/var/www/openstreets/");
define ("ENCODING", "UTF-8");
define ("SALT", "long-string-used-to-salt-hashes");
define ("SITENAME", "Open Streets Database");
define ("EMAIL", "automail@friends.openstreets.co.za");
define ("SYSADMIN", "sysadmin@friends.openstreets.co.za");

define ("MAILCHIMP_API_KEY", 'api-for-mailchimp');
define ("MAILCHIMP_LIST", 'list-id-from-mailchimp');

$client_id = 'google-id-for-google-API.apps.googleusercontent.com';
$client_secret = 'client-secret-provided-by-google-api';
?>
