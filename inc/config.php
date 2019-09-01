<?php
ini_set('max_execution_time', 2000); // increase this if the script needs more time (if you have a lot of farms)
define('TRAVIAN_SERVER','https://ts[X].travian.com');
define('TRAVIAN_USER','[Your_Username]');
define('TRAVIAN_PASSWORD','[Your_Password]');
define('MY_CITY_ID','123123'); // Your attacking from city. City id taken from rally point, not from city profile URL
define('TROUPS_AMOUNT',2); // by default base troup is sent: e.g: Romans: legionnaire
define('USER_AGENT','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.100 Safari/537.36');