<?php

namespace WebCMS\Tests;

abstract class EntityTestCase extends BasicTestCase
{
    protected $exceptions = array(
        'BreadcrumbsItem.php',
    );

    protected $tool;

    public function __construct()
    {
        parent::__construct();

        $this->tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
    }

    public function setUp()
    {
        parent::setUp();

        $this->tool->createSchema($this->getClassesMetadata(__DIR__.'/../Entity', 'WebCMS\\Entity'));
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->em->clear();

        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropDatabase();
    }

    /**
     * @param string $path
     * @param string $namespace
     */
    protected function getClassesMetadata($path, $namespace)
    {
        $metadata = array();

        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if (strstr($file, '.php') && $this->isEntity($file)) {
                    list($class) = explode('.', $file);
                    $metadata[] = $this->em->getClassMetadata($namespace.'\\'.$class);
                }
            }
        }

        return $metadata;
    }

    /**
     * @param string $path
     */
    private function isEntity($path)
    {
        foreach ($this->exceptions as $exception) {
            if (strpos($exception, $path) !== false) {
                return false;
            }
        }

        return true;
    }
}
