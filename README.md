# Hemsida-ANM
SNMP Requests via perl for docker providing PHP website

PHP:
Add the whole folder to /var/www/

Scripts:
Use the text in createDBscript.txt to initiate the mysql Database
Use task2.conf to enter the server to probe etc.
Use task2.pl to probe once
Use probe.sh to run task2.pl every now and then, change the number at sleep to increase/decrease the period to pause between probes

Make sure probe.sh is pointing to the location of task2.pl
