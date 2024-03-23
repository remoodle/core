<?php 

namespace App\Controllers;

use App\Modules\Moodle\Moodle;
use App\Repositories\UserMoodle\DatabaseUserMoodleRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CourseContentController extends BaseController
{

    public function __construct(
        private DatabaseUserMoodleRepositoryInterface $userMoodleRepository
    ){}

    //TODO: REFACTOR
    public function getCourse(Request $request, Response $response, array $args): Response
    {
        /**@var \App\Models\MoodleUser */
        $user = $request->getAttribute('user');
        $response
            ->getBody()
            ->write(json_encode([
                'course' => $this->userMoodleRepository->getActiveCourses(
                    $user->moodle_id, 
                    $user->moodle_token
                )->keyBy('course_id')[$args['id']],
                'content' => Moodle::createFromToken(
                    $user->moodle_token, 
                    $user->moodle_id
                )->getWrapper()
                ->getCoursesInfo((int)$args['id']),
            ]));

        return $response->withHeader("Content-Type", "application/json");
    }
}
