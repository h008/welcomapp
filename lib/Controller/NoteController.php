<?php

namespace OCA\WelcomApp\Controller;

use OCA\WelcomApp\AppInfo\Application;
use OCA\WelcomApp\Service\NoteService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class NoteController extends Controller
{
	/** @var NoteService */
	private $service;

	/** @var string */
	private $userId;

	use Errors;

	public function __construct(
		IRequest $request,
		NoteService $service,
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
		return new DataResponse($this->service->findAll($this->userId, 3));
	}

	/**
	 * @NoAdminRequired
	 */
	public function show(int $id): DataResponse
	{
		return $this->handleNotFound(function () use ($id) {
			return $this->service->find($id, $this->userId);
		});
	}
	/**
	 * @NoAdminRequired
	 */
	public function filter(int $category,int $offset ,int $limit,bool $pubFlag,bool $pinFlag): DataResponse
	{
		 return $this->handleNotFound(function () use ($category,$offset,$limit,$pubFlag,$pinFlag) {
			return $this->service->filter($category,$offset,$limit,$pubFlag,$pinFlag,$this->userId);
		});
	}
	/**
	 * @NoAdminRequired
	 */
	public function filtercount(int $category,bool $pubFlag,bool $pinFlag)
	{
		 return $this->handleNotFound(function () use ($category,$pubFlag,$pinFlag) {
			return $this->service->filtercount($category,$pubFlag,$pinFlag,$this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(string $title, string $content,int $category,bool $pinFlag,bool $pubFlag,string $tags,string $uuid,int $shareId ): DataResponse
	{
		return new DataResponse($this->service->create(
			$title,
			$content,
			$this->userId,
			$category,
			$pinFlag,
			$pubFlag,
			$tags,
			$uuid,
			$shareId
		));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(
		int $id,
		string $title,
		string $content,
		int $category,
		bool $pinFlag,
		bool $pubFlag,
		string $tags,
		string $uuid,
		int $shareId
	): DataResponse {
		return $this->handleNotFound(function () use ($id, $title, $content, $category, $pinFlag, $pubFlag, $tags,$uuid,$shareId) {
			return $this->service->update($id, $title, $content, $this->userId, $category, $pinFlag, $pubFlag, $tags,$uuid,$shareId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function destroy(int $id): DataResponse
	{
		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id, $this->userId);
		});
	}
}
