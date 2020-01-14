<?php


namespace app\services;

use React\Filesystem\Filesystem;
use React\HttpClient\{Client, Request, Response};
use React\Stream\ThroughStream;
use app\middleware\Response as ResponseMiddleware;
use function \React\Promise\Stream\unwrapWritable;

/**
 * Class Download
 *
 * @property Client $client;
 * @property Filesystem $filesystem;
 * @property array $files;
 * @property int $total
 * @property int $done;
 *
 * @package app\services
 */
final class Download
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $files = [];

    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $done;

    /**
     * Download constructor.
     * @param Client $client
     * @param Filesystem $filesystem
     */
    public function __construct(Client $client, Filesystem $filesystem)
    {
        $this->client = $client;
        $this->filesystem = $filesystem;
    }

    /**
     * @param array $files
     * @param string $path
     * @param int $concurrently
     * @return ResponseMiddleware
     */
    public function download(array $files, string $path, int $concurrently = 5)
    {
        $this->files = $files;
        $this->total = count($this->files);
        $this->done = 0;

        $max = $concurrently ?: count($this->files);
        while($max --) {
            $this->runDownload($path);
        }

        echo "Downloaded: 0%\n";

        return ResponseMiddleware::ok(['status' => 'downloading started']);
    }

    private function runDownload(string $path)
    {
        $file = array_shift($this->files);
        $request = $this->initRequest($file, $path);
        $request->end();
    }

    /**
     * @param string $url
     * @param string $path
     * @return Request
     */
    private function initRequest(string $url, string $path)
    {
        $fileName = basename($url);
        $file = unwrapWritable(
            $this->filesystem->file($path . $fileName)->open('cw')
        );

        $request = $this->client->request('GET', $url);
        $request->on(
            'response',
            function (Response $response) use ($file, $path)
            {
                $response->pipe($file);
                $response->on(
                    'end',
                    function () use ($path) {
                        $this->done++;
                        $progress = number_format($this->done / $this->total * 100);
                        echo "\033[1A Downloaded: $progress%\n";

                        if ($this->files) {
                            $this->runDownload($path);
                        }
                    }
                );
            }
        );

        return $request;
    }
}