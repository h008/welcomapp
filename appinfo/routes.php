<?php

return [
	'resources' => [
		'note' => ['url' => '/notes'],
		'category' => ['url' => '/categories'],
		'welcom_tag' => ['url' => '/tags'],
		'files' => ['url' => '/files'],
		'users' => ['url' => '/users'],
		'note_api' => ['url' => '/api/0.1/notes']
	],
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'note_api#preflighted_cors', 'url' => '/api/0.1/{path}',
			'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']],
		['name' => 'note#filter','url'=>'/filter','verb' =>'GET'],
		['name' => 'note#filtercount','url'=>'/filtercount','verb' =>'GET'],
		['name' => 'users#getAllUsers','url'=>'/getusers','verb' =>'GET'],
		['name' => 'users#getUserInfo','url'=>'/getuser/{id}','verb' =>'GET'],
		['name' => 'files#showByAid','url'=>'/getfiles/{fileurl}','verb'=>'GET']
	]
];