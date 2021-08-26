<?php

namespace OCA\WelcomApp\Controller;

use OCA\WelcomApp\AppInfo\Application;
use OCA\WelcomApp\Service\CategoryService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class CategoryController extends Controller
{
	/** @var CategoryService */
	private $service;

	/** @var string */
	private $userId;

	use Errors;

	public function __construct(
		IRequest $request,
		CategoryService $service,
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
	public function create(string $category_name, int $category_order,string $color ): DataResponse
	{
		return new DataResponse($this->service->create(
			$category_name,
			$category_order,
			$color,
		));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(
		int $id,
		string $category_name,
		int $category_order,
		string $color
	): DataResponse {
		return $this->handleNotFound(function () use ($id, $category_name, $category_order,$color) {
			return $this->service->update($id, $category_name, $category_order,$color);
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
