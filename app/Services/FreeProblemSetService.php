<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 17/4/19
 * Time: 下午10:13
 */

namespace NEUQOJ\Services;


use Illuminate\Support\Facades\File;
use League\Flysystem\Directory;
use NEUQOJ\Common\Utils;
use NEUQOJ\Repository\Eloquent\ProblemRepository;
use NEUQOJ\Services\Contracts\FreeProblemSetServiceInterface;

class FreeProblemSetService implements FreeProblemSetServiceInterface
{

    private $problemRepo;

    public function __construct(ProblemRepository $problemRepo)
    {
        $this->problemRepo = $problemRepo;
    }

    private function getValue($Node, $TagName)
    {
        return $Node->$TagName;
    }

    private function getAttribute($Node, $TagName, $attribute)
    {
        return $Node->children()->$TagName->attributes()->$attribute;
    }

    private function makeData(int $problemId,string $filename,$data)
    {
        $path = Utils::getProblemDataPath($problemId);

        File::put($path.$filename,$data);
    }

    private function getData(int $problemId,string $filename)
    {
        $path = Utils::getProblemDataPath($problemId);
        return File::get($path.$filename);
    }

    public function importProblems($file, array $config)
    {
        $problemOuts = [];
        $xmlDoc = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_PARSEHUGE);
        $searchNodes = $xmlDoc->xpath("/fps/item");

        foreach ($searchNodes as $searchNode) {
            //echo $searchNode->title,"\n";

            $title = $searchNode->title;

            $timeLimit = $searchNode->time_limit;
            $unit = $this->getAttribute($searchNode, 'time_limit', 'unit');
            //echo $unit;
            if ($unit == 'ms') $timeLimit /= 1000;

            $memoryLimit = $this->getValue($searchNode, 'memory_limit');
            $unit = $this->getAttribute($searchNode, 'memory_limit', 'unit');
            if ($unit == 'kb') $memoryLimit /= 1024;

            $description = $this->getValue($searchNode, 'description');
            $input = $this->getValue($searchNode, 'input');
            $output = $this->getValue($searchNode, 'output');
            $sampleInput = $this->getValue($searchNode, 'sample_input');
            $sampleOutput = $this->getValue($searchNode, 'sample_output');

            $hint = $this->getValue($searchNode, 'hint');
            $source = $this->getValue($searchNode, 'source');

            $spjcode = $this->getValue($searchNode, 'spj');
            $spj = trim($spjcode) ? 1 : 0;

            // TODO 从solution节点取出标准题解

            $problem = [
                'title' => (string)$title,
                'description' => (string)$description,
                'creator_id' => $config['creator_id'],
                'creator_name' => $config['creator_name'],
                'input' => (string)$input,
                'output' => (string)$output,
                'sample_input' => (string)$sampleInput,
                'sample_output' => (string)$sampleOutput,
                'hint' => (string)$hint,
                'source' => (string)$source,
                'time_limit' => (int)$timeLimit,
                'memory_limit' => (int)$memoryLimit,
                'is_public' => $config['is_public'],
                'spj' => $spj
            ];

            $problemId = $this->problemRepo->insertWithId($problem);

            // 样例

            if (!File::makeDirectory(Utils::getProblemDataPath($problemId), $mode = 0755))
                return false;

            $this->makeData($problemId, 'sample.in', $sampleInput);
            $this->makeData($problemId, 'sample.out', $sampleOutput);

            $testInputs = $searchNode->children()->test_input;

            $testNum = 0;

            foreach ($testInputs as $testInput) {
                $this->makeData($problemId, 'test' . $testNum++ . ".in", $testInput);
            }

            $testOutputs = $searchNode->children()->test_output;

            $testNum = 0;

            foreach ($testOutputs as $testOutput) {
                $this->makeData($problemId, 'test' . $testNum++ . ".out", $testOutput);
            }

            $problemOuts[] = [
                'problem_id' => $problemId,
                'problem_title' => (string)$title
            ];

        }

        return $problemOuts;
    }

    public function exportProblems(array $problemIds)
    {
        $problems = $this->problemRepo->getIn('id',$problemIds,[
            'id','title','description','input','output','sample_input',
            'sample_output','hint','source','time_limit','memory_limit',
            'is_public','spj'
        ])->toArray();

        foreach ($problems as &$problem)
        {
            $files = File::files(Utils::getProblemDataPath($problem['id']));

            $testIns = [];
            $testOuts = [];

            foreach ($files as $file) {
                if ($file == Utils::getProblemDataPath($problem['id']).'sample.in' || $file == Utils::getProblemDataPath($problem['id']).'sample.out')
                    continue;
                if (File::extension($file) == 'in') {
                    $testIns[] = file_get_contents($file);
                }
                if (File::extension($file) == 'out') {
                    $testOuts[] = file_get_contents($file);
                }
            }

            $problem['test_input'] = $testIns;
            $problem['test_output'] = $testOuts;
        }

        return $problems;

    }
}