<?php

namespace Application\Most\Adapter;

use ArrayAccess;
use Exception;
use Laminas\Db\Sql;

class Attr extends AbstractAdapter
{
    /** @var array|ArrayAccess */
    protected $attribute;

    protected string $order;

    public function setAttribute(int $value): void
    {
        $this->attribute = $value;
    }

    public function setOrder(string $value): void
    {
        $this->order = $value;
    }

    /**
     * @throws Exception
     */
    public function getCars(Sql\Select $select, string $language): array
    {
        $attribute = $this->attributeTable->select(['id' => (int) $this->attribute])->current();
        if (! $attribute) {
            throw new Exception("Attribute '{$this->attribute}' not found");
        }

        $specService = $this->most->getSpecs();

        $valuesTable = $specService->getValueDataTable($attribute['type_id']);
        $tableName   = $valuesTable->getTable();

        $select
            ->where([
                $tableName . '.attribute_id' => $attribute['id'],
                $tableName . '.value IS NOT NULL',
            ])
            ->join($tableName, 'item.id = ' . $tableName . '.item_id', [])
            ->order($tableName . '.value ' . $this->order)
            ->group(['item.id', $tableName . '.value']);

        $result = [];
        foreach ($this->itemTable->selectWith($select) as $car) {
            $valueText = $specService->getActualValueText($attribute['id'], $car['id'], $language);

            $result[] = [
                'car'       => $car,
                'valueText' => $valueText,
            ];
        }

        return [
            'unit' => $specService->getUnit($attribute['unit_id']),
            'cars' => $result,
        ];
    }
}
