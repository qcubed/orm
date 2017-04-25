<?php

	/*
	 * Configure the mysql travis-ci connection
	 */
	define('DB_CONNECTION_1', serialize(array(
		'adapter' => 'Mysqli5',
		'server' => 'localhost',
		'port' => null,
		'database' => 'qcubed',
		'username' => 'root',
		'password' => '',
		'caching' => false,
		'profiling' => false)));
