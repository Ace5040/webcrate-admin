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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\Type\DomainsType;
use App\Form\Type\DomainType;
use App\Form\Type\ProjectType;
//use Symfony\Component\Form\Extension\Core\Type\ButtonType;

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
        $soft = [];
        if(!empty($soft_json))
        {
            $soft = $encoder->decode($soft_json, 'json');
        }
        return $soft;
    }

    /**
     * @Route("/admin/projects", name="admin-projects")
     */
    public function projects()
    {
        $list = $this->repository->getListForTable();
        return $this->render('admin/projects.html.twig', [
            'controller_name' => 'AdminController',
            'projects' => $list
        ]);
    }

    /**
     * @Route("/admin/project/add", name="project-add")
     */
    public function newProject(Request $request)
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        if ($request->isMethod('POST'))
        {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid())
            {
                $project = $form->getData();
                $project->setUid($this->repository->getFirstAvailableUid());
                $this->manager->persist($project);
                $this->manager->flush();
                $this->updateUsersYaml();
                return $this->redirectToRoute('admin-projects');
            }
        }
        return $this->render(
            'admin/project.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/project/{uid}", name="admin-project")
     */
    public function project($uid, Request $request)
    {
        $project = $this->repository->loadByUid($uid);
        $form = $this->createForm(ProjectType::class, $project);
        if ($request->isMethod('POST'))
        {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid())
            {
                $project = $form->getData();
                $this->manager->persist($project);
                $this->manager->flush();
                $this->updateUsersYaml();
                return $this->redirectToRoute('admin-projects');
            }
        }
        return $this->render(
            'admin/project.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/project/{uid}/delete", name="admin-project-delete")
     */
    public function projectDelete($uid)
    {
        $project = $this->repository->loadByUid($uid);
        $this->manager->remove($project);
        $this->manager->flush();
        $this->updateUsersYaml();
        $list = $this->repository->getListForTable();
        $response = new JsonResponse();
        $response->setData([
            'result' => 'ok',
            'projects' => $list
        ]);
        return $response;
    }

    /**
     * @Route("/admin/import-projects", name="import-projects")
     */
    public function importProjects(Request $request): Response
    {
        $file = $request->files->get('file');
        $filename = $file->getClientOriginalName();
        $filepath = $file->getPathname();
        $projects = Yaml::parseFile($filepath);
        foreach ( $projects as $projectname => $project_obj ) {
            $project_obj = (object)$project_obj;
            $entity = $this->repository->loadByUid($project_obj->uid);
            if ( empty($entity) ) {
                $project = new Project();
                $project->setUid($project_obj->uid);
                $project->setName($projectname);
                $project->setBackup($project_obj->backup == 'yes' || $project_obj->backup === true );
                $project->setRedirect($project_obj->redirect == 'yes' || $project_obj->redirect === true );
                $project->setGzip($project_obj->gzip == 'yes' || $project_obj->gzip === true );
                $project->setMysql($project_obj->mysql_db == 'yes' || $project_obj->mysql_db === true );
                $project->setMysql5($project_obj->mysql5_db == 'yes' || $project_obj->mysql5_db === true );
                $project->setPostgre($project_obj->postgresql_db == 'yes' || $project_obj->postgresql_db === true );
                $project->setRootFolder($project_obj->root_folder);
                $project->setPasswordHash($project_obj->password);
                $project->setNginxConfig($project_obj->nginx_config == 'custom');
                $https = $this->https_repository->findByName($project_obj->https);
                $project->setHttps($https);
                $backend_version = empty($project_obj->backend_version) || $project_obj->backend_version == "7" ? 'latest' : $project_obj->backend_version;
                $backend = $this->backend_repository->findByNameAndVersion($project_obj->backend, (string)$backend_version);
                $project->setBackend($backend);
                if ( !empty($project_obj->gunicorn_app_module) && ( $project_obj->backend == 'gunicorn' ) ) {
                    $project->setGunicornAppModule($project_obj->gunicorn_app_module);
                }
                $project->setDomains($project_obj->domains);
                $options_array = [];
                foreach ( $project_obj->nginx_options as $name => $value ) {
                    $options_array[] = [ 'name' => $name, 'value' => $value ];
                }
                $project->setNginxOptions($options_array);
                $this->manager->persist($project);
            }
        }
        $this->manager->flush();
        $this->updateUsersYaml();
        $list = $this->repository->getListForTable();
        $response = new JsonResponse();
        $response->setData([
            'result' => 'ok',
            'projects' => $list
        ]);

        return $response;
    }

    public function updateUsersYaml()
    {
        $projects = $this->repository->getList();
        $projects_list = (object)[];
        foreach ( $projects as $project ) {
            $projectname = $project->getName();
            $projects_list->$projectname = $project->toObject();
        }

        $ymlData = Yaml::dump($projects_list, 3, 2, Yaml::DUMP_OBJECT_AS_MAP);

        try {
            $new_file_path = "/webcrate/updated-users.yml";
            file_put_contents($new_file_path, $ymlData);
            $process = Process::fromShellCommandline('sudo /webcrate/updateusers.py');
            $process->run();
            if (!$process->isSuccessful()) {
                throw new \Symfony\Component\Process\Exception\ProcessFailedException($process);
            }
        } catch (IOExceptionInterface $exception) {
            $debug['error'] = $exception->getMessage();
        }

        return $ymlData;
    }

}
