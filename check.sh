#!/bin/sh
find . -name \*.php -exec php -l "{}" \; 1> /dev/null
