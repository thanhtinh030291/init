<?php

namespace Lza\LazyAdmin\Utility\Tool;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
interface ApiHandler
{
    const HEADER_AUTHORIZATION = 'Authorization';

    const HTTP_STATUS_SUCCESS = 200;
    const HTTP_STATUS_CREATED = 201;
    const HTTP_STATUS_NO_CONTENT = 204;
    const HTTP_STATUS_NOT_MODIFIED = 304;
    const HTTP_STATUS_BAD_REQUEST = 400;
    const HTTP_STATUS_UNAUTHORIZED = 401;
    const HTTP_STATUS_FORBIDDEN = 403;
    const HTTP_STATUS_NOT_FOUND = 404;
    const HTTP_STATUS_CONFLICT = 409;
    const HTTP_STATUS_PRECONDITION_FAILED = 412;
    const HTTP_STATUS_TOO_MANY_REQUESTS = 429;
    const HTTP_STATUS_INTERNAL_SERVER_ERROR = 500;
    const HTTP_STATUS_UNAVAILABLE = 503;

    function get($uri, $callback);
    function post($uri, $callback);
    function put($uri, $callback);
    function patch($uri, $callback);
    function delete($uri, $callback);
}
