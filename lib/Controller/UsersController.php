<?php

namespace OCA\WelcomApp\Controller;

use OCP\Files\NotFoundException;
use OCP\AppFramework\OCS\OCSException;
use OCP\AppFramework\OCS\OCSNotFoundException;

use OCA\WelcomApp\AppInfo\Application;
use OCP\Accounts\IAccountManager;
use OCP\Accounts\PropertyDoesNotExistException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;

class UsersController extends Controller
{

	/** @var IUserManager */
	protected $userManager;
	/** @var IGroupManager */
	protected $groupManager;
	/** @var IUserSession */
	protected $userSession;
	/** @var IAccountManager */
	protected $accountManager;

	/** @var string */
	private $userId;
	

	use Errors;

	public function __construct(
		IRequest $request,
		IGroupManager $groupManager,
		IUserManager $userManager,
		IUserSession $userSession,
		IAccountManager $accountManager,
		$userId
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->userId = $userId;
		$this->groupManager=$groupManager;
		$this->userManager=$userManager;
		$this->userSession=$userSession;
		$this->accountManager=$accountManager;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index() 
	{

		// ICreateGroupBackend::createGroup('test');
		// return $this->userId;
		$userData=$this->initialize();
		return new JSONResponse($userData);
	}
	/**
	 * @NoAdminRequired
	 */
	public function initialize() 
	{
		$user=$this->userManager->get($this->userId);
		$group = $this->groupManager->get('all_users');
		if ($group ===null){
			$group = $this->groupManager->createGroup('all_users');
		}
		$userData=$this->getUserData($this->userId);
		if(!in_array('all_users',$userData['groups'])){
			$group->addUser($user);
			
		}
		return $userData;




	}
	/**
	 * @NoAdminRequired
	 */
	public function addToGroup($userId){
		$group = $this->groupManager->get('all_users');
		$user=$this->userManager->get($userId);
		$group->addUser($user);
		
		
	}
	/**
	 * @NoAdminRequired
	 */
	public function getAllUsers()
	{
		$group=$this->groupManager->get('all_users');
		$users=$group->getUsers();
		$users=array_map(function($user){
			/** @var IUser $user */
			return $user->getUID();
		},$users);
		$users= array_values($users);
		return new JSONResponse($users);

		
	}
	/**
	 * @NoAdminRequired
	 */
	public function getUserInfo(string $id){
		$userInfo=$this->getUserData($id);
		return new JSONResponse($userInfo);

	}
	/**
	 * @NoAdminRequired
	 * @param string $userId
	 * @return aray
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
		$userAccount = $this->accountManager->getAccount($targetUserObject);
		$groups=$this->groupManager->getUserGroups($targetUserObject);
		$gids=[];
		foreach($groups as $group) {
			$gids[]=$group->getGID();
		}
		$data['id']=$targetUserObject->getUID();
		try{
			$data[IAccountManager::PROPERTY_EMAIL] = $targetUserObject->getEMailAddress();

			$additionalEmails = $additionalEmailScopes = [];
			$emailCollection = $userAccount->getPropertyCollection(IAccountManager::COLLECTION_EMAIL);
			foreach ($emailCollection->getProperties() as $property) {
				$additionalEmails[] = $property->getValue();
			}
			$data[IAccountManager::COLLECTION_EMAIL] = $additionalEmails;

			$data[IAccountManager::PROPERTY_DISPLAYNAME] = $targetUserObject->getDisplayName();

		} catch (PropertyDoesNotExistException $e){
			throw new OCSException($e->getMessage(),Http::STATUS_INTERNAL_SERVER_ERROR,$e);
		}
		$data['groups']=$gids;
		return $data;
		
	}


}
