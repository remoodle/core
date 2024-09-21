<?php

declare(strict_types=1);

namespace Queue\Handlers;

use Illuminate\Database\Connection;
use Queue\Actions\ParseUserGrades as ActionsParseUserGrades;

class ParseUserGrades extends BaseHandler
{
    private ActionsParseUserGrades $action;

    protected function setup(): void
    {
        $connection = $this->get(Connection::class);
        $user = $this->getPayload()->payload();
        $this->action = new ActionsParseUserGrades($connection, $user);
    }

    protected function dispatch(): void
    {
        ($this->action)();
    }
}
