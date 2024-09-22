<?php

declare(strict_types=1);

namespace Queue\Actions;

use App\Models\MoodleUser;
use App\Modules\Moodle\Moodle;
use Illuminate\Database\Connection;

class ParseUserGrades
{
    public function __construct(
        private Connection $connection,
        private MoodleUser $moodleUser
    ) {
    }

    public function __invoke()
    {
        $moodle = Moodle::createFromToken($this->moodleUser->moodle_token, $this->moodleUser->moodle_id);

        $courseModulesUpsert = [];
        $courseGradesUpsert = [];

        foreach ($this->moodleUser->courseAssigns as $courseAssign) {
            [$courseModules, $courseGrades, $gradeEntities] = $this->getCourseModulesAndGrades($courseAssign->course_id, $moodle);
            $courseModulesUpsert = array_merge($courseModulesUpsert, $courseModules);
            $courseGradesUpsert = array_merge($courseGradesUpsert, $courseGrades);
        }

        $this->connection->beginTransaction();

        try {
            $this->connection
                ->table("grades")
                ->upsert(
                    $courseGradesUpsert,
                    ["moodle_id", "grade_id"],
                    ["percentage", "graderaw", "feedbackformat", "feedback"]
                );
            $this->connection->commit();
        } catch (\Throwable $th) {
            $this->connection->rollBack();
            throw $th;
        }
    }

    /**
     * @param int $courseId
     * @param \App\Modules\Moodle\Moodle $moodle
     * @return array{array, array, \App\Modules\Moodle\Entities\Grade[]}
     */
    private function getCourseModulesAndGrades(int $courseId, Moodle $moodle): array
    {
        try {
            $courseGrades = $moodle->getCourseGrades($courseId);
        } catch (\Throwable $th) {
            if (str_contains($th->getMessage(), 'error/notingroup')) {
                return [[],[],[]];
            }
            throw $th;
        }
        $courseGradesFiltered = [];

        $courseModulesUpsertArray = [];
        $courseGradesUpsertArray = [];
        foreach ($courseGrades as $courseGrade) {
            if ($courseGrade->itemtype === 'category' && $courseGrade->cmid === null && $courseGrade->name === '') {
                continue;
            }
            if ($courseGrade->itemtype === 'course' && $courseGrade->cmid === null && $courseGrade->name === '') {
                $courseGrade = $courseGrade->with(name: 'Total');
            }

            $courseGradesFiltered[] = $courseGrade;
            $courseGradesUpsertArray[] = (array) $courseGrade;
            if ($courseGrade->cmid === null) {
                continue;
            }

            $courseModulesUpsertArray[] = [
                "cmid" => $courseGrade->cmid,
                "course_id" => $courseId,
            ];
        }

        return [$courseModulesUpsertArray, $courseGradesUpsertArray, $courseGradesFiltered];
    }
}
