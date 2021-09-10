<?php

namespace OCA\WelcomApp\Controller;

use OCA\WelcomApp\AppInfo\Application;
use OCA\WelcomApp\Service\NoteService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;


use OCP\AppFramework\OCS\OCSException;
use OCP\AppFramework\OCS\OCSNotFoundException;
use OCP\Accounts\IAccountManager;
use OCP\IUserManager;
use OCP\IGroupManager;

class NoteController extends Controller
{
	/** @var NoteService */
	private $service;
	/** @var IUserManager */
	protected $userManager;
	/** @var IGroupManager */
	protected $groupManager;

	/** @var string */
	private $userId;

	use Errors;

	public function __construct(
		IRequest $request,
		NoteService $service,
		IGroupManager $groupManager,
		IUserManager $userManager,
		$userId
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userId = $userId;
		$this->groupManager=$groupManager;
		$this->userManager=$userManager;
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
	public function filter(int $category,int $offset ,int $limit,int $pubFlag,int $pinFlag): DataResponse
	{
$userData=$this->getUserData($this->userId);
		 return $this->handleNotFound(function () use ($category,$offset,$limit,$pubFlag,$pinFlag,$userData) {
			return $this->service->filter($category,$offset,$limit,$pubFlag,$pinFlag,$userData,$this->userId);
		});
	}
	/**
	 * @NoAdminRequired
	 */
	public function filtercount(int $category,int $pubFlag,int $pinFlag)
	{
$userData=$this->getUserData($this->userId);
		 return $this->handleNotFound(function () use ($category,$pubFlag,$pinFlag,$userData) {
			return $this->service->filtercount($category,$pubFlag,$pinFlag,$userData,$this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(string $title, string $content,int $category,int $pinFlag,int $pubFlag,string $tags,string $uuid,string $shareInfo ): DataResponse
	{
		if($pinFlag){$pinFlag=1;}else{$pinFlag=0;}
		return new DataResponse($this->service->create(
			$title,
			$content,
			$this->userId,
			$category,
			$pinFlag,
			$pubFlag,
			$tags,
			$uuid,
			$shareInfo
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
		int $pinFlag,
		int $pubFlag,
		string $tags,
		string $uuid,
		string $shareInfo
	): DataResponse {
		return $this->handleNotFound(function () use ($id, $title, $content, $category, $pinFlag, $pubFlag, $tags,$uuid,$shareInfo) {
			return $this->service->update($id, $title, $content, $this->userId, $category, $pinFlag, $pubFlag, $tags,$uuid,$shareInfo);
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
	/**
	 * @NoAdminRequired
	 * @param string $userId
	 * @return array
	 * @throws NotFoundException
	 * @throws OCSException
	 * @throws OCSNotFoundException
	 */
	protected function getUserData(string $userId){
		$data=[];
		$targetUserObject = $this->userManager->get($userId);
		if($targetUserObject === null){
			throw new OCSNotFoundException('User does not exist');
		}
		$groups=$this->groupManager->getUserGroups($targetUserObject);
		$gids=[];
		foreach($groups as $group) {
			$gids[]=$group->getGID();
		}
		$udata['id']=$targetUserObject->getUID();
		$udata['groups']=$gids;
		return $udata;
		
	}
}
