<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends AbstractController
{

    private function getOutput($cmd)
    {
        $process = Process::fromShellCommandline('sudo docker exec webcrate-core bash -c "' . $cmd . " | tr -d '\n'" . '"');
        $process->run();
        return $process->getOutput();
    }

    /**
     * @Route("/", name="admin")
     */
    public function index()
    {
        $php = self::getOutput("/usr/bin/php -v | awk 'NR<=1{ print $2 }'");
        $php73 = self::getOutput("/usr/bin/php73 -v | awk 'NR<=1{ print $2 }'");
        $php56 = self::getOutput("/usr/bin/php56 -v | awk 'NR<=1{ print $2 }'");
        $composer = self::getOutput("composer -V | awk 'NR<=1{ print $3 }'");
        $npm = self::getOutput("npm -v | awk 'NR<=1{ print $1 }'");
        $git = self::getOutput("git --version | awk 'NR<=1{ print $3 }'");
        $symfony = self::getOutput("symfony -V | awk 'NR<=1{ print $4 }' | sed -r 's/\x1B\[([0-9]{1,3}(;[0-9]{1,2})?)?[mGK]//g' | tr -d 'v'");
        $compass = self::getOutput("compass -v | awk 'NR<=1{ print $2 }'");
        $python = self::getOutput("python -V | awk 'NR<=1{ print $2 }'");
        $pip = self::getOutput("pip -V | awk 'NR<=1{ print $2 }'");
        $gem = self::getOutput("gem -v | awk 'NR<=1{ print $1 }'");
        $tmux = self::getOutput("tmux -V | awk 'NR<=1{ print $2 }'");
        $soft = [
            (object)[ 'name' => 'php', 'version' => $php ],
            (object)[ 'name' => 'php73', 'version' => $php73 ],
            (object)[ 'name' => 'php56', 'version' => $php56 ],
            (object)[ 'name' => 'composer', 'version' => $composer ],
            (object)[ 'name' => 'npm', 'version' => $npm ],
            (object)[ 'name' => 'git', 'version' => $git ],
            (object)[ 'name' => 'symfony cli', 'version' => $symfony ],
            (object)[ 'name' => 'compass', 'version' => $compass ],
            (object)[ 'name' => 'python', 'version' => $python ],
            (object)[ 'name' => 'pip', 'version' => $pip ],
            (object)[ 'name' => 'gem', 'version' => $gem ],
            (object)[ 'name' => 'tmux', 'version' => $tmux ]
        ];
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
                // foreach ($users as $username => $user) {
                //     $user = (object)$user;
                //     $user->name = $username;
                //     $list[] = $user;
                // }
            } catch (ParseException $exception) {
            }
       }
       return $list;
    }

}
