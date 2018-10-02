<?php
if (function_exists('opcache_reset')){
	echo opcache_reset();
} else {
	echo 'no opcache';
}