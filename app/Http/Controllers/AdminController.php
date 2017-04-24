<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Facades\Permission;
use NEUQOJ\Services\FreeProblemSetService;
use NEUQOJ\Services\UserService;

class AdminController extends Controller
{
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

        $file = $request->file('fps');

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
}
