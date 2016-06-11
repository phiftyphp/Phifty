#!/bin/bash
if [[ ${TRAVIS_PHP_VERSION:0:1} == "5" ]] ; then
    echo "yes" | pecl install apcu-4.0.11
fi
if [[ ${TRAVIS_PHP_VERSION:0:1} == "7" ]] ; then
    echo "yes" | pecl install apcu-5.1.5
fi
