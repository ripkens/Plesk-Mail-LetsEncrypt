# Plesk-Mail-LetsEncrypt
Script to Secure Plesk Panel and mail.&lt;domain.tld> on Mail Services

Plesk does not Support SNI on Mailservices. Therefore we need ONE SSL Certificate which contains ALL possible subdomains used for client connections. (In this example, we use 'mail' as the subdomains all clients use for connections.)

Lets assume all your customers are using mail.customerdomain.tld to connect to Mailboxes. SSL is almost impossible on Apple devices and most clients because the SSL certificate ist probably only valid for the hostname assigned to the maschine.

This script installs ONE Certificate with all subdomains used for mail traffic and assigns it to the panel and the mail services.
The Hostname is also included as main domain, so the panel is also secured with this certificate.

If a customer wants to login to the Plesk Panel, he can use his mail domain SSL Secured.
If you have a SSL certificate on the customers domain, he can use that as well.

This only works if you have less than 100 Domains on you server configured which also have an A Record pointing to the servers IP.
Domains wich have no subdomain A Record are ignored!

To have the script include your domain, you have to have a Record for this subdomain on our DNS Servers!

# What the script does

The script reads all domains from the psa database which are configured on the server and then checks the DNS Records for the configured subdomain.

```define('MAIL_SUBDOMAIN', 'mail');```

If the subdomain points to your IP with an A Record, the domain is included in the certificate process. The script shows a colored output which makes it easy to see which domains are included, which have no A Record, or which are ponting to other servers.

When all domains are checked, the script uses the Lets Encrypt extension to generate a certificate for ALL mail domains on the server  which are pointing to the server IP with an A Record and assigns the certificate to the mailserver.

All domain certificates exisiting on the server are NOT touched, so you dont have to worry breaking anything on domain settings.

# Which Servers are supported

All Linux based Plesk Servers Version >= 12.5 with installed Lets Encrypt extension

## Get started

###### 1. Install Lets Encrypt extension

Use the Panel Extension Manager to install the Lets Encrypt extension

More Info: [Plesk Extension Lets Encrypt](https://www.plesk.com/extensions/letsencrypt/)

###### 2. Create subscription for your Hostname

If your Hostname is 'web.example.com', create a subscription with the name 'web.example.com'.

More Info: [Create Subscriptions](https://docs.plesk.com/en-US/onyx/administrator-guide/customers-and-resellers/hosting-plans-and-subscriptions/managing-subscriptions.65125/)

###### 3. Set default Subscription for IP

In Plesk Panel, configure your IP to use the created subscription as default website for unknown domains

More Info: [Assign the default website for an IP address](https://docs.plesk.com/en-US/onyx/administrator-guide/server-administration/ip-addresses-management.59410/)

###### 4. Find the document root on the server via SSH

The Dokument root is probably '/var/www/vhosts/web.example.com/httpdocs'
Change web.example.com with your hostname.

###### 5. Download the Script

Download the script and copy it onto your server, its a php file which will be called via ssh

```wget https://raw.githubusercontent.com/ripkens/Plesk-Mail-LetsEncrypt/master/Plesk-Mail-LetsEncrypt.php```

###### 6. Configure the Script !!!!!!THIS IS IS IMPORTANT!!!!!!

Change the following defines at the top of the script:

```
define('IP', '1.2.3.4');
define('HOSTNAME', 'web.example.com');
define('LEMAIL', 'admin@example.com');
define('MYSQL_DB', 'psa');
define('PLESK_ADMIN', 'admin');
define('MAIL_SUBDOMAIN', 'mail');
define('DEFAULT_IP_VHOST', '/var/www/vhosts/web.example.com/httpdocs')
```

- IP = IP Adress of the Maschine used for outside connections to SMTP,IMAP,POP
- HOSTNAME = The Hostname of the maschine
- LEMAIL = The Mail adress of an admin to receive update mails form Lets Encrypt
- MYSQL_DB = the Database name of the Plesk Panel (defaults to 'psa')
- PLESK_ADMIN = The Admin user name (defaults to 'admin')
- MAIL_SUBDOMAIN = The subdomain used for client connections
- DEFAULT_IP_VHOST = The document root of the default subscription for that IP

###### 7. Run the Script

Make sure you have completed all steps above!!!!!!!
In the directory where the script resides, call it ...

```
php Plesk-Mail-LetsEncrypt.php
```

###### 7. Check the results

If the certificate is assigned to the mailservices but not to the panel, you can do that manually.
If you rerun the script later, you have to rename the certificate "EMail & Panel" to something else or you will receive an error and the certificates name will be 'Lets Encrypt certificate' which is uninformant and useless (you can rename it though).

You can also set the new certificate as standard certificate and remove all other certificates.
Remember: Domain certificates are UNTOUCHED and still needs to be created and assigned manually!
