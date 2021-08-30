<?php

namespace OCA\WelcomApp\Controller;

use OCA\WelcomApp\AppInfo\Application;
use OCA\WelcomApp\Service\ConfigService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ConfigController extends Controller
{
	/** @var ConfigService */
	private $service;

	/** @var string */
	private $userId;

	use Errors;

	public function __construct(
		IRequest $request,
		ConfigService $service,
		$userId
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(): DataResponse
	{
		return new DataResponse($this->service->findAll());
	}

	/**
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse
	{
		return $this->handleNotFound(function () use ($id) {
			return $this->service->find($id);
		});
	}
	/**
	 * @NoAdminRequired
	 */
	public function showByKind(string $kind): DataResponse
	{
		return $this->handleNotFound(function () use ($kind) {
			return $this->service->findByKind($kind);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(string $kind, string $key ,string $value ): DataResponse
	{
		return new DataResponse($this->service->create(
			$kind,
			$key,
			$value,
			$this->userId,
		));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(
		int $id,
		string $kind,
		string $key,
		string $value
	): DataResponse {
		return $this->handleNotFound(function () use ($id, $kind,$key, $value) {
			return $this->service->update($id, $kind, $key,$value,$this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse
	{
		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id);
		});
	}
}
