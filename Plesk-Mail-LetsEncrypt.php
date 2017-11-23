<?php
define('IP', '1.2.3.4');
define('HOSTNAME', 'web.example.com');
define('LEMAIL', 'admin@example.com');
define('MYSQL_DB', 'psa');
define('PLESK_ADMIN', 'admin');
define('MAIL_SUBDOMAIN', 'mail');
define('DEFAULT_IP_VHOST', '/var/www/vhosts/web.example.com/httpdocs');

############################################################
$password = trim(file_get_contents('/etc/psa/.psa.shadow'));
$db = mysqli_connect('localhost', PLESK_ADMIN, "$password") or die("\nUsername or password incorrect!\n\n");
mysqli_select_db($db, 'psa');

$res = mysqli_query($db, "SELECT name from domains where name != '".HOSTNAME."' order by name ASC");

$arr_mail = array(HOSTNAME);

echo "Fetching Domain DNS Records.\n";

$max = mysqli_num_rows($res);
$i = 1;
echo " \033[0;37m";
while($row = mysqli_fetch_assoc($res))
{
    echo "($i / $max) | " . $row['name'];
    $data = dns_get_record(MAIL_SUBDOMAIN . '.' . $row['name'].'.', DNS_A);
    if(count($data) == 0)
    {
        echo " \033[0;31mNO A RECORD FOR " . MAIL_SUBDOMAIN . "\n";
        echo " \033[0;37m";
        continue;
    }
    $found = false;
    foreach($data as $record)
    {
        if($record['ip'] == IP)
        {
            $arr_mail[] = MAIL_SUBDOMAIN . '.' . $row['name'];
            $found = true;
        }
    }
    if($found)
    {
        echo " \033[0;32OK\n";
        echo " \033[0;37m";
    }
    else
    {
        echo " \033[1;33mPOINTING TO OTHER SERVER\n";
        echo " \033[0;37m";
    }
    $i++;
}

if(count($arr_mail) > 100)
{
    echo "Can not create certificate for ".count($arr_mail)." domains. 100 is the maximum allowd\n\n";
    die;
}

echo "\n\n";
echo "Creating Certificate for ".count($arr_mail)." domains and assign it to Plesk Panel\n";
shell_exec('/usr/local/psa/bin/extension --exec letsencrypt cli.php --secure-plesk -w "'.DEFAULT_IP_VHOST.'" -m "'.LEMAIL.'" -d ' . implode(' -d ', $arr_mail));
echo "Rename Certificate\n";
shell_exec('plesk bin certificate --update "Lets Encrypt certificate" -new-name "EMail & Panel"  -admin');
echo "Assign Certificate to MailServer\n";
shell_exec('plesk bin mailserver --set-certificate "EMail & Panel"');
echo "DONE\n";
