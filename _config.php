<?php

if (($NEWS_MODULE_DIR = basename(dirname(__FILE__))) != 'news') {
	die("News module should exist in the /news directory, not $NEWS_MODULE_DIR");
}
