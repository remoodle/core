<?php

declare(strict_types=1);

namespace Queue\Actions;

use App\Models\MoodleUser;
use App\Modules\Moodle\Entities\Event;
use App\Modules\Moodle\Moodle;
use App\Modules\Search\SearchEngineInterface;
use Illuminate\Database\Connection;

class ParseUserEvents
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SearchEngineInterface $searchEngine,
        private readonly MoodleUser $user
    ) {
    }

    public function __invoke()
    {
        $moodle = Moodle::createFromToken($this->user->moodle_token, $this->user->moodle_id);
        $userApiEvents = $moodle->getDeadlines();

        $this->connection->beginTransaction();

        try {
            $this->connection->table("events")->upsert(array_map(function (Event $event) {
                $event = (array)$event;
                unset($event['assignment']);
                return $event;
            }, $userApiEvents), ["instance"], ['timestart', 'visible', 'name']);
            $this->connection->commit();
        } catch (\Throwable $th) {
            $this->connection->rollBack();
            throw $th;
        }
    }
}
