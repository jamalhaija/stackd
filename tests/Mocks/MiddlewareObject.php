<?php
class MiddlewareObject extends Stackd\Middleware
{
    public function call($request, $response)
    {
        return 'Middleware 1';
    }
}
