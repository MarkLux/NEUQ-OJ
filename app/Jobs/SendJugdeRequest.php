<?php

namespace NEUQOJ\Jobs;


use function GuzzleHttp\Psr7\str;
use NEUQOJ\Exceptions\Judge\JudgeServerStatusErrorException;
use NEUQOJ\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use NEUQOJ\Repository\Models\JudgeServer;
use NEUQOJ\Services\CacheService;
use NEUQOJ\Services\ProblemService;
use NEUQOJ\Services\SolutionService;
use NEUQOJ\Services\UserService;
use Redis;


class SendJugdeRequest extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $problemId;
    protected $data;
    protected $problemNum;
    protected $userId;
    protected $type;//1 contestService 2 problemService 3 rejudge
    protected $solutionId;
    protected $key;

    /**
     * SendJugdeRequest constructor.
     * @param $serverURL
     * @param $headers
     * @param $body
     */
    public function __construct($solutionId, $problemId, $data, $problemNum = -1, $userId, $type)
    {
        $this->problemId = $problemId;
        $this->data = $data;
        $this->problemNum = $problemNum;
        $this->userId = $userId;
        $this->solutionId = $solutionId;
        $this->key = 'solution:' . $solutionId;
        $this->type = $type;
    }


    /**
     * Execute the job.
     *
     * @param ProblemService $problemService
     * @return void
     */
    public function handle(CacheService $cacheService, ProblemService $problemService, UserService $userService, SolutionService $solutionService)
    {
        $cacheService->setJudgeResult($this->key, ['result' => -2, 'data' => '开始判题'], 100);
        $result = $problemService->submitProblem($this->solutionId, $this->problemId, $this->data, $this->problemNum);
        $res = [
            'result' => $result['result'],
            'data' => $result['data']
        ];
        $cacheService->setJudgeResult($this->key, $res, 100);
        $detail = $solutionService->getSolution($this->solutionId);
        $user = $userService->getUserById($detail['user_id']);
        if ($this->type == 2) {
            // 单次普通提交
            if ($result['result'] == 4) {
                if (!$solutionService->isUserAc($detail['user_id'], $detail['problem_id'])) {

                    $userService->updateUserById($user->id, ['submit' => $user->submit + 1, 'solved' => $user->solved + 1]);
                } else {

                    $userService->updateUserById($user->id, ['submit' => $user->submit + 1]);
                }
            } else {
                $userService->updateUserById($user->id, ['submit' => $user->submit + 1]);
            }
        } else if ($this->type == 1) {
            // 比赛提交
            if ($result['result'] == 4) {
                $userService->updateUserById($user->id, ['submit' => $user->submit + 1, 'solved' => $user->solved + 1]);
            } else {
                $userService->updateUserById($user->id, ['submit' => $user->submit + 1]);
            }
        } else if ($this->type == 3) {
            // 重判
            if ($result['result'] == 4) {
                if (!$solutionService->isUserAc($detail['user_id'], $detail['problem_id'])) {
                    $userService->updateUserById($user->id, ['solved' => $user->solved + 1]);
                }
            }
        }
    }

    /**
     * 要处理的失败任务。
     *
     *
     * @return void
     */
    public function failed(CacheService $cacheService)
    {
        $cacheService->setJudgeResult($this->key, ['result' => -1, 'data' => '服务器异常'], 100);
    }
}
