<?php

namespace FluentQueryLogger\App\Http\Requests;

use FluentQueryLogger\Framework\Foundation\RequestGuard;

class UserRequest extends RequestGuard
{
    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * @return array
     */
    public function sanitize()
    {
        return $this->all();
    }
}
