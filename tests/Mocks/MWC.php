<?php

class MWC extends Stackd\Middleware 
{
    public function call($request, $response)
    {
        return 'C';
    }
}
