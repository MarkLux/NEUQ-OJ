<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-25
 * Time: 下午9:55
 */

namespace NEUQOJ\Http\Controllers\UserGroup;

use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Exceptions\PasswordErrorException;
use NEUQOJ\Exceptions\UserGroup\NoticeNotBelongToGroupException;
use NEUQOJ\Exceptions\UserGroup\UserGroupNotExistException;
use NEUQOJ\Services\UserGroupService;
use Illuminate\Support\Facades\Hash;
use NEUQOJ\Http\Controllers\Controller;

class NoticeController extends Controller
{
    private $userGroupService;

    public function __construct(UserGroupService $userGroupService)
    {
        $this->userGroupService = $userGroupService;
    }

    /**
     *公告板部分
     */
    public function getGroupNotices(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'size' => 'integer|min:1|max:50',
            'page' => 'integer|min:1|max:500',
            'gid' => 'integer|required'
        ]);

        if(!$this->userGroupService->isGroupExistById($request->gid))
            throw new UserGroupNotExistException();

        if(!$this->userGroupService->isUserInGroup($request->user->id,$request->gid))
            throw new NoPermissionException();
        $total_count = $this->userGroupService->getGroupNoticesCount($request->gid);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $size = $request->input('size',20);
        $page = $request->input('page',1);

        if(!empty($total_count))
            $data = $this->userGroupService->getGroupNotices($request->gid,$page,$size);
        else
            $data = null;

        return response()->json([
            "code" => 0,
            "data" => $data,
            "page_count" => ($total_count%$size)?intval($total_count/$size+1):($total_count/$size)
        ]);
    }

    public function addNotice(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|min:1|max:100',
            'content' => 'required|min:6|max:2048',
            'gid' => 'required|integer'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());


        if(!$this->userGroupService->isGroupExistById($request->gid))
            throw new UserGroupNotExistException();

        //权限检查
        if(!$this->userGroupService->isUserGroupOwner($request->user->id,$request->gid))
            throw new NoPermissionException();


        if(!$this->userGroupService->addNotice($request->gid,['title'=>$request->input('title'),'content'=>$request->input('content')]))
            throw new InnerError("Fail to add notice");

        return response()->json([
            "code" => 0
        ]);
    }

    public function updateNotice(Request $request,int $noticeId)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'string|min:1|max:100',
            'content' => 'string|min:6|max:2048',
            'gid' => 'required|integer'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //标题和内容不能都没有
        $title = $request->input('title',null);
        $content = $request->input('content',null);

        if($title == null&&$content==null)
            throw new FormValidatorException(['title and content cant be empty meanwhile']);

        if(!$this->userGroupService->isNoticeBelongToGroup($noticeId,$request->gid))
            throw new NoticeNotBelongToGroupException();

        //权限检查
        if(!$this->userGroupService->isUserGroupOwner($request->user->id,$request->gid))
            throw new NoPermissionException();

        if(!$this->userGroupService->updateNotice($noticeId,['content' => $content,'title'=>$title]))
            throw new InnerError("Fail to update Notice");

        return response()->json([
            'code' => 0
        ]);
    }

    public function deleteNotice(Request $request,int $noticeId)
    {
        $validator = Validator::make($request->all(),[
            'gid' => 'required|integer'
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        if(!$this->userGroupService->isNoticeBelongToGroup($noticeId,$request->gid))
            throw new NoticeNotBelongToGroupException();

        //权限检查
        if(!$this->userGroupService->isUserGroupOwner($request->user->id,$request->gid))
            throw new NoPermissionException();

        if(!$this->userGroupService->deleteNotice($noticeId))
            throw new InnerError("Fail to delete Notice");

        return response()->json([
            'code' => 0
        ]);
    }

    public function getNotice(Request $request,int $noticeId)
    {
        $validator = Validator::make($request->all(),[
            "gid" => "required|integer"
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //检查一堆东西
        if(!$this->userGroupService->isUserInGroup($request->user->id,$request->gid))
            throw new NoPermissionException();
        if(!$this->userGroupService->isNoticeBelongToGroup($noticeId,$request->gid))
            throw new NoticeNotBelongToGroupException();

        $data = $this->userGroupService->getSingleNotice($noticeId);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }
}