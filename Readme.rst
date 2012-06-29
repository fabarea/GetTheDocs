Necessary packages on the server that need to be installed:

* aptitude install zip - command zip
* RestTools.git
* aptitude install openoffice.org - command soffice
* daemon soffice converter listening to 8100

IMPORTANT: make sure apache can use the soffice daemon. Maybe run apache2 as an other user as www-data

umask 0002
chown -R render:www-default {upload,files}
chmod -R 775 {upload,files}

Starting OO in headless with Version OpenOffice 3.0 ++
=========================================================
sudo apt-get install openoffice.org-headless

http://code.google.com/p/openmeetings/wiki/OpenOfficeConverter



