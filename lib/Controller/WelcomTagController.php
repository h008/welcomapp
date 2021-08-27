<?php

namespace OCA\WelcomApp\Controller;

use OCA\WelcomApp\AppInfo\Application;
use OCA\WelcomApp\Service\WelcomTagService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class WelcomTagController extends Controller
{
	/** @var WelcomTagService */
	private $service;

	/** @var string */
	private $userId;

	use Errors;

	public function __construct(
		IRequest $request,
		WelcomTagService $service,
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
	public function create(string $tag_name, int $tag_order,string $color ): DataResponse
	{
		return new DataResponse($this->service->create(
			$tag_name,
			$tag_order,
			$color,
		));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(
		int $id,
		string $tag_name,
		int $tag_order,
		string $color
	): DataResponse {
		return $this->handleNotFound(function () use ($id, $tag_name, $tag_order,$color) {
			return $this->service->update($id, $tag_name, $tag_order,$color);
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
