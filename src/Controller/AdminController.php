<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class AdminController extends AbstractController
{

    /**
     * @Route("/", name="admin")
     */
    public function index()
    {
        $process = Process::fromShellCommandline('sudo /webcrate/versions.py');
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \Symfony\Component\Process\Exception\ProcessFailedException($process);
        }
        $soft_json = $process->getOutput();
        $encoder = new JsonEncoder();
        $soft = $encoder->decode($soft_json, 'json');
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'soft' => $soft
        ]);
    }

    /**
     * @Route("/admin/users", name="admin-users")
     */
    public function users()
    {
        $users = self::get_users_list();
        return $this->render('admin/users.html.twig', [
            'controller_name' => 'AdminController',
            'users' => $users
        ]);
    }

    private function get_users_list()
    {
        $list = [];
        if ( file_exists('/webcrate/users.yml') ) {
            try {

                $users = Yaml::parseFile('/webcrate/users.yml');
                foreach ($users as $username => $user) {
                    $user = (object)$user;
                    $user->name = $username;
                    $list[] = $user;
                }
            } catch (ParseException $exception) {
            }
       }
       return $list;
    }

}
