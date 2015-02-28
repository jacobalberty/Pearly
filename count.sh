#!/bin/sh
find . -path "./class/Psr" -prune -o -path "./includes" -prune -o -path "./conf" -prune -o -path './3rdparty' -prune -o -name \*.php -print0 | xargs -0 wc -l
