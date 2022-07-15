<?php

namespace FluentQueryLogger\App\Http\Policies;

use FluentQueryLogger\Framework\Request\Request;
use FluentQueryLogger\Framework\Foundation\Policy;

class AdminPolicy extends Policy
{
    /**
     * Check user permission for any method
     * @param  \FluentQueryLogger\Framework\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return current_user_can('manage_options');
    }

    /**
     * Check user permission for any method
     * @param  \FluentQueryLogger\Framework\Request\Request $request
     * @return Boolean
     */
    public function create(Request $request)
    {
        return current_user_can('manage_options');
    }
}
