#!/bin/bash
pecl channel-update pecl.php.net
if [[ ${TRAVIS_PHP_VERSION:0:1} == "5" ]] ; then
echo "yes" | pecl install apcu-4.0.11
if [[ ${TRAVIS_PHP_VERSION:0:1} == "7" ]] ; then
echo "yes" | pecl install apcu-5.1.3
fi
