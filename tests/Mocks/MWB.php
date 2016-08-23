<?php

class MWB extends Stackd\Middleware 
{
    public function call($request, $response)
    {
        return 'B' . $this->next($request, $response) . 'B';
    }
}
