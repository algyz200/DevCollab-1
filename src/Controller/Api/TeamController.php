<?php

namespace App\Controller\Api;

use ApiPlatform\Core\Hal\Serializer\ObjectNormalizer;
use ApiPlatform\Core\Serializer\JsonEncoder;
use App\Controller\Form\TeamType;
use App\Entity\Projects;
use App\Entity\Teams;
use App\Entity\User;
use App\Entity\Users;
use App\Factory\TeamsFactory;
use App\Request\TeamsRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TeamsRepository;
use App\Repository\ProjectsRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Serializer\Serializer;

class TeamController extends AbstractFOSRestController
{
    /**
     * List all Teams
     * @Rest\Get("/teams", name="get_teams")
     *
     * @return Response
     */

    public function showTeams(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Teams::class);
        $teams = $repository->getTeams();

        return $this->handleView($this->view($teams));
    }

    /**
     * List all Users
     * @Rest\Get("/users", name="get_users")
     *
     * @return Response
     */
    public function showUser()
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->getUsers();

        return $this->handleView($this->view($users));
    }

    /**
     * Create a new Team
     * @Rest\Post("/team", name="post_team")
     * @param Request $request
     *
     * @return Response
     */
    public function createTeam(Request $request): Response
    {
        $teamsRequest = new TeamsRequest();
        $form = $this->createForm(TeamType::class, $teamsRequest);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);
        if ($form->isSubmitted() && $form->isValid()) {
            $teams = TeamsFactory::create($teamsRequest->getName(), $teamsRequest->getGithubRepo());
            $em = $this->getDoctrine()->getManager();
            $em->persist($teams);
            $em->flush();

            return $this->handleView($this->view([], Response::HTTP_CREATED));
        }

        return $this->handleView($this->view($form->getErrors(), Response::HTTP_BAD_REQUEST));
    }

    /**
     * @param $id
     * @Rest\Get("/teams/{id}", name="get_teamsbyid")
     * @return Response
     */
    public function getUsersByTeamId($id): Response
    {
        $teams = $this->getDoctrine()
            ->getRepository(Teams::class)
            ->getAllTeamInfo($id)
            ;
        dd($teams);
        //->find($id);


//        $array = array(
//            'id' => $teams->getId(),
//            'name' => $teams->getName(),
//            'githubRepo' => $teams->getGithubRepo()
//        );
//        dd($teams->getTeamPoints());
//        $users = $teams->getUsers();
//
//
//        $projectsArray = [];
//        foreach ($users as $user) {
//            $tempArray = [];
//            array_push($tempArray, $user->getId());
//            array_push($tempArray, $user->getName());
//            array_push($tempArray, $user->getLastname());
//            array_push($tempArray, $user->getEmail());
//            array_push($projectsArray, $tempArray);
//        }
//
//        $setKey = array('users' => $projectsArray);
//        $array = array_merge($array, $setKey);
//
//        $projects = $teams->getProjects();
//        $projectsArray = [];
//        foreach ($projects as $project) {
//            $tempArray = [];
//            array_push($tempArray, $project->getId());
//            array_push($tempArray, $project->getTitle());
//            array_push($projectsArray, $tempArray);
//        }
//
//        $setKey = array('projects' => $projectsArray);
//        $array = array_merge($array, $setKey);
//
//        $projects = $teams->getTeamPoints();
//        $projectsArray = [];
//        foreach ($projects as $project) {
//            array_push($projectsArray, $project->getPoints());
//        }
//
//        $setKey = array('teamPoints' => $projectsArray);
//        $array = array_merge($array, $setKey);
//
//        $projects = $teams->getTeamTasks();
//        $projectsArray = [];
//        foreach ($projects as $project) {
//            array_push($projectsArray, $project->getTask());
//        }
//
//        $setKey = array('teamTasks' => $projectsArray);
//        $array = array_merge($array, $setKey);
//
//        return $this->handleView($this->view($array));
    }

    /**
     * Join a Team
     * @Rest\Post("/jointeam", name="get_jointeam")
     *
     * @return Response
     */
    public function teamSorter(Request $request)
    {
        $userId = json_decode($request->getContent());
        $userId = $userId[0];
        $teamsArray = $this->showTeams()->getContent();
        $teamstoArray = json_decode($teamsArray, true);
        $randomTeam = array_rand($teamstoArray, 1);
        $firstTeamId = $teamstoArray[0]['id'];
        $randomTeamConverted = $firstTeamId + $randomTeam;
        $entityManager = $this->getDoctrine()->getManager();
        $team = $entityManager->getRepository(Teams::class)->find($randomTeamConverted);
        $user = $entityManager->getRepository(User::class)->find($userId);

        $team->addUser($user);
        $entityManager->flush();

        return $this->handleView($this->view($team->getId()));
    }
}
