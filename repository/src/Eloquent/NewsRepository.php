<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-10-12
 * Time: 下午9:12
 */
namespace NEUQOJ\Repository\Eloquent;

class NewsRepository extends AbstractRepository
{

    function model()
    {
        return "NEUQOJ\Repository\Models\News";
    }
}