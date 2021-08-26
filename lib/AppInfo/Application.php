<?php
namespace OCA\WelcomApp\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public const APP_ID = 'welcomapp';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}
