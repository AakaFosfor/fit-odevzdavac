<?php

$db = new SQLite3('log.db');

var_dump($db->exec('CREATE TABLE log (id INTEGER PRIMARY KEY NOT NULL, date INTEGER NOT NULL, ip TEXT NOT NULL, user TEXT NOT NULL, filename TEXT NOT NULL)'));
