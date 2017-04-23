<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/3/26
 * Time: 下午9:42
 */

namespace NEUQOJ\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use NEUQOJ\Exceptions\FormValidatorException;
use NEUQOJ\Exceptions\InnerError;
use NEUQOJ\Exceptions\NoPermissionException;
use NEUQOJ\Facades\Permission;
use NEUQOJ\Services\NewsService;

class NewsController extends Controller
{

    /**
     * 新闻和公告部分
     */

    private $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }

    public function getAllNews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'size' => 'integer|min:1'
        ]);

        if ($validator->fails()) {
            throw new FormValidatorException($validator->getMessageBag()->all());
        }

        $page = $request->input('page', 1);
        $size = $request->input('size', 20);

        $data = $this->newsService->getAllNews($page, $size);

        return response()->json([
            'code' => 0,
            'data' => $data
        ]);
    }

    /**
     * 用于首页通知，最大20条
     */

    public function getIndexNews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'size' => 'integer|min:1|max:20'
        ]);

        if ($validator->fails()) {
            throw new FormValidatorException($validator->getMessageBag()->all());
        }

        // 默认3条

        $size = $request->input('size', 3);

        $news = $this->newsService->getLatestNews($size);


        // 获取固定的公告内容

        $fixedNews = $this->newsService->getFixedNews();

        return response()->json([
            'code' => 0,
            'data' => [
                'latest_news' => $news,
                'fixed_news' => $fixedNews
            ]
        ]);
    }

    public function getNews(int $newsId)
    {
        $news = $this->newsService->getNews($newsId);

        return response()->json([
            'code' => 0,
            'data' => $news
        ]);
    }

    public function addNews(Request $request)
    {
        // 重要程度：1表示普通，2表示重要，3表示紧急

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'content' => 'required',
            'importance' => 'required|integer|min:0|max:3',
        ]);

        if ($validator->fails()) {
            throw new FormValidatorException($validator->getMessageBag()->all());
        }

        if (!Permission::checkPermission($request->user->id, ['add-news'])) {
            throw new NoPermissionException();
        }

        $news = [
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'importance' => $request->input('importance'),
            'author_id' => $request->user->id,
            'author_name' => $request->user->name
        ];

        if (!$this->newsService->addNews($news)) {
            throw new InnerError("Fail to add News");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function updateNews(Request $request, int $newsId)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'content' => 'required',
            'importance' => 'required|integer|min:0|max:3',
        ]);

        if ($validator->fails()) {
            throw new FormValidatorException($validator->getMessageBag()->all());
        }

        if (!Permission::checkPermission($request->user->id, ['update-news'])) {
            throw new NoPermissionException();
        }

        $newNews = [
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'importance' => $request->input('importance')
        ];

        if (!$this->newsService->updateNews($newsId, $newNews)) {
            throw new InnerError("Fail to update news");
        }

        return response()->json([
            'code' => 0
        ]);
    }

    public function deleteNews(Request $request, int $newsId)
    {
        if (!Permission::checkPermission($request->user->id, ['delete-news'])) {
            throw new NoPermissionException();
        }

        if (!$this->newsService->deleteNews($newsId)) {
            throw new InnerError("Fail to delete News");
        }

        return response()->json([
            'code' => 0
        ]);
    }

}