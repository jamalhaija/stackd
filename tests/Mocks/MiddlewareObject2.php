<?php
class MiddlewareObject2 extends Stackd\Middleware
{
    public function call($request, $response)
    {
        return 'Middleware 2';
    }
}
