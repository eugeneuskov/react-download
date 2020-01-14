<?php


namespace app\middleware;

/**
 * Class Response
 * @package app\services
 */
final class Response extends \React\Http\Response
{
    /**
     * Response constructor.
     * @param int $status
     * @param null $data
     */
    public function __construct(int $status = 200, $data = null)
    {
        parent::__construct(
            $status,
            ['Content-Type' => 'application/json'],
            $data ? json_encode($data) : null
        );
    }

    /**
     * @param string $error
     * @return static
     */
    public static function unauthorized(string $error = 'Unauthorized'): self
    {
        return new self(401, $error);
    }

    /**
     * @param array $data
     * @return static
     */
    public static function ok(array $data): self
    {
        return new self(200, $data);
    }

    /**
     * @param string $url
     * @return static
     */
    public static function downloaded(string $url): self
    {
        return new self(200, $url);
    }

    /**
     * @param string $error
     * @return static
     */
    public static function badRequest(string $error): self
    {
        return new self(400, ['error' => $error]);
    }

    /**
     * @param string $error
     * @return static
     */
    public static function notFound(string $error): self
    {
        return new self(404, ['error' => $error]);
    }

    /**
     * @return static
     */
    public static function noContent(): self
    {
        return new self(204);
    }

    /**
     * @param string $error
     * @param int $code
     * @return static
     */
    public static function error(string $error, int $code): self
    {
        return new self($code, $error);
    }
}