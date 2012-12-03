<?php

define("DIFF_ACTION_TIMEOUT",0); // Timeout when fetching the server's data. fetch ignored (or zonefile empty).
define("DIFF_ACTION_INSERTED",1); // Zone inserted
define("DIFF_ACTION_INSERTED_DISABLED",2); // Zone inserted in disabled mode
define("DIFF_ACTION_DELETED",3); // Zone deleted
define("DIFF_ACTION_ENABLED",4); // Zone enabled because conflict disappeared

