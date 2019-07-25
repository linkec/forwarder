# PHP Forwarder
## A pure PHP based TCP & UDP forwarder with Web interface.

# Config
## Authenticate
    Find following block at master.php

    define('USERNAME','admin');
    define('PASSWORD','asdasd');

## Web Interface Listen Address And Port
    Find following block at master.php

    $webserver = new WebServer('http://0.0.0.0:18512');

## PHP-CLI Path
    If you are install the service manually, You probably need to set the php path
    
    define('PHPCLI','php');

# Usage
## Web Interface
I think you know how to use it.
## Command Line
Start the service
```bash
    php master.php start
```
Start the service as DEAMON
```bash
    php master.php start -d
```
Restart the service as DEAMON
```bash
    php master.php restart -d
```
Stop the service for DEAMON
```bash
    php master.php stop
```
# Install
## CentOS 6 ( OneKey )
```bash
    yum install -y epel-release
    rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm
    yum remove -y libevent-devel
    yum install php71w-cli php71w-process git gcc php71w-devel php71w-pear libevent2-devel -y
    echo "\n\n\n\n\nno\nyes\n" | pecl install event
    echo extension=event.so >> /etc/php.d/sockets.ini
    git clone https://github.com/linkec/forwarder
    php master start -d
```
## CentOS 6 ( Detail )
```bash
    #Install epel RPM source
    yum install -y epel-release

    #Install webtatic RPM source
    rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm

    #Remove Old Version Libevent Development Package
    yum remove libevent-devel

    #Install PHP7 & Libevent From RPM
    yum install php71w-cli php71w-process git gcc php71w-devel php71w-pear libevent2-devel

    #Install PHP event Extension
    #ATTENTION: Please Enter 'no' When you see "Include libevent OpenSSL support [yes] :"
    #ATTENTION: Please Enter 'yes' When you see "PHP Namespace for all Event classes :"
    pecl install event

    #Active Extension
    echo extension=event.so >> /etc/php.d/sockets.ini

    #Download Forwarder
    git clone https://github.com/linkec/forwarder
    cd forwarder

    #Start Forwarder as deamon
    php master start -d
```
## CentOS 7 ( OneKey )
```bash
    yum install -y epel-release
    rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
    yum remove -y libevent-devel
    yum install php71w-cli php71w-process git gcc php71w-devel php71w-pear libevent-devel -y
    echo "\n\n\n\n\nno\nyes\n" | pecl install event
    echo extension=event.so >> /etc/php.d/sockets.ini
    git clone https://github.com/linkec/forwarder
    php master start -d
```
## CentOS 7 ( Detail )
```bash
    #Install epel RPM source
    yum install -y epel-release

    #Install webtatic RPM source
    rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm

    #Remove Old Version Libevent Development Package
    yum remove libevent-devel

    #Install PHP7 & Libevent From RPM
    yum install php71w-cli php71w-process git gcc php71w-devel php71w-pear libevent-devel

    #Install PHP event Extension
    #ATTENTION: Please Enter 'no' When you see "Include libevent OpenSSL support [yes] :"
    #ATTENTION: Please Enter 'yes' When you see "PHP Namespace for all Event classes :"
    pecl install event

    #Active Extension
    echo extension=event.so >> /etc/php.d/sockets.ini

    #Download Forwarder
    git clone https://github.com/linkec/forwarder
    cd forwarder

    #Start Forwarder as deamon
    php master start -d
```