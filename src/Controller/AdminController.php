<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use App\Repository\ProjectRepository;
use App\Repository\HttpsTypeRepository;
use App\Repository\BackendRepository;
use App\Entity\Project;
use App\Entity\HttpsType;
use App\Entity\Backend;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class AdminController extends AbstractController
{

    private $cache;
    private $repository;
    private $https_repository;
    private $backend_repository;

    public function __construct(CacheInterface $cache, ProjectRepository $repository, HttpsTypeRepository $https_repository, BackendRepository $backend_repository, EntityManagerInterface $manager)
    {
        $this->cache = $cache;
        $this->repository = $repository;
        $this->https_repository = $https_repository;
        $this->backend_repository = $backend_repository;
        $this->manager = $manager;
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

    /**
     * @Route("/admin/users/{uid}", name="admin-user")
     */
    public function user($uid)
    {
        $user = self::get_user($uid);
        return $this->render('admin/user.html.twig', [
            'controller_name' => 'AdminController',
            'user' => $user
        ]);
    }

    private function get_users_list()
    {
        $list = [];
        if ( file_exists('/webcrate/users.yml') ) {
            try {
                $users = $this->repository->getList();
                foreach ($users as $entity) {
                    $user = (object)[];
                    $user->name = $entity->getName();
                    $user->uid = $entity->getUid();
                    $user->backend = $entity->getBackend()->getName();
                    $user->backend_version = $entity->getBackend()->getVersion();
                    $user->backup = $entity->getBackup() ? 'yes' : 'no';
                    $user->https = $entity->getHttps()->getName();
                    $list[] = $user;
                }
            } catch (ParseException $exception) {
            }
       }
       return $list;
    }

    private function get_user($uid)
    {
       $entity = $this->repository->loadByUid($uid);
       $user = (object)[];
       $user->name = $entity->getName();
       $user->uid = $entity->getUid();
       $user->backend = $entity->getBackend()->getName();
       $user->backend_version = $entity->getBackend()->getVersion();
       $user->backup = $entity->getBackup() ? 'yes' : 'no';
       $user->https = $entity->getHttps()->getName();
       return $user;
    }

    /**
     * @Route("/admin/import-projects", name="import-projects")
     */
    public function importProjects(Request $request): Response
    {
        $file = $request->files->get('file');
        $filename = $file->getClientOriginalName();
        $filepath = $file->getPathname();
        $users = Yaml::parseFile($filepath);
        foreach ( $users as $username => $user ) {
            $user = (object)$user;
            $entity = $this->repository->loadByUid($user->uid);
            if ( empty($entity) ) {
                $project = new Project();
                $project->setUid($user->uid);
                $project->setName($username);
                $project->setBackup($user->backup == 'yes' || $user->backup === true );
                $project->setMysql($user->mysql_db == 'yes' || $user->mysql_db === true );
                $project->setMysql5($user->mysql5_db == 'yes' || $user->mysql5_db === true );
                $project->setPostgre($user->postgresql_db == 'yes' || $user->postgresql_db === true );
                $project->setRootFolder($user->root_folder);
                $project->setPassword($user->password);
                $project->setNginxConfig($user->nginx_config == 'custom');
                $https = $this->https_repository->findByName($user->https);
                $project->setHttps($https);
                $backend_version = empty($user->backend_version) || $user->backend_version == "7" ? 'latest' : $user->backend_version;
                $backend = $this->backend_repository->findByNameAndVersion($user->backend, (string)$backend_version);
                $project->setBackend($backend);
                if ( !empty($user->gunicorn_app_module) && ( $user->backend == 'php' ) ) {
                    $project->getGunicornAppModule($user->gunicorn_app_module);
                }
                $project->setDomains($user->domains);
                $this->manager->persist($project);
            }
        }
        $this->manager->flush();

        $debug = $this->updateUsersYaml();

        $response = new JsonResponse();
        $response->setData([
            'name' => $filename,
            'data' => $users,
            'debug' => $debug
        ]);

        return $response;
    }

    public function updateUsersYaml()
    {
        $projects = $this->repository->getList();
        $users = (object)[];
        foreach ( $projects as $project ) {
            $projectname = $project->getName();
            $users->$projectname = $project->toObject();
        }
        $ymlData = Yaml::dump($users, 3, 2, Yaml::DUMP_OBJECT_AS_MAP);
        $WEBCRATE_UID = (int)$_ENV['DATABASE_URL'];
        $WEBCRATE_GID = (int)$_ENV['WEBCRATE_GID'];

        $debug = [
            'WEBCRATE_UID' => $WEBCRATE_UID,
            'WEBCRATE_GID' => $WEBCRATE_GID
        ];

        try {
            $new_file_path = "/webcrate/users.yml";
            file_put_contents($new_file_path, $ymlData);
        } catch (IOExceptionInterface $exception) {
            $debug['error'] = $exception->getMessage();
        }
        return $debug;
    }

}
