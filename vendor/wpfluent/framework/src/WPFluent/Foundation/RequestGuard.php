<?php

namespace FluentQueryLogger\Framework\Foundation;

use FluentQueryLogger\Framework\Foundation\App;
use FluentQueryLogger\Framework\Validator\Validator;
use FluentQueryLogger\Framework\Validator\ValidationException;

abstract class RequestGuard
{
    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }

    public function sanitize()
    {
        return [];
    }

    public function validate(Validator $validator)
    {
        try {

            if (!($rules = (array) $this->rules())) return;

            $validator = $validator->make($data = $this->all(), $rules, (array) $this->messages());

            if ($validator->validate()->fails()) {
                throw new ValidationException('Unprocessable Entity!', 422, null, $validator->errors());
            }

            return $data;

        } catch (ValidationException $e) {

            if (defined('REST_REQUEST') && REST_REQUEST) {
                throw $e;
            } else {
                App::getInstance()->doCustomAction('handle_exception', $e);
            }
        }
    }

    /**
     * Get an input element from the request.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    public function __call($method, $params)
    {
        return call_user_func_array(
            [App::getInstance('request'), $method], $params
        );
    }
}
