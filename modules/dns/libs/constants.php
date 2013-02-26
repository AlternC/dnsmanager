<?php

define("DIFF_ACTION_TIMEOUT",0); // Timeout when fetching the server's data. fetch ignored (or zonefile empty).
define("DIFF_ACTION_INSERTED",1); // Zone inserted
define("DIFF_ACTION_INSERTED_DISABLED",2); // Zone inserted in disabled mode
define("DIFF_ACTION_DELETED",3); // Zone deleted
define("DIFF_ACTION_ENABLED",4); // Zone enabled because conflict disappeared
define("DIFF_ACTION_EMPTY",5); // domlist page returned empty file.
define("DIFF_ACTION_STATS",6); // log entry is statistics
define("DIFF_ACTION_DELETE_SERVER",7); // This server entry was deleted.
define("DIFF_ACTION_MASTER",8); // Successfull reload of the master zone



