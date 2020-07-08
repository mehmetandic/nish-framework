<?php

namespace Nish\Utils\Http;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse
{
    protected static $defaultCharset;
    protected static $defaultHeaders = [];

    protected const UPPER = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected const LOWER = '-abcdefghijklmnopqrstuvwxyz';

    public static function setDefaultCharset(string $charset)
    {
        self::$defaultCharset = $charset;
    }

    public static function getDefaultCharset()
    {
        return self::$defaultCharset;
    }

    public static function addDefaultHeader(string $key, $value)
    {
        $key = strtr($key, self::UPPER, self::LOWER);
        self::$defaultHeaders[$key] = $value;
    }

    public static function removeDefaultHeader(string $key)
    {
        $key = strtr($key, self::UPPER, self::LOWER);

        if (isset(self::$defaultHeaders[$key])) {
            unset(self::$defaultHeaders[$key]);
        }
    }

    public static function hasDefaultHeader(string $key)
    {
        $key = strtr($key, self::UPPER, self::LOWER);

        if (array_key_exists($key, self::$defaultHeaders) && !empty(self::$defaultHeaders[$key])) {
            return true;
        }

        return false;
    }

    /**
     * @param string $content
     * @param int $statusCode
     * @param array|null $headers
     * @param \Symfony\Component\HttpFoundation\Cookie[]|null $addedCookies
     * @param string|null $charset
     * @param array|null $removedCookieNames
     */
    public static function sendResponse(string $content, int $statusCode = self::HTTP_OK, array $headers = null, array $addedCookies = null, string $charset = null, array $removedCookieNames = null)
    {
        if (empty($charset)) $charset = self::$defaultCharset;

        if (empty($headers)) $headers = [];

        $headers = array_merge(self::$defaultHeaders, $headers);

        $response = new self($content);
        $response->setCharset($charset)
            ->setStatusCode($statusCode);

        foreach ($headers as $key=>$value) {
            $response->headers->set($key, $value, true);
        }

        if (!is_array($addedCookies) && !empty($addedCookies)) {
            /* @var \Symfony\Component\HttpFoundation\Cookie $cookie */
            foreach ($addedCookies as $cookie) {
                $response->headers->setCookie($cookie);
            }
        }

        if (!is_array($removedCookieNames) && !empty($removedCookieNames)) {
            foreach ($removedCookieNames as $name) {
                $response->headers->removeCookie($name);
            }
        }

        $response->send();
    }

    public static function sendJSONResponse($contentObject, int $statusCode = self::HTTP_OK, array $headers = null, array $addedCookies = null, string $charset = null, array $removedCookieNames = null)
    {
        if (empty($headers)) $headers = [];

        $headers['Content-Type'] = 'application/json';

        self::sendResponse(json_encode($contentObject), $statusCode, $headers, $addedCookies, $charset, $removedCookieNames);
    }
}