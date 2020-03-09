<?php

namespace Application\Controller\Api;

use Application\Hydrator\Api\RestHydrator;
use Autowp\User\Controller\Plugin\User;
use Laminas\Db\Sql;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

/**
 * @method User user($user = null)
 * @method ViewModel forbiddenAction()
 * @method string language()
 */
class PerspectiveController extends AbstractRestfulController
{
    /** @var TableGateway */
    private TableGateway $table;

    /** @var RestHydrator */
    private RestHydrator $hydrator;

    public function __construct(RestHydrator $hydrator, TableGateway $table)
    {
        $this->hydrator = $hydrator;
        $this->table    = $table;
    }

    public function indexAction()
    {
        if (! $this->user()->inheritsRole('moder')) {
            return $this->forbiddenAction();
        }

        $this->hydrator->setOptions([
            'language' => $this->language(),
            'fields'   => [],
        ]);

        $select = new Sql\Select($this->table->getTable());
        $select->order('position');

        $rows  = $this->table->selectWith($select);
        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->hydrator->extract($row);
        }

        return new JsonModel([
            'items' => $items,
        ]);
    }
}
