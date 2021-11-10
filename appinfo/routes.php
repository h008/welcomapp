<?php

return [
	'resources' => [
		'note' => ['url' => '/notes'],
		'category' => ['url' => '/categories'],
		'welcom_tag' => ['url' => '/tags'],
		'files' => ['url' => '/files'],
		'users' => ['url' => '/users'],
		'config'=>['url'=>'/config'],
		'note_api' => ['url' => '/api/0.1/notes']
	],
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'note#filter','url'=>'/filter','verb' =>'GET'],
		['name' => 'note#filtercount','url'=>'/filtercount','verb' =>'GET'],
		['name' => 'users#getAllUsers','url'=>'/getusers','verb' =>'GET'],
		['name' => 'users#getAllUserInfo','url'=>'/getallusers','verb' =>'GET'],
		['name' => 'users#getUserInfo','url'=>'/getuser/{id}','verb' =>'GET'],
		['name' => 'users#getAllGroups','url'=>'/getallgroups','verb' =>'GET'],
		['name' => 'users#editGroup','url'=>'/editgroup/{id}','verb' =>'PUT'],
		['name' => 'files#showByAid','url'=>'/getfiles/{fileurl}','verb'=>'GET'],
		['name' => 'config#showByKind','url'=>'/getconfig/{kind}','varb'=>'GET'],
	]
];