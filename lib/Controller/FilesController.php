<?php

namespace OCA\WelcomApp\Controller;

use OCA\WelcomApp\AppInfo\Application;
use OCA\WelcomApp\Service\FilesService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class FilesController extends Controller
{
	/** @var FilesService */
	private $service;

	/** @var string */
	private $userId;

	use Errors;

	public function __construct(
		IRequest $request,
		FilesService $service,
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
	public function showByAid(string $fileurl): DataResponse
	{
			return new DataResponse($this->service->findByAid($fileurl));
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(int $id,int $announceId, string $filename,string $fileurl,string $filetype,bool $isEyecatch,string $href,bool $hasPreview ,string $updated,int $size): DataResponse
	{
		return new DataResponse($this->service->create(
			$id,
			$announceId,
			$filename,
			$fileurl,
			$filetype,
			$isEyecatch,
			$href,
			$hasPreview,
			$updated,
			$size,
			$this->userId
		));
	}

	/**
	 * @NoAdminRequired
	 */
	public function update(
		int $id,
		int $announceId,
		string $filename,
		string $fileurl,
		string $filetype,
		bool $isEyecatch,
		string $href,
		bool $hasPreview,
		string $updated,
		int $size,


	): DataResponse {
		return $this->handleNotFound(function () use ($id, $announceId, $filename,$fileurl,$filetype,$isEyecatch,$href,$hasPreview,$updated,$size) {
			return $this->service->update($id, $announceId, $filename,$fileurl,$filetype,$isEyecatch,$href,$hasPreview,$updated,$size,$this->userId);
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
