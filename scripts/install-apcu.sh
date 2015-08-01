#!/bin/bash
if [[ ${TRAVIS_PHP_VERSION:0:3} != "5.4" ]] ; then
    echo "no" | pecl install apcu-4.0.7
fi
