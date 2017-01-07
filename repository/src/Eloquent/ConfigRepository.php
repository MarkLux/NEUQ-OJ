<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17-1-7
 * Time: 下午10:00
 */

namespace NEUQOJ\Repository\Eloquent;


class ConfigRepository extends AbstractRepository
{
    public function model()
    {
        return "NEUQOJ\Repository\Models\Config";
    }
}