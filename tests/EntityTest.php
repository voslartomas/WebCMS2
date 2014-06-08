<?php

namespace WebCMS\Tests;

abstract class EntityTestCase extends BasicTestCase
{
    protected $exceptions = array(
        'BreadcrumbsItem.php'
    );

    public function setUp()
    {
        parent::setUp();

        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->createSchema($this->getClassesMetadata(__DIR__ . '/../Entity', 'WebCMS\\Entity'));
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->em->clear();

        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropDatabase();
    }

    private function getClassesMetadata($path, $namespace)
    {
        $metadata = array();

        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if (strstr($file, '.php') && $this->isEntity($file)) {
                    list($class) = explode('.', $file);
                    $metadata[] = $this->em->getClassMetadata($namespace . '\\' . $class);
                }
            }
        }

        return $metadata;
    }

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
