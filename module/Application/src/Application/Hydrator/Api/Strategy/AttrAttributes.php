<?php

namespace Application\Hydrator\Api\Strategy;

use Application\Hydrator\Api\AttrAttributeHydrator as Hydrator;

class AttrAttributes extends HydratorStrategy
{
    private int $userId;

    protected function getHydrator(): Hydrator
    {
        if (! $this->hydrator) {
            $this->hydrator = new Hydrator($this->serviceManager);
        }

        return $this->hydrator;
    }

    public function extract($value): array
    {
        $hydrator = $this->getHydrator();

        $hydrator->setFields($this->fields);
        $hydrator->setLanguage($this->language);
        $hydrator->setUserId($this->userId);

        $result = [];
        foreach ($value as $row) {
            $result[] = $hydrator->extract($row);
        }
        return $result;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
