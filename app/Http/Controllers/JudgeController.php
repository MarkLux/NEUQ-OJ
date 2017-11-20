<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/8/23
 * Time: 上午10:56
 */

namespace NEUQOJ\Http\Controllers;

use NEUQOJ\Common\Utils;
use NEUQOJ\Exceptions\InnerError;
use Illuminate\Http\Request;
use NEUQOJ\Exceptions\Judge\JudgeServerNotExistException;
use NEUQOJ\Facades\Permission;
use NEUQOJ\Services\JudgeService;

class JudgeController extends Controller
{
    private $judgeService;

    public function __construct(JudgeService $judgeService)
    {
        $this->judgeService = $judgeService;
        $this->middleware('token');
    }

    public function index(Request $request)
    {
        return response()->json([
            'code' => 0,
            'data' => $this->judgeService->getAllJudgeServerInfo()
        ]);
    }

    public function getAll(Request $request)
    {
        return response()->json([
            'code' => 0,
            'data' => $this->judgeService->getAllJudgeServer()
        ]);
    }

    public function addServer(Request $request)
    {
        Utils::validateCheck($request->all(),[
            'name' => 'required|unique:judge_servers',
            'rpc_token' => 'string|max:255',
            'host' => 'required',
            'port' => 'required',
            'status' => 'required|integer',
        ]);

        $server = [
            'name' => $request->name,
            'rpc_token' => $request->rpc_token,
            'host' => $request->host,
            'port' => $request->port,
            'status' => $request->status,
        ];

        $serverId = $this->judgeService->addJudgeServer($server);

        $serverInfo = $this->judgeService->refreshServerStatus($serverId);

        return response()->json([
            'code' => 0,
            'data' => [
                'id' => $serverId,
                'info' => $serverInfo
            ]
        ]);
    }

    public function updateServer(Request $request,int $serverId)
    {
        Utils::validateCheck($request->all(),[
            'rpc_token' => 'string|max:255',
            'host' => 'required',
            'port' => 'required',
            'status' => 'required|integer',
        ]);

        $server = [
            'rpc_token' => $request->rpc_token,
            'host' => $request->host,
            'port' => $request->port,
            'status' => $request->status,
        ];

        if (!$this->judgeService->updateServer($serverId,$server)) {
            throw new InnerError("Fail to update server info");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function deleteServer(Request $request,int $serverId)
    {
        if (!$this->judgeService->deleteServer($serverId)) {
            throw new InnerError("Fail to delete server");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function getServerInfo(Request $request,int $id)
    {
        return response()->json([
            'code' => 0,
            'data' => $this->judgeService->refreshServerStatus($id)
        ]);
    }

    public function getServer(Request $request,int $id)
    {
        $server = $this->judgeService->getSingleJudger($id);
        if ($server == null) {
            throw new JudgeServerNotExistException();
        }
        return response()->json([
            'code' => 0,
            'data' => $server
        ]);
    }

    public function getJudgeResult(Request $request,int $solutionId){

        //业务层先不加
        $result = $this->judgeService->getJudgeResult($solutionId);

        return response()->json([
            'code'=> 0,
            'answer'=>json_decode($result)
        ]);
    }
}