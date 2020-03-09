<?php

namespace Application\Hydrator\Api\Strategy;

use Application\Hydrator\Api\PictureHydrator as Hydrator;

class Picture extends HydratorStrategy
{
    protected function getHydrator(): Hydrator
    {
        if (! $this->hydrator) {
            $this->hydrator = new Hydrator($this->serviceManager);
        }

        return $this->hydrator;
    }
}
