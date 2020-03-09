<?php

namespace Application\Controller\Api;

use Application\Hydrator\Api\RestHydrator;
use Autowp\Message\MessageService;
use Autowp\User\Model\User;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\InputFilter\InputFilter;
use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

use function get_object_vars;

/**
 * @method \Autowp\User\Controller\Plugin\User user($user = null)
 * @method ViewModel forbiddenAction()
 * @method ApiProblemResponse inputFilterResponse(InputFilter $inputFilter)
 * @method string language()
 */
class MessageController extends AbstractRestfulController
{
    /** @var MessageService */
    private MessageService $message;

    /** @var RestHydrator */
    private RestHydrator $hydrator;

    /** @var InputFilter */
    private InputFilter $listInputFilter;

    /** @var InputFilter */
    private InputFilter $postInputFilter;

    /** @var User */
    private User $userModel;

    public function __construct(
        RestHydrator $hydrator,
        MessageService $message,
        InputFilter $listInputFilter,
        InputFilter $postInputFilter,
        User $userModel
    ) {
        $this->message         = $message;
        $this->hydrator        = $hydrator;
        $this->listInputFilter = $listInputFilter;
        $this->postInputFilter = $postInputFilter;
        $this->userModel       = $userModel;
    }

    public function postAction()
    {
        $currentUser = $this->user()->get();
        if (! $currentUser) {
            return $this->forbiddenAction();
        }

        $request = $this->getRequest();
        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            $data = $this->jsonDecode($request->getContent());
        } else {
            /* @phan-suppress-next-line PhanUndeclaredMethod */
            $data = $request->getPost()->toArray();
        }

        $this->postInputFilter->setData($data);

        if (! $this->postInputFilter->isValid()) {
            return $this->inputFilterResponse($this->postInputFilter);
        }

        $data = $this->postInputFilter->getValues();

        $user = $this->userModel->getRow((int) $data['user_id']);
        if (! $user) {
            return $this->notFoundAction();
        }

        if ($currentUser['id'] === $user['id']) {
            return $this->forbiddenAction();
        }

        $this->message->send($currentUser['id'], $user['id'], $data['text']);

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(201);
    }

    public function indexAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        $this->listInputFilter->setData($this->params()->fromQuery());

        if (! $this->listInputFilter->isValid()) {
            return $this->inputFilterResponse($this->listInputFilter);
        }

        $params = $this->listInputFilter->getValues();

        $messages = [];
        switch ($params['folder']) {
            case 'inbox':
                $messages = $this->message->getInbox($user['id'], (int) $params['page']);
                break;
            case 'sent':
                $messages = $this->message->getSentbox($user['id'], (int) $params['page']);
                break;
            case 'system':
                $messages = $this->message->getSystembox($user['id'], (int) $params['page']);
                break;
            case 'dialog':
                $messages = $this->message->getDialogbox(
                    $user['id'],
                    (int) $params['user_id'],
                    (int) $params['page']
                );
                break;
        }

        $this->hydrator->setOptions([
            'language' => $this->language(),
            'fields'   => $params['fields'],
            'user_id'  => $user ? $user['id'] : null,
        ]);

        $items = [];
        foreach ($messages['messages'] as $row) {
            $items[] = $this->hydrator->extract($row);
        }

        return new JsonModel([
            'paginator' => get_object_vars($messages['paginator']->getPages()),
            'items'     => $items,
        ]);
    }

    public function deleteListAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        $this->listInputFilter->setData($this->params()->fromQuery());

        if (! $this->listInputFilter->isValid()) {
            return $this->inputFilterResponse($this->listInputFilter);
        }

        $params = $this->listInputFilter->getValues();

        switch ($params['folder']) {
            case 'sent':
                $this->message->deleteAllSent($user['id']);
                break;
            case 'system':
                $this->message->deleteAllSystem($user['id']);
                break;
            default:
                return $this->notFoundAction();
        }

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(204);
    }

    public function deleteAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        $this->message->delete($user['id'], (int) $this->params('id'));

        /* @phan-suppress-next-line PhanUndeclaredMethod */
        return $this->getResponse()->setStatusCode(204);
    }

    public function summaryAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        return new JsonModel([
            'inbox'  => [
                'count'     => $this->message->getInboxCount($user['id']),
                'new_count' => $this->message->getInboxNewCount($user['id']),
            ],
            'sent'   => [
                'count' => $this->message->getSentCount($user['id']),
            ],
            'system' => [
                'count'     => $this->message->getSystemCount($user['id']),
                'new_count' => $this->message->getNewSystemCount($user['id']),
            ],
        ]);
    }

    public function newAction()
    {
        $user = $this->user()->get();

        if (! $user) {
            return $this->forbiddenAction();
        }

        return new JsonModel([
            'count' => $this->message->getNewCount($user['id']),
        ]);
    }
}
