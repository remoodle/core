<?php

declare(strict_types=1);

namespace Queue\Actions;

use App\Models\MoodleUser;
use App\Modules\Moodle\Enums\CourseEnrolledClassification;
use Illuminate\Database\Connection;
use Queue\Actions\Batch\GetGradesBatch;

class ParseUserGrades
{
    public function __construct(
        private Connection $connection,
        private MoodleUser $moodleUser,
        private string $moodleWebservicesUrl
    ) {
    }

    public function __invoke()
    {
        $courseIds = $this->moodleUser
            ->courses()
            ->where('status', CourseEnrolledClassification::INPROGRESS)
            ->get()
            ->pluck('course_id')
            ->all()
        ;

        $courseGradesTotalUpsert = (new GetGradesBatch($this->moodleWebservicesUrl))->__invoke(
            $this->moodleUser->moodle_token,
            $this->moodleUser->moodle_id,
            ...$courseIds
        );

        $this->connection->beginTransaction();

        try {
            $this->connection
                ->table("grades")
                ->upsert(
                    $courseGradesTotalUpsert,
                    ["moodle_id", "grade_id"],
                    ["percentage", "graderaw", "feedbackformat", "feedback"]
                );
            $this->connection->commit();
        } catch (\Throwable $th) {
            $this->connection->rollBack();
            throw $th;
        }
    }
}
