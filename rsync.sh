#!/bin/bash
rsync -avz -e ssh . "root@ns368978.ovh.net:/var/www/vhosts/chatanoo.org/core/mc/prod/" --exclude-from 'rsync.exclude'