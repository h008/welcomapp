<?php

namespace OCA\WelcomApp\Controller;

use OCA\WelcomApp\AppInfo\Application;
use OCA\WelcomApp\Service\NoteService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;


use OCP\AppFramework\OCS\OCSException;
use OCP\AppFramework\OCS\OCSNotFoundException;
use OCP\IUserManager;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\Mail\IEMailTemplate;
use OCP\Mail\IMailer;

class NoteController extends Controller
{
	/** @var NoteService */
	private $service;
	/** @var IUserManager */
	protected $userManager;
	/** @var IGroupManager */
	protected $groupManager;
	/** @var IMailer */
	private $mailer;

	/** @var string */
	private $userId;

	use Errors;

	public function __construct(
		IRequest $request,
		NoteService $service,
		$userId,
		IGroupManager $groupManager,
		IUserManager $userManager,
		IMailer $mailer,
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userId = $userId;
		$this->groupManager = $groupManager;
		$this->userManager = $userManager;
		$this->mailer = $mailer;
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
			return $this->service->find($id, $this->userId,false);
		});
	}
	/**
	 * @NoAdminRequired
	 */
	public function filter(int $category, int $offset, int $limit, int $pubFlag, int $pinFlag, string $tags = "all",bool $unread=false): DataResponse
	{
		$userData = $this->getUserData($this->userId);
		$tagArray = explode(',', $tags);
		return $this->handleNotFound(function () use ($category, $offset, $limit, $pubFlag, $pinFlag, $userData, $tagArray,$unread) {
			return $this->service->filter($category, $offset, $limit, $pubFlag, $pinFlag, $userData, $tagArray,$unread, $this->userId);
		});
	}
	/**
	 * @NoAdminRequired
	 */
	public function filtercount(int $category, int $pubFlag, int $pinFlag, string $tags,$unread=false)
	{
		$userData = $this->getUserData($this->userId);
		$tagArray = explode(',', $tags);
		return $this->handleNotFound(function () use ($category, $pubFlag, $pinFlag, $userData, $tagArray,$unread) {
			return $this->service->filtercount($category, $pubFlag, $pinFlag, $userData, $tagArray, $unread,$this->userId);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function create(string $title, string $content, int $category, bool $pinFlag, bool $pubFlag, string $tags, string $uuid, string $shareInfo, string $readusers,bool $updateflg): DataResponse
	{
		if ($pinFlag) {
			$pinFlag = 1;
		} else {
			$pinFlag = 0;
		}
		$result = $this->service->create(
			$title,
			$content,
			$this->userId,
			$category,
			$pinFlag,
			$pubFlag,
			$tags,
			$uuid,
			$shareInfo,
			$readusers,
			$updateflg,
			
		);
		if($result && $pubFlag){
			//$shareInfoObj=json_decode('{"shareInfo":'.$shareInfo .'}');
			//$shareInfoArraytest=$shareInfoObj['shareInfo'];
		//	$shareInfoArray=explode(',',$shareInfoArraytest);
		//	$shareInfoArray = array_map(function($group){ return $group['id'];},$shareInfoArray);
		//	$shareGroupStr=implode(',',$shareInfoArray);
			$user=$this->userManager->get($this->userId);
			$this->sendMail($user,$shareInfo,$title,$content,false);
			

		}
		return new DataResponse($result);
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
		string $shareInfo,
		string $readusers,
		bool $updateflg,

	): DataResponse {
		$result= $this->handleNotFound(function () use ($id, $title, $content, $category, $pinFlag, $pubFlag, $tags, $uuid, $shareInfo, $readusers,$updateflg) {
			return $this->service->update($id, $title, $content, $this->userId, $category, $pinFlag, $pubFlag, $tags, $uuid, $shareInfo, $readusers,$updateflg);
		});
		if($result && $pubFlag){
			$user=$this->userManager->get($this->userId);

			$this->sendMail($user,$shareInfo,$title,$content,true);

		}
		return $result;
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
	protected function getUserData(string $userId)
	{
		$data = [];
		$targetUserObject = $this->userManager->get($userId);
		if ($targetUserObject === null) {
			throw new OCSNotFoundException('User does not exist');
		}
		$groups = $this->groupManager->getUserGroups($targetUserObject);
		$gids = [];
		foreach ($groups as $group) {
			$gids[] = $group->getGID();
		}
		$udata['id'] = $targetUserObject->getUID();
		$udata['groups'] = $gids;
		return $udata;
	}
	/**
	 * Sends a welcome mail to $user
	 *
	 * @param IUser $user
	 * @param IEmailTemplate $emailTemplate
	 * @throws \Exception If mail could not be sent
	 */
	private function sendMail(IUser $iuser,string $shareInfo,string $title,string $content,bool $update
							 ): void {
		preg_match_all('/(?<={"gid":").*?(?="\,"shareId":)/',$shareInfo,$matches);
		$groups=$matches[0];
		if(count($groups)){
			$users=[];
			foreach ($groups as $groupId) {
		$group=$this->groupManager->get($groupId);
		$users=array_merge($users,$group->getUsers());
		}
		}
		
		
		$emailTemplate= $this->mailer->createEMailTemplate('welcomapp',[]);
		if($update){
			$subject="「".$title ."」が更新されました。";
		}else{
			$subject="「".$title ."」が投稿されました。";
			
		}
		$bodytext="https://www.sincerely.2g.jpからのお知らせです。
		
		";
		$bodytext.=$subject ."
		";
		$bodytext.="
		";
		$bodytext.=$content;
		$emailTemplate->setSubject("【アナウンス】".$subject);
		$emailTemplate->addBodyText("https://www.sincerely.c3g.jpからのお知らせです。");
		$emailTemplate->addBodyText($subject ."https://www.sincerely.c3g.jp　にログインして詳細をご確認ください。");


		$message = $this->mailer->createMessage();
		$iemail = $iuser->getEMailAddress();
		if ($iemail !== null) {
			$mailList[$iemail] = $iuser->getDisplayName();
		}
		foreach($users as $user ){
			
		// Be sure to never try to send to an empty e-mail
		$email = $user->getEMailAddress();
		if ($email !== null) {
		$mailList[$email] = $user->getDisplayName();
		}
		$message->setTo($mailList);
		}
		$message->setFrom(['support@sincerely.c3g.jp' => 'sincerely.c3g.jp']);
		$message->useTemplate($emailTemplate);
		$this->mailer->send($message);
							 }
}
