<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/9/1
 * Time: 上午10:26
 */

namespace NEUQOJ\Http\Controllers;


use Illuminate\Support\Facades\File;
use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Facades\Permission;
use Illuminate\Http\Request;
use NEUQOJ\Services\JudgeService;
use NEUQOJ\Services\RunDataService;

class RunDataController extends Controller
{
    private $runDataService;
    private $judgeService;

    public function __construct(RunDataService $runDataService, JudgeService $judgeService)
    {
        $this->runDataService = $runDataService;
        $this->judgeService = $judgeService;
        $this->middleware("token");
    }

    public function getRunDataList(Request $request, int $problemId)
    {
        if (!Permission::checkPermission($request->user->id, ['get-run-data'])) {
            throw new NoPermissionException();
        }

        $files = $this->runDataService->getRunDataList($problemId);

        return response()->json([
            'code' => 0,
            'data' => [
                'files' => $files
            ]
        ]);
    }

    public function getRunData(Request $request)
    {
        Utils::validateCheck($request->all(), [
            'file_path' => 'required|string'
        ]);

        if (!Permission::checkPermission($request->user->id, ['get-run-data'])) {
            throw new NoPermissionException();
        }

        $ext = substr(strrchr($request->file_path, '.'), 1);

        if ($ext != 'in' && $ext != 'out') {
            throw new FormValidatorException(['Invalid File Extension']);
        }

        if (!File::isFile($request->file_path)) {
            throw new InnerError('File Not Exist');
        }

        return response()->download($request->file_path, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Cookie, Accept,token,Accept,X-Requested-With',
            'Access-Control-Allow-Methods' => 'GET, POST, PATCH,DELETE,PUT, OPTIONS',
            'Access-Control-Allow-Credentials' => 'true'
        ]);
    }

    public function uploadRunData(Request $request, int $problemId)
    {
        if (!Permission::checkPermission($request->user->id, ['get-run-data'])) {
            throw new NoPermissionException();
        }

        if (!$request->hasFile('upload')) {
            throw new FormValidatorException(['upload file required']);
        }

        $testFile = $request->file('upload');

        if ($testFile->extension() != 'in' && $testFile->extension() != 'out') {
            throw new FormValidatorException(['invalid file extension']);
        }

        // save the file into target dir

        $testFile->move(Utils::getProblemDataPath($problemId), $testFile->getClientOriginalName());

        $data = $this->judgeService->rsyncTestCase($problemId);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    public function deleteRunData(Request $request, int $id)
    {
        Utils::validateCheck($request->all(), [
            'file_path' => 'required|string',
        ]);

        if (!Permission::checkPermission($request->user->id, ['get-run-data'])) {
            throw new NoPermissionException();
        }

        if (!File::isFile($request->file_path)) {
            throw new InnerError('File Not Exist');
        }

        File::delete($request->file_path);

        $data = $this->judgeService->rsyncTestCase($id);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }
}