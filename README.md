# Plesk-Mail-LetsEncrypt
Script to Secure Plesk Panel and mail.&lt;domain.tld> on Mail Services

Plesk does not Support SNI on Mailservices. Therefor we need ONE SSL Certificate which contains ALL possible subdomains used for client connections.

Lets assume all your customers are using mail.mydomain.tld to connect to ther Mailboxes. SSL is almost impossible on Apple devices and most clients.

This script installs ONE Certificate with all subdomains used for mail traffic and assigns it to the panel and the mail services.
The Hostname is included, so the panel is secured too.

If a customer wants to login to the Plesk Panel, he can also use https://mail.mydomain.tld:8443 and it is SSL Secured.
If you have a SSL certificate on the customers domain, he can use that as well.

This only works if you have less than 100 Domains on you server configured

# What the script does

The script fetches all domains configured on the server and then checks the DNS Records for the mail subdomain.
If the subdomain points to your IP with an A Record, the domain is included in the certificate process

When all domains are checked, the script uses the Lets Encrypt extension to generate a certificate for ALL mail domains on the server  pointing to the server IP with an A Record and assigns it to the mailserver.

All domain certificates exisiting on the server are NOT touched, so you dont have to worry breaking anything.

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
