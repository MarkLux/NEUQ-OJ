<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/3/26
 * Time: 下午9:01
 */

namespace NEUQOJ\Services;


use NEUQOJ\Exceptions\Other\NewsNotExistException;
use NEUQOJ\Repository\Eloquent\NewsRepository;
use NEUQOJ\Services\Contracts\NewsServiceInterface;

class NewsService implements NewsServiceInterface
{
    private $newsRepository;

    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }

    // 分页获取所有的通知列表

    public function getAllNews(int $page, int $size)
    {
        return $this->newsRepository->paginate($page,$size,['id','author_id','title','importance','created_at','updated_at']);
    }

    public function getNews(int $newsId,array $columns = ['*'])
    {
        $news = $this->newsRepository->get($newsId,$columns)->first();

        if($news == null){
            throw new NewsNotExistException();
        }

        return $news;
    }


    public function getLatestNews(int $size)
    {
        return $this->newsRepository->getLatestNews($size);
    }

    public function addNews(array $news): bool
    {
        $converter = app('CommonMarkService');

        $news['content'] = $converter->convertToHtml($news['content']);

        return $this->newsRepository->insert($news) == 1;
    }

    public function updateNews(int $newsId, array $news): bool
    {
        return $this->newsRepository->update($news,$newsId) == 1;
    }

    public function deleteNews(int $newsId): bool
    {
        return $this->newsRepository->deleteWhere(['id' => $newsId]) == 1;
    }
}