<?php

namespace NEUQOJ\Http\Controllers;

use Illuminate\Http\Request;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Services\FreeProblemSetService;
use NEUQOJ\Services\UserService;

class AdminController extends Controller
{
    public function lockUser(UserService $userService,$id)
    {
        if($userService->lockUser($id))
            return response()->json([
                'code' => '0'
            ]);
    }

    public function unlockUser(UserService $userService,$id)
    {
        if($userService->unlockUser($id))
            return response()->json([
                'code' => '0'
            ]);
    }

    public function importProblem(Request $request,FreeProblemSetService $freeProblemSetService)
    {
        $file = $request->file('fps');

        $config = [
            'is_public' => 1,
            'creator_id' => 0,
            'creator_name' => 'NEUQer'
        ];

        $problemIds = $freeProblemSetService->importProblems($file,$config);

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
}
