<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/3/26
 * Time: 下午8:48
 */

namespace NEUQOJ\Services\Contracts;


interface NewsServiceInterface
{
    function getAllNews(int $page,int $size);

    function getLatestNews(int $size);

    function getNews(int $newsId,array $columns = ['*']);

    function addNews(array $news):bool;

    function updateNews(int $newsId,array $news):bool;

    function deleteNews(int $newsId):bool;

    function getFixedNews();
}