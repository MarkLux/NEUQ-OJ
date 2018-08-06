<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Facades\Permission;
use NEUQOJ\Services\AdminService;
use NEUQOJ\Services\FreeProblemSetService;
use NEUQOJ\Services\UserService;

class AdminController extends Controller
{
    private $adminService;
    private $userService;
    public function __construct(AdminService $adminService,UserService $userService)
    {
        $this->adminService = $adminService;
        $this->userService = $userService;
    }

    public function changeUserPassword(Request $request){
        $identifier=$request->identifier;
        $password = $request->password;
        $userId=$this->userService->getUserId($identifier);
        $this->adminService->changeUserPassword($userId,$password);
        return response()->json([
            'code'=>'0',
            'message'=>'密码修改成功'
        ]);
    }

    public function lockUser(UserService $userService, $id)
    {
        if ($userService->lockUser($id))
            return response()->json([
                'code' => '0'
            ]);
    }

    public function unlockUser(UserService $userService, $id)
    {
        if ($userService->unlockUser($id))
            return response()->json([
                'code' => '0'
            ]);
    }

    public function importProblems(Request $request, FreeProblemSetService $freeProblemSetService)
    {
        // todo 检查表单验证和权限逻辑

        if (!Permission::checkPermission($request->user->id, ['import-problems']))
            throw new NoPermissionException();

        if (!$request->hasFile('fps')) {
            throw new InnerError("no file to upload");
        }

        $file = $request->file('fps');
        dd($file);

        $isPublic = $request->input('is_public', 1);
        $creatorId = $request->user->id;
        $creatorName = 'fps-auto-import';

        $config = [
            'is_public' => $isPublic,
            'creator_id' => $creatorId,
            'creator_name' => $creatorName
        ];

        $problemIds = $freeProblemSetService->importProblems($file, $config);

        if (empty($problemIds)) {
            throw new InnerError("fail to import problems");
        }

        return response()->json([
            'code' => 0,
            'data' => [
                'problem_ids' => $problemIds
            ]
        ]);
    }

    public function exportProblems(Request $request, FreeProblemSetService $freeProblemSetService)
    {
        $validator = Validator::make($request->all(), [
            'problem_ids' => 'required'
        ]);

        if ($validator->fails()) {
            throw new FormValidatorException($validator->getMessageBag()->all());
        }

        if (!Permission::checkPermission($request->user->id, ['export-problems'])) {
            throw new NoPermissionException();
        }

        $problemIds = $request->input('problem_ids');

        $problems = $freeProblemSetService->exportProblems($problemIds);

        $headers = [
            'Content-Disposition' => 'attachment;filename=fps.xml',
            'Content-type' => 'application/file'
        ];

        return response()->view('xml', ['problems' => $problems], 200, $headers);
    }

    public function generateUsersByPrefix(Request $request)
    {
        Utils::validateCheck($request->all(),[
            'prefix' => 'required|string|max:45',
            'num' => 'required|integer',
            'names' => 'array'
        ]);

        // todo check permission

        $names = $request->input('names',[]);

        $users = $this->adminService->generateUsersByPrefix($request->prefix,$request->num,$names);

        return response()->json([
            'code' => 0,
            'data' => $users
        ]);
    }
}
