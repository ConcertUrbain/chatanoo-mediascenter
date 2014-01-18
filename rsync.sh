#!/bin/bash
# rsync -avz -e ssh . "root@ns368978.ovh.net:/var/www/vhosts/chatanoo.org/core/mc/prod/" --exclude-from 'rsync.exclude'
rsync -avz -e ssh . "root@ns3002499.ip-37-59-5.eu:/var/www/medias-center/" --exclude-from 'rsync.exclude'