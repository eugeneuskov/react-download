<?php


namespace app\controllers;


use app\exceptions\InvalidDataError;
use app\middleware\Response;
use app\services\Download;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class DownloadController
 *
 * @property Download $downloadService
 *
 * @package app\controllers
 */
final class DownloadController
{
    /**
     * @var Download
     */
    private $downloadService;

    /**
     * DownloadController constructor.
     * @param Download $downloadService
     */
    public function __construct(Download $downloadService)
    {
        $this->downloadService = $downloadService;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $downloadData = json_decode((string)$request->getBody(), true);

        $url = $downloadData['url'] ?? null;
        $format = $downloadData['format'] ?? null;
        $from = $downloadData['from'] ?? null;
        $to = $downloadData['to'] ?? null;
        $path = $downloadData['path'] ?? '/media/eugene/STAR/video/from_react_script/';
        $concurrently = (int)$downloadData['concurrently'] ?? null;

        if (!($url && $format && $from && $to && $path)) {
            return Response::error(404,'Invalidate data for download');
        }

        $files = [];
        while ($from <= $to) {
            $files[] = $url . str_replace('{N}', $from, $format);
            $from++;
        }

        return $this->downloadService->download($files, $path, $concurrently);
    }
}