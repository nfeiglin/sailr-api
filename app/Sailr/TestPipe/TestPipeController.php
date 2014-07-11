<?php
namespace Sailr\TestPipe;
use Illuminate\Filesystem\FileNotFoundException;
use \Illuminate\Routing\Controller;
use \Illuminate\Support\Facades\Response;

class TestPipeController extends Controller {

    /**
     * @var TestPipe $testpipe
     */
    protected $testpipe;


    public function __construct(TestPipe $testPipe) {
        $this->testpipe = $testPipe;
    }
    public function showAsset($path) {
        //dd($path);

        try {
            $asset = $this->testpipe->getAsset($path);
        }

        catch(FileNotFoundException $e) {
            return Response::make('File not found', 404);
        }

        $res = Response::make($asset, 200);
        $res->header('Content-Type', $this->testpipe->getContentType($path), true);
        return $res;
    }
} 