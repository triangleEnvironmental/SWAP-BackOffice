<?php

namespace App\Exceptions;

use Exception;
use JetBrains\PhpStorm\Pure;
use Kreait\Firebase\Auth\UserRecord;

class FirebaseUserUnregisteredException extends Exception
{
    public UserRecord $firebase_user;

    #[Pure] public function __construct(UserRecord $firebase_user)
    {
        $this->firebase_user = $firebase_user;
        parent::__construct('User is registered but not yet have information in the system');
    }
}
