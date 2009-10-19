<?php
//CONFIG FILE
//Configuration variables for Slurp!
//Set your username, password, host and database name. Tables should be left the same
//Unless you know they're set different in your database.

define('DB_USR','username'); #Database Username
define('DB_PASS','password'); #Database Password
define('DB_HOST','localhost'); #Database Host, it's unlikely you need to change this
define('DB_NAME','slurp'); #Database Name
define('TB_MAIN','slurp'); #Name for main Slurp table
define('TB_USRS','slurp_usr'); #Username/Passwords for users
define('TB_TMP','slurp_reqs'); #Pending requests for users
define('URL_LEN',4); #Length of URLs to produce
define('COOKIE_DATA','kjdfkjdfskjljJKFHAJDHASru878^£%$&(*953uisghdkj5iy454HFKJH') #Data to set in the cookie when logging in.
define('BASE_URL','somewhere.com'); #Base URL of your site. This should, ideally, be YourDomain.com. Don't put any slashes in, and only put a www. in if it is necessary.


//Mailer settings
define('SMTP_HOST','smtp.yourinternet.com'); #SMTP Host
define('SMTP_PORT',25); #SMTP Port, leave this as it is unless you know it should be different.
define('SMTP_FROM','mailerbot@yourweb.com'); #Email address to send from
define('SMTP_FROMNAME','The Email Man'); #'Friendly' name for this email address
define('SMTP_AUTH',false); #Whether to use authentication or not
define('MAIL_NAME','I am the owner'); #Your name
define('MAIL_EMAIL','someone@theinternet.com'); #Your email
?>