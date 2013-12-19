#!/bin/sh

# You may use this *safe* script instead of rndc reconfig 
# to push the zone file to another server, and reload both the local one
# and the remote one
# thanks to that script, you can have NS0 AND NS1 synchronizing from the same machine 
# (the one that run the AlternC-dnsmanager)

# Which host we push data to:
REMOTEHOST="ns2.alternc.net"
# Which command to launch on the remote side to reconfig bind
REMOTECOMMAND="/usr/sbin/rndc -c /etc/bind2/rndc.key reconfig"
# Which ssh-key we shall use
REMOTEKEY="/root/.ssh/id_rsa"
# the remote zone file
REMOTEZONE="/etc/bind2/bind.slave.conf"

# Which host we push data to:
REMOTE2HOST="ns3.alternc.net"
# Which command to launch on the remote side to reconfig bind
REMOTE2COMMAND="rndc reconfig"
# Which ssh-key we shall use
REMOTE2KEY="/root/.ssh/id_rsa"
# the remote zone file
REMOTE2ZONE="/etc/bind/bind.slave.conf"

# The local zone
LOCALZONE="/var/lib/slavedns/bind.slave.conf"
# The local bind9 reconfig command
LOCALCOMMAND="/usr/sbin/rndc reconfig"


rsync "$LOCALZONE" "root@$REMOTEHOST:$REMOTEZONE" -e "ssh -4 -i '$REMOTEKEY'"
OUT=$?
if [ "$OUT" -ne "0" ]
then
    echo "Error launching RSYNC on push.sh"
    exit $OUT
fi 

ssh -4 "root@$REMOTEHOST" -i "$REMOTEKEY" "$REMOTECOMMAND"
OUT=$?
if [ "$OUT" -ne "0" ]
then
    echo "Error launching remote command $REMOTECOMMAND on push.sh"
    exit $OUT
fi 

rsync "$LOCALZONE" "root@$REMOTE2HOST:$REMOTE2ZONE" -e "ssh -4 -i '$REMOTE2KEY'"
OUT=$?
if [ "$OUT" -ne "0" ]
then
    echo "Error launching RSYNC on push.sh"
    exit $OUT
fi 

ssh -4 "root@$REMOTE2HOST" -i "$REMOTE2KEY" "$REMOTE2COMMAND"
OUT=$?
if [ "$OUT" -ne "0" ]
then
    echo "Error launching remote command $REMOTE2COMMAND on push.sh"
    exit $OUT
fi 

$LOCALCOMMAND
OUT=$?
if [ "$OUT" -ne "0" ]
then
    echo "Error launching local command $LOCALCOMMAND on push.sh"
    exit $OUT
fi 
