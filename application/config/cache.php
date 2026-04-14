<?php
/**
 * Cache configuration for the test environment.
 * Enables the 'file' driver so tests that use Kohana::cache() and Route::cache()
 * have a working backend without requiring external services.
 */
return [
	'file' => [
		'driver'         => 'file',
		'cache_dir'      => APPPATH.'cache',
		'default_expire' => 3600,
		'ignore_on_delete' => [
			'file_we_want_to_keep.cache',
			'.gitignore',
			'.git',
			'.svn',
		],
	],
];
