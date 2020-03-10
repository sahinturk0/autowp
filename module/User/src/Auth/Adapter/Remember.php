<?php

namespace Autowp\User\Auth\Adapter;

use Autowp\User\Model\User;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Adapter\Exception\InvalidArgumentException;
use Laminas\Authentication\Result;

class Remember implements AdapterInterface
{
    /**
     * Credential values
     */
    private string $credential;

    private array $authenticateResultInfo;

    private User $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * @suppress PhanUndeclaredMethod, PhanPluginMixedKeyNoKey
     */
    public function authenticate()
    {
        $this->authenticateSetup();

        $select = $this->userModel->getTable()->getSql()->select();

        $userRow = $this->userModel->getTable()->selectWith(
            $select
                ->join('user_remember', 'users.id = user_remember.user_id', [])
                ->where([
                    'user_remember.token' => (string) $this->credential,
                    'not users.deleted',
                ])
        )->current();

        if (! $userRow) {
            $this->authenticateResultInfo['code']       = Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
        } else {
            $this->authenticateResultInfo['code']       = Result::SUCCESS;
            $this->authenticateResultInfo['identity']   = (int) $userRow['id'];
            $this->authenticateResultInfo['messages'][] = 'Authentication successful.';
        }

        return $this->authenticateCreateAuthResult();
    }

    /**
     * authenticateSetup() - This method abstracts the steps involved with
     * making sure that this adapter was indeed setup properly with all
     * required pieces of information.
     *
     * @throws InvalidArgumentException - in the event that setup was not done properly
     */
    private function authenticateSetup(): bool
    {
        $exception = null;

        if ($this->credential === null) {
            $exception = 'A credential value was not provided prior to authentication.';
        }

        if (null !== $exception) {
            throw new InvalidArgumentException($exception);
        }

        $this->authenticateResultInfo = [
            'code'     => Result::FAILURE,
            'identity' => null,
            'messages' => [],
        ];

        return true;
    }

    /**
     * authenticateCreateAuthResult() - Creates a Result object from
     * the information that has been collected during the authenticate() attempt.
     */
    private function authenticateCreateAuthResult(): Result
    {
        return new Result(
            $this->authenticateResultInfo['code'],
            $this->authenticateResultInfo['identity'],
            $this->authenticateResultInfo['messages']
        );
    }

    /**
     * setCredential() - set the credential value to be used
     */
    public function setCredential(string $credential): self
    {
        $this->credential = $credential;
        return $this;
    }
}
