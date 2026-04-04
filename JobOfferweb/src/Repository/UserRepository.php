<?php

namespace App\Repository;

/**
 * Bridge repository for backward compatibility.
 * Delegates to the namespaced repository under App\Repository\User.
 */
class UserRepository extends \App\Repository\User\UserRepository
{
}
