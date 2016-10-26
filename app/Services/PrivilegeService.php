<?php
/**
 * Created by PhpStorm.
 * User: yz
 * Date: 16-10-26
 * Time: 下午8:07
 */

namespace NEUQOJ\Services;


class PrivilegeService
{
    private $privilegeRepository;
    private $usrPriRepository;

    public function __construct(PrivilegeRepository $privilegeRepository,UsrPriRepository $usrPriRepository)
    {
        $this->privilegeRepository= $privilegeRepository;
        $this->usrPriRepository =  $usrPriRepository;
    }
    /*
     * 确认做出请求的用户的角色
     */

    public function confirmRole()
    {

    }

    public function findRole()
    {

    }

}