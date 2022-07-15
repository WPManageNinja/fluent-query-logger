<?php

namespace FluentQueryLogger\App\Models;

use FluentQueryLogger\Framework\Database\Orm\Model as BaseModel;

class Model extends BaseModel
{
    protected $guarded = ['id', 'ID'];


    public function getPerPage()
    {
        return (isset($_REQUEST['per_page'])) ? intval($_REQUEST['per_page']) : 15;
    }
}
