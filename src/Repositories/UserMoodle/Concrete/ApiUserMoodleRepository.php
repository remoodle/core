<?php

declare(strict_types=1);

namespace App\Repositories\UserMoodle\Concrete;

use App\Modules\Moodle\BaseMoodleUser;
use App\Modules\Moodle\Moodle;
use App\Repositories\UserMoodle\ApiUserMoodleRepositoryInterface;

class ApiUserMoodleRepository implements ApiUserMoodleRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getActiveCourses(int $moodleId, string $moodleToken): array
    {
        return Moodle::createFromToken($moodleToken, $moodleId)->getUserCourses();
    }

    /**
     * @inheritDoc
     */
    public function getCourseGrades(int $moodleId, string $moodleToken, int $courseId): array
    {
        return Moodle::createFromToken($moodleToken, $moodleId)->getCourseGrades($courseId);
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo(string $moodleToken): ?BaseMoodleUser
    {
        return Moodle::createFromToken($moodleToken)->getUser();
    }

    /**
     * @inheritDoc
     */
    public function getDeadlines(int $moodleId, string $moodleToken): array
    {
        return Moodle::createFromToken($moodleToken, $moodleId)->getDeadlines();
    }

    /**
     * @inheritDoc
     */
    public function getCourseAssigments(int $moodleId, string $moodleToken, int $courseId): array
    {
        return Moodle::createFromToken($moodleToken, $moodleId)->getCourseAssignments($courseId);
    }
}
