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
use NEUQOJ\Facades\Permission;
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
    public function getGroupNotices(Request $request, int $groupId)
    {
        $validator = Validator::make($request->all(), [
            'size' => 'integer|min:1',
            'page' => 'integer|min:1',
        ]);

        if (!$this->userGroupService->isGroupExistById($groupId))
            throw new UserGroupNotExistException();

        if (!$this->userGroupService->isUserInGroup($request->user->id, $groupId))
            throw new NoPermissionException();
        $total_count = $this->userGroupService->getGroupNoticesCount($groupId);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $size = $request->input('size', 20);
        $page = $request->input('page', 1);

        if (!empty($total_count))
            $data = $this->userGroupService->getGroupNotices($groupId, $page, $size);
        else
            $data = null;

        return response()->json([
            "code" => 0,
            "data" => [
                'notices' => $data,
                'count' => $total_count
            ]
        ]);
    }

    public function addNotice(Request $request, int $groupId)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:1|max:100',
            'content' => 'required|min:6|max:2048'
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());


        if (!$this->userGroupService->isGroupExistById($groupId))
            throw new UserGroupNotExistException();

        //权限检查
        if (!Permission::checkPermission($request->user->id, ['manage-user-group'])) {
            if (!$this->userGroupService->isUserGroupOwner($request->user->id, $groupId))
                throw new NoPermissionException();
        }

        if (!$this->userGroupService->addNotice([
            'group_id' => $groupId,
            'title' => $request->input('title'),
            'content' => $request->input('content')
        ])
        )
            throw new InnerError("Fail to add notice");

        return response()->json([
            "code" => 0
        ]);
    }

    public function updateNotice(Request $request, int $groupId,int $noticeId)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string|min:1|max:100',
            'content' => 'string|min:6|max:2048'
        ]);

        if ($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        //标题和内容不能都没有
        $title = $request->input('title', null);
        $content = $request->input('content', null);

        if ($title == null && $content == null)
            throw new FormValidatorException(['title and content cant be empty meanwhile']);

        if (!$this->userGroupService->isNoticeBelongToGroup($noticeId, $groupId))
            throw new NoticeNotBelongToGroupException();

        //权限检查
        if (!Permission::checkPermission($request->user->id, ['manage-user-group'])) {
            if (!$this->userGroupService->isUserGroupOwner($request->user->id, $groupId))
                throw new NoPermissionException();
        }

        if (!$this->userGroupService->updateNotice($noticeId, ['content' => $content, 'title' => $title]))
            throw new InnerError("Fail to update Notice");

        return response()->json([
            'code' => 0
        ]);
    }

    public function deleteNotice(Request $request,int $groupId,int $noticeId)
    {
        if (!$this->userGroupService->isNoticeBelongToGroup($noticeId, $groupId))
            throw new NoticeNotBelongToGroupException();

        //权限检查
        if (!Permission::checkPermission($request->user->id, ['manage-user-group'])) {
            if (!$this->userGroupService->isUserGroupOwner($request->user->id, $groupId))
                throw new NoPermissionException();
        }

        if (!$this->userGroupService->deleteNotice($noticeId))
            throw new InnerError("Fail to delete Notice");

        return response()->json([
            'code' => 0,
        ]);
    }

    public function getNotice(Request $request, int $groupId,int $noticeId)
    {

        //检查一堆东西
        if (!$this->userGroupService->isNoticeBelongToGroup($noticeId, $groupId))
            throw new NoticeNotBelongToGroupException();
        if (!$this->userGroupService->isUserInGroup($request->user->id, $groupId))
            throw new NoPermissionException();

        $data = $this->userGroupService->getSingleNotice($noticeId);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }
}