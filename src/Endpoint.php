<?php
namespace equilibrium;

abstract class Endpoint {
    public const METHOD_GET = 'get';
    public const METHOD_POST = 'post';
    public const METHOD_PUT = 'put';
    public const METHOD_PATCH = 'patch';
    public const METHOD_DELETE = 'delete';
    public const METHOD_OPTIONS = 'options';
}