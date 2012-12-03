#!/bin/sh

# You may use this *safe* script instead of rndc reconfig 
# to push the zone file to another server, and reload both the local one
# and the remote one
# thanks to that script, you can have NS0 AND NS1 synchronizing from the same machine 
# (the one that run the AlternC-dnsmanager)

# Which host we push data to:
REMOTEHOST="mysecondarydns.mydomain.tld"
# Which command to launch on the remote side to reconfig bind
REMOTECOMMAND="rndc reconfig"
# Which ssh-key we shall use
REMOTEKEY="/root/.ssh/id_rsa"
# the remote zone file
REMOTEZONE="/var/lib/slavedns/bind.slave.conf"

# The local zone
LOCALZONE="/var/lib/slavedns/bind.slave.conf"
# The local bind9 reconfig command
LOCALCOMMAND="rndc reconfig"




rsync "$LOCALZONE" "root@$REMOTEHOST:$REMOTEZONE" -e "ssh -i '$REMOTEKEY'"
OUT=$?
if [ "$OUT" -ne "0" ]
then
    echo "Error launching RSYNC on push.sh"
    exit $OUT
fi 

ssh "root@$REMOTEHOST" -i "$REMOTEKEY" "$REMOTECOMMAND"
OUT=$?
if [ "$OUT" -ne "0" ]
then
    echo "Error launching remote command $REMOTECOMMAND on push.sh"
    exit $OUT
fi 

$LOCALCOMMAND
OUT=$?
if [ "$OUT" -ne "0" ]
then
    echo "Error launching local command $LOCALCOMMAND on push.sh"
    exit $OUT
fi 
