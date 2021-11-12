<?php

namespace OCA\WelcomApp\Controller;

use OCP\Files\NotFoundException;
use OCP\AppFramework\OCS\OCSException;
use OCP\AppFramework\OCS\OCSNotFoundException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Response;

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
use PHPUnit\Util\Json;
use OCP\Mail\IEMailTemplate;
use OCP\Mail\IMailer;

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
	/** @var IMailer */
	private $mailer;

	/** @var string */
	private $userId;
	private $name;

	

	use Errors;

	public function __construct(
		IRequest $request,
		IGroupManager $groupManager,
		IUserManager $userManager,
		IUserSession $userSession,
		IAccountManager $accountManager,
		IMailer $mailer,
		$userId
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->userId = $userId;
		$this->groupManager=$groupManager;
		$this->userManager=$userManager;
		$this->userSession=$userSession;
		$this->accountManager=$accountManager;
		$this->mailer = $mailer;
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
	 * 
	 * @param string $id
	 * @param string $gname
	 * 
	 */
	public function editGroup($request,$id,$gname){
		if($id && $gname){
			$group =$this->groupManager->get($id);
			$group->setDisplayName($gname);

		}
		$ret['req']=$request;
		$ret['id']=$id;
		$ret['name']=$gname;
return new JSONResponse($ret);
	}
	/**
	 * @NoAdminRequired
	 */
	public function getAllGroups(){
		$groups = $this->groupManager->search('');
		$groups = array_map(function($group){
			$gid= $group->getGID();
			$display_name=$group->getDisplayName();	
			$ret['id']=$gid;
			$ret['name']=$display_name;
			return $ret;
		},$groups);
		
		return new JSONResponse(array_values($groups));
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
	public function getAllUserInfo()
	{
		$group=$this->groupManager->get('all_users');
		$users=$group->getUsers();
		$users=array_map(function($user){
			/** @var IUser $user */
			$uid = $user->getUID();
			/** @var IUser $user */
			$display_name=$user->getDisplayName();
			$ret['uid']=$uid;
			$ret['display_name']=$display_name;

			return $ret;
		},$users);
		//$users=json_encode($users)
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
		$userAccount = $this->accountManager->getAccount($targetUserObject);
		$groups=$this->groupManager->getUserGroups($targetUserObject);
		$gids=[];
		$groupData=[];
		foreach($groups as $group) {
			$obj["id"]=$group->getGID();
			$obj["name"]=$group->getDisplayName();
			$gids[]=$obj["id"];
			$gdata[]=$obj;
			

		}
		$data['id']=$targetUserObject->getUID();
		try{
			$data[IAccountManager::PROPERTY_EMAIL] = $targetUserObject->getEMailAddress();

			//$additionalEmails = $additionalEmailScopes = [];
			//$emailCollection = $userAccount->getPropertyCollection(IAccountManager::COLLECTION_EMAIL);
			//foreach ($emailCollection->getProperties() as $property) {
			//	$additionalEmails[] = $property->getValue();
			//}
			//$data[IAccountManager::COLLECTION_EMAIL] = $additionalEmails;

			$data[IAccountManager::PROPERTY_DISPLAYNAME] = $targetUserObject->getDisplayName();

		} catch (PropertyDoesNotExistException $e){
			throw new OCSException($e->getMessage(),Http::STATUS_INTERNAL_SERVER_ERROR,$e);
		}
		$data['groups']=$gids;
		$data['gdata']=$gdata;
		return $data;
		
	}
	public function sendMessage(){
		$user=$this->userManager->get($this->userId);
		$this->sendMail($user);

	}
	/**
	 * Sends a welcome mail to $user
	 *
	 * @param IUser $user
	 * @param IEmailTemplate $emailTemplate
	 * @throws \Exception If mail could not be sent
	 */
	private function sendMail(IUser $user,
							 ): void {

		// Be sure to never try to send to an empty e-mail
		$email = $user->getEMailAddress();
		if ($email === null) {
			return;
		}
		$emailTemplate= $this->mailer->createEMailTemplate('welcomapp',[]);
		$emailTemplate->setSubject('タイトル');
		$emailTemplate->addBodyText('ボディテキスト');


		$message = $this->mailer->createMessage();
		$message->setTo([$email => $user->getDisplayName()]);
		$message->setFrom(['support@sincerely.c3g.jp' => 'お知らせ']);
		$message->useTemplate($emailTemplate);
		$this->mailer->send($message);
	}


}
