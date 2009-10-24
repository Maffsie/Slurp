<?php
//CONFIG FILE
//Configuration variables for Slurp!
//Set your username, password, host and database name. Tables should be left the same
//Unless you know they're set different in your database.

define('DB_USR','Database_Username'); #Database Username
define('DB_PASS','Database_Password'); #Database Password
define('DB_HOST','Database_Host'); #Database Host, it's unlikely you need to change this
define('DB_NAME','Database_Name'); #Database Name
define('TB_MAIN','Table_Main_Name'); #Name for main Slurp table
define('TB_USRS','Table_Users_Name'); #Username/Passwords for users
define('TB_TMP','Table_Temp_Name'); #Pending requests for users
define('URL_LEN','URL_Length'); #Length of URLs to produce
define('COOKIE_DATA','Cookie_Data') #Data to set in the cookie when logging in.
define('BASE_URL','Base_Site_URL'); #Base URL of your site. This should, ideally, be YourDomain.com. Don't put any slashes in, and only put a www. in if it is necessary.


//Mailer and registration settings
define('REG_APP',false); #Enable or disable manual registration approving
define('SMTP_HOST','SMTP_Host'); #SMTP Host
define('SMTP_PORT',25); #SMTP Port, leave this as it is unless you know it should be different.
define('SMTP_FROM','SMTP_From_Address'); #Email address to send from
define('SMTP_FROMNAME','SMTP_From_Friendly_Name'); #'Friendly' name for this email address
define('SMTP_AUTH',false); #Whether to use authentication or not
define('MAIL_NAME','SMTP_Owner_Name'); #Your name
define('MAIL_EMAIL','SMTP_Owner_Email'); #Your email
?>