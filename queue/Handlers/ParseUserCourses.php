<?php

declare(strict_types=1);

namespace Queue\Handlers;

use App\Models\UserCourseAssign;
use App\Modules\Jobs\FactoryInterface;
use App\Modules\Moodle\Enums\CourseEnrolledClassification;
use App\Modules\Moodle\Moodle;
use Illuminate\Database\Connection;
use App\Modules\Moodle\Entities\Course;

class ParseUserCourses extends BaseHandler
{
    private Connection $connection;
    private FactoryInterface $queueFactory;

    protected function setup(): void
    {
        $this->connection = $this->get(Connection::class);
        $this->queueFactory = $this->get(FactoryInterface::class);
    }

    protected function dispatch(): void
    {
        /**@var \App\Models\MoodleUser */
        $user = $this->getPayload()->payload();
        $moodle = Moodle::createFromToken($user->moodle_token, $user->moodle_id);

        [$courses, $coursesAssign] = $this->getUserCoursesAndAssigns($user->moodle_id, $moodle);

        $this->connection->beginTransaction();

        try {
            UserCourseAssign::where("moodle_id", $user->moodle_id)->update([
                "classification" => CourseEnrolledClassification::PAST->value
            ]);
            $this->connection
                ->table("courses")
                ->upsert($courses, "course_id");

            $this->connection
                ->table("user_course_assign")
                ->upsert($coursesAssign, [ "course_id", "moodle_id"], ["classification"]);

            $this->connection->commit();
        } catch (\Throwable $th) {
            $this->connection->rollBack();
            throw $th;
        }
    }

    /**
     * @param int $moodleId
     * @param \App\Modules\Moodle\Moodle $moodle
     * @return array<int, array>
     */
    private function getUserCoursesAndAssigns(int $moodleId, Moodle $moodle): array
    {
        $courses = $moodle->getUserCourses();
        $courseAssign = array_map(function (Course $course) use ($moodleId) {
            return [
                "course_id" => $course->course_id,
                "moodle_id" => $moodleId,
                "classification" => CourseEnrolledClassification::INPROGRESS->value
            ];
        }, $courses);

        return [array_map(fn (Course $course) => (array)$course, $courses), $courseAssign];
    }
}
