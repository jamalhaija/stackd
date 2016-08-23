<?php

class MWA extends Stackd\Middleware 
{
    public function call($request, $response)
    {
        return 'A' . $this->next($request, $response) . 'A';
    }
}
