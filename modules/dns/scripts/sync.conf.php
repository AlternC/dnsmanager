<?php

// Maximum number of parallel HTTP(S) sockets
define("MAX_SOCKETS",200);
// Location of the system-wide Certificate Authority File  (MUST BE an absolute path)
define("DEFAULT_CAFILE","/etc/ssl/certs/ca-certificates.crt");
// Absolute path to the bind slavedns zone list. 
// We must be allowed to write to this file.
define("SLAVEZONE_FILE","/var/lib/slavedns/bind.slave.conf");
// Command to launch to reload bind configuration. 
// Should be something like "rndc reconfig" maybe using sudo if you are neither root nor bind user.
// DON'T USE BIND9 RESTART OR RNDC RELOAD. READ THE MAN PAGES!
// You can replace it by a shell-script that could push the zone to the real servers
define("BIND_RELOAD","rndc reconfig");

$DEBUG=false;
$DEBUG=true;


