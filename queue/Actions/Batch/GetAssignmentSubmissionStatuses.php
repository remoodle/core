<?php

declare(strict_types=1);

namespace Queue\Actions\Batch;

use GuzzleHttp\Client;

final class GetAssignmentSubmissionStatuses
{
    public function __invoke(string $token, )
    {
        $client = new Client(['verify' => false]);

    }
}
