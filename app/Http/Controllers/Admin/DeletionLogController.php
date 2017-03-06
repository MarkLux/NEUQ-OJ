<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-11-28
 * Time: 下午5:03
 */

namespace NEUQOJ\Http\Controllers\Admin;


use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Http\Controllers\Controller;
use NEUQOJ\Services\DeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeletionLogController extends Controller
{

    private $deletionService;

    public function __construct(DeletionService $deletionService)
    {
        $this->deletionService = $deletionService;
    }

    public function getLog(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'size' => 'integer|min:1|max:50',
            'page' => 'integer|min:1|max:500',
        ]);

        if($validator->fails())
            throw new FormValidatorException($validator->getMessageBag()->all());

        $size = $request->input('size',10);
        $page = $request->input('page',1);

        $total_count = $this->deletionService->getDeletionCount();

        $data = $this->deletionService->getLog($page,$size);

        return response()->json([
            'code' =>0,
            'data' => $data,
            "page_count" => ($total_count%$size)?intval($total_count/$size+1):($total_count/$size)
        ]);
    }

    public function confirmDeletion(int $id)
    {
        if(!$this->deletionService->confirmDeletion($id))
            throw new InnerError("Fail to confirm deletion :".$id);
        return response()->json([
            'code' => 0
        ]);
    }

    public function undoDeletion(int $id)
    {
        if(!$this->deletionService->undoDeletion($id))
            throw new InnerError("Fail to undo deletion :".$id);
        return response()->json([
            'code' => 0
        ]);
    }

    //TODO:重新组织信息提交方式（POST+批量处理，优化底层数据库部分）
}