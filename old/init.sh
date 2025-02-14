#!/bin/bash

#if mariadb status is not running, start it
if [ $(systemctl is-active mariadb) != "active" ]; then
    sudo systemctl start mariadb
fi

# start the server and open the browser to the page http://127.0.0.1:8080
php -S 127.0.0.1:5500 & firefox http://127.0.0.1:5500

