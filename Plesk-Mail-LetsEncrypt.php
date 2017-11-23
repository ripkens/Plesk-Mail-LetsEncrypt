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
while($row = mysqli_fetch_assoc($res))
{
    echo "($i / $max) | " . $row['name']."\n";
    $data = dns_get_record(MAIL_SUBDOMAIN . '.' . $row['name'], DNS_A);
    foreach($data as $record)
    {
        if($record['ip'] == IP)
        {
            $arr_mail[] = MAIL_SUBDOMAIN . '.' . $row['name'];
        }
    }
    $i++;
}

echo "\n\n";
echo "Creating Certificate and assign it to Plesk Panel\n";
shell_exec('/usr/local/psa/bin/extension --exec letsencrypt cli.php --secure-plesk -w "'.DEFAULT_IP_VHOST.'" -m "'.LEMAIL.'" -d ' . implode(' -d ', $arr_mail)));
echo "Rename Certificate\n";
shell_exec('plesk bin certificate --update "Lets Encrypt certificate" -new-name "EMail & Panel"  -admin');
echo "Assign Certificate to MailServer\n";
shell_exec('plesk bin mailserver --set-certificate "EMail & Panel"');
echo "DONE\n";
