<?php

namespace FluentQueryLogger\Framework\Support;

interface QueueableEntity
{
    /**
     * Get the queueable identity for the entity.
     *
     * @return mixed
     */
    public function getQueueableId();
}
