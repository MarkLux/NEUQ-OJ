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
use NEUQOJ\Services\JudgeService;

class JudgeController extends Controller
{
    private $judgeService;

    public function __construct(JudgeService $judgeService)
    {
        $this->judgeService = $judgeService;
//        $this->middleware('token');
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

    public function getServerInfo(Request $request,int $id)
    {
        return response()->json([
            'code' => 0,
            'data' => $this->judgeService->refreshServerStatus($id)
        ]);
    }
}