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
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

class AdminController extends AbstractController
{

    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @Route("/", name="admin")
     */
    public function index()
    {
        $soft = $this->cache->get('versions', function (ItemInterface $item) {
            $item->expiresAfter(604800);
            return $this->getVersions();
        });
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'soft' => $soft
        ]);
    }

    private function getVersions()
    {
        $process = Process::fromShellCommandline('sudo /webcrate/versions.py');
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \Symfony\Component\Process\Exception\ProcessFailedException($process);
        }
        $soft_json = $process->getOutput();
        $encoder = new JsonEncoder();
        $soft = $encoder->decode($soft_json, 'json');
        return $soft;
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
