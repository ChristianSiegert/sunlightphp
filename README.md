# SunlightPHP

## Introduction

Hello there,

thanks for downloading SunlightPHP. I put many months of work into this small
and fast PHP framework. I created it specifically for my needs, it is not an
all-rounder and might not be suited for your purposes. Try it and find out.


## Features

* **MVC pattern.** You have controllers, components, models, views and helpers.
* **Support for CouchDB.** Even with validation of document fields.
* **Support for CLI.** Start scripts via command line. Great for cronjobs.  
* **Asset compression.** Your CSS and JS files can be merged  automatically into one file each. Afterwards they are compressed.
* **Asset caching.** Static files like images, stylesheets and scripts automatically receive appropriate caching headers so browsers do not ask for them over and over again. If you update your CSS or JS, just change the URL salt in app/config/core.php and the files will look new to browsers. To sum up: Your files are always cached by browsers but never outdated.
* **Whitespace removal.** Whitespace between HTML tags is removed.
* **Namespaces.** Name clashes are a thing of the past.
* **Autoloader.** Classes are automatically included the first time they are used.
* **Small code base, no magic.** I write code for humans and try to keep it as straight forward as possible.

## Requirements

To run SunlightPHP you need:

* Apache 2
* CouchDB
* PHP 5.3
	* cli module
	* curl module
	* intl module

You can install them in one go with this command:

	sudo apt-get install apache2 couchdb php5 php5-cli php5-curl php5-intl


If you want to execute unit tests, you also need PHPUnit:

	sudo apt-get install phpunit


## Installation

### Step 1
	
SunlightPHP needs some Apache modules to be enabled. Issue this command:

	sudo a2enmod deflate dir expires headers rewrite
		

### Step 2

Copy SunlightPHP to the location of your choice. Make sure it is within the document root so Apache can access it. Now check if the server is set up correctly by browsing to:

	http://example.com/sunlightphp/maintenance/maintenance.php
	
Replace "example.com" with your server's address. If you try out SunlightPHP locally, it is probably "localhost". If you changed the folder name or nested SunlightPHP differently, adjust the path accordingly.
	

### Step 3

Open `app/config/core.php` and adjust the settings to suit your needs. You should be good to go with the default values though. You can tweak them later.


### (Optional) Step 3.1: Asset compression
	
If you enable CSS and/or JS compression, you need to download the YUICompressor from http://developer.yahoo.com/yui/compressor/ and place it in `app/vendors/yuicompressor/yuicompressor-2.4.2.jar`

You will also need Java for this:

	sudo apt-get install default-jre
	

### (Optional) Step 3.2: Caching with APC
	
If you enable caching with APC, you need to have the APC module for PHP installed:

	sudo apt-get install php-apc
	
	
### (Optional) Step 3.3: Caching with memcached
	
If you enable caching with Memcached, you need to have memcached and the memcache module for PHP installed:

	sudo apt-get install memcached php5-memcache


## Tips

### Securing PHP
	
It is not a magic solution that makes PHP completely bomb-proof but it makes it at least harder for attackers:

	sudo apt-get install php5-suhosin
		
	
### Tuning Apache
	
In the default configuration, Apache tries to appeal to everyone. This means lax settings and performance penalties.

#### Disabling .htaccess
	
Apache looks in each directory for an .htaccess file. Disable this behavior by editing your VirtualHost configuration file and change "AllowOverride All" to "AllowOverride None".
		
Make sure to create a VirtualHost configuration file. I created an example config that I use as a template for my projects. You can find it in `app/documentation/apache_example_config`.
	
#### Decreasing connection timeouts

High traffic websites are choked to death by Apache's default handling of connections. Remove this bottleneck by setting the KeepAliveTimeout to "KeepAliveTimeout 2". This means that if a client is not sending a new request within two seconds after a previous request, the connection is closed. Apache can then use the freed connection to communicate with another client.
		
This setting can be found in `/etc/apache2/apache2.conf`.
	
#### Muting Apache	

Give attackers as little information as possible about your system. Turn off Apache's detailed response about its version and installed modules by changing "ServerTokens Full" to "ServerTokens Prod".
		
This setting can be found in `/etc/apache2/conf.d/security.conf`.


### Tuning APC
	
Not configured correctly, APC will slow your server down horribly. For a huge big performance boost, change the APC settings to this:
	
	; Size of the cache in MB. Give APC as much RAM as you can spare but not
	; too much or else you cause swapping. Swapping means hard-disk access
	; which is very slow and defies the purpose of using a RAM-based cache.
	apc.shm_size = 1200
	
	; Check on each request if script files have been updated. Possible
	; values:
	;	0 (disable, recommended for production environment)
	;	1 (enable, recommended for development environment)
	apc.stat = 0
	
	; If cache is full, only remove entries that have been stored longer in
	; it than the values given. Values are in seconds.
	;
	; Clearing the complete cache is better or else you will continue with a
	; heavily fragmented cache.
	;
	; apc.ttl affects the system cache which contains cached script files.
	; apc.user_ttl affects the user cache which contains user-stored values.
	apc.ttl = 0
	apc.user_ttl = 0
		
The APC configuration file is located at `/etc/php5/conf.d/apc.ini`.
	

### Remotely accessing CouchDB

If you want to access CouchDB on a remote server, e.g. for administration with the pretty front-end Futon, you can make it accessible with:

	ssh -L {1}:localhost:{2} {3}@{4}

	{1}	The port on your localhost you want to bind the CouchDB from the
		remote server to.
	{2}	The port CouchDB occupies on your remote server. CouchDB listens by
		default to port 5984.
	{3} Your username for the remote server.
	{4} The IP address of your remote server.
		
With all the placeholders replaced, it can look something like this:

	ssh -L 5985:localhost:5984 root@12.34.56.78

Congratulations, you can now access your remote server's CouchDB simply by browsing to `http://localhost:5985/`.
	
	
### Remotely accessing SunlightPHP's maintenance panel
	
If you want to access SunlightPHP's maintenance panel on a remote server,
follow these steps:
		
#### Step 1	

Add "127.0.0.1 {name}" to the /etc/hosts file on your localhost. Replace {name} with a unique name you made up, e.g. an abbreviation of your domain name, so you can remember it well.
		
For projects hosted on example.com you could use "ex" as name, i.e. your localhost /etc/hosts file would contain "127.0.0.1 ex".
	
#### Step 2	

Add the name as a server alias in your remote server's VirtualHost configuration file. This file is usually located at `/etc/apache2/sites-available`. Look for a line starting with "ServerAlias" and append the name there.
				
Continuing with the example from above, you would edit `/etc/apache2/sites-available/example.com` and update the list of server aliases to "ServerAlias www.example.com ex".
		
#### Step 3

You can now access the SunlightPHP maintenance panel on your remote server from your localhost with something like:
	
	ssh -L 8080:example.com:80 root@12.34.56.78
			
Browse to `http://{name}:8080/sunlightphp/maintenance/maintenance.php` on
your localhost to see it.