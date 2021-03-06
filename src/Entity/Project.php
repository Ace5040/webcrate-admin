<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="bigint", unique=true)
     */
    private $uid;

    /**
     * @ORM\Column(type="array")
     */
    private $domains = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $nginxConfig;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $rootFolder;

    /**
     * @ORM\Column(type="boolean")
     */
    private $mysql;

    /**
     * @ORM\Column(type="boolean")
     */
    private $mysql5;

    /**
     * @ORM\Column(type="boolean")
     */
    private $postgre;

    /**
     * @ORM\Column(type="boolean")
     */
    private $backup;

    /**
     * @ORM\ManyToOne(targetEntity=HttpsType::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $https;

    /**
     * @ORM\ManyToOne(targetEntity=Backend::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $backend;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $gunicornAppModule;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getDomains(): ?array
    {
        return $this->domains;
    }

    public function setDomains(array $domains): self
    {
        $this->domains = $domains;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        if ( !empty($password) ) {
            $salt = str_replace('+', '.', base64_encode(random_bytes(6)));
            $this->password = crypt($password, '$6$'.$salt.'$');
        }

        return $this;
    }

    public function setPasswordHash(string $password): self
    {
        if ( !empty($password) ) {
            $this->password = $password;
        }

        return $this;
    }

    public function getNginxConfig(): ?bool
    {
        return $this->nginxConfig;
    }

    public function setNginxConfig(bool $nginxConfig): self
    {
        $this->nginxConfig = $nginxConfig;

        return $this;
    }

    public function getRootFolder(): ?string
    {
        return $this->rootFolder;
    }

    public function setRootFolder(string $rootFolder): self
    {
        $this->rootFolder = $rootFolder;

        return $this;
    }

    public function getMysql(): ?bool
    {
        return $this->mysql;
    }

    public function setMysql(bool $mysql): self
    {
        $this->mysql = $mysql;

        return $this;
    }

    public function getMysql5(): ?bool
    {
        return $this->mysql5;
    }

    public function setMysql5(bool $mysql5): self
    {
        $this->mysql5 = $mysql5;

        return $this;
    }

    public function getPostgre(): ?bool
    {
        return $this->postgre;
    }

    public function setPostgre(bool $postgre): self
    {
        $this->postgre = $postgre;

        return $this;
    }

    public function getBackup(): ?bool
    {
        return $this->backup;
    }

    public function setBackup(bool $backup): self
    {
        $this->backup = $backup;

        return $this;
    }

    public function getHttps(): ?HttpsType
    {
        return $this->https;
    }

    public function setHttps(?HttpsType $https): self
    {
        $this->https = $https;

        return $this;
    }

    public function getBackend(): ?Backend
    {
        return $this->backend;
    }

    public function setBackend(?Backend $backend): self
    {
        $this->backend = $backend;

        return $this;
    }

    public function getGunicornAppModule(): ?string
    {
        return $this->gunicornAppModule;
    }

    public function setGunicornAppModule(?string $gunicornAppModule): self
    {
        $this->gunicornAppModule = $gunicornAppModule;

        return $this;
    }

    public function toObject(): object
    {
        return  (object)[
            'uid' => (int)$this->uid,
            'password' => $this->password,
            'domains' => $this->domains,
            'nginx_config' => $this->nginxConfig == 'custom' ? 'custom' : 'default',
            'root_folder' => $this->rootFolder,
            'https' => !empty($this->https) ? $this->https->getName(): 'disabled',
            'backend' => !empty($this->backend) ? $this->backend->getName(): 'php',
            'backend_version' => !empty($this->backend) ? $this->backend->getVersion(): 'latest',
            'gunicorn_app_module' => !empty($this->gunicornAppModule) ? $this->gunicornAppModule : '',
            'mysql_db' => (bool)$this->mysql,
            'mysql5_db' => (bool)$this->mysql5,
            'postgresql_db' => (bool)$this->postgre,
            'backup' => (bool)$this->backup
        ];
    }

}
