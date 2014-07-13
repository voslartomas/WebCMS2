<?php

class FilesystemPresenterTest extends \WebCMS\Tests\PresenterTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->createPresenter('Admin:Filesystem');
    }

    public function testDefault()
    {
        $response = $this->makeRequest();

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testDefaultWithPath()
    {
        mkdir('upload/test-directory');

        $response = $this->makeRequest('default', 'GET', array('path' => 'test-directory'));

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testDefaultDialog()
    {
        $response = $this->makeRequest('default', 'GET', array('dialog' => 1));

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);

        $this->getResponse($response);
    }

    public function testCreateDirectoryDialog()
    {
        $response = $this->makeRequest('default', 'GET', array(
            'action' => 'default',
                'do' => 'makeDirectory',
        ));

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
    }

    public function testUploadFile()
    {
        mkdir('upload/test-directory');
        system('convert -size 32x32 xc:white /tmp/empty.jpg');

        $this->createSystemThumbnail();

        $file = new \Nette\Http\FileUpload(array(
            'name' => 'empty.jpg',
            'tmp_name' => '/tmp/empty.jpg',
            'type' => 'text/plain',
            'size' => 0,
            'error' => 0
        ));
        
        $response = $this->makeRequest('default', 'POST', array('path' => 'test-directory'), array(), 'uploadFile',
            array(
                    'files' => $file
                )
            );

        $this->assertInstanceOf('Nette\Application\Responses\JsonResponse', $response);

        $this->assertFileExists('upload/test-directory/empty.jpg');
        $this->assertFileExists('thumbnails/test-directory/systemempty.jpg');
    }

    private function createSystemThumbnail()
    {
        $thumbnail = new \WebCMS\Entity\Thumbnail;
        $thumbnail->setKey('system');
        $thumbnail->setX('20');
        $thumbnail->setY('20');
        $thumbnail->setWatermark(false);
        $thumbnail->setSystem(true);
        $thumbnail->setResize(0);
        $thumbnail->setCrop(true);

        $this->em->persist($thumbnail);
        $this->em->flush();
    }

    public function testMakeDirectory()
    {
        $response = $this->makeRequest('default', 'GET', array(
            'action' => 'default',
            'name' => 'test directory'
        ), array(), 'makeDirectory');

        $this->assertInstanceOf('Nette\Application\Responses\TextResponse', $response);
        $this->assertEquals(TRUE, file_exists('upload/test-directory/'));
    }

    public function testRemove()
    {
        mkdir('upload/test-directory');
        mkdir('thumbnails/test-directory');
        system('convert -size 32x32 xc:white upload/empty.jpg');
        system('convert -size 32x32 xc:white thumbnails/systemempty.jpg');

        $this->createSystemThumbnail();

        $this->assertEquals(true, file_exists('upload/test-directory/'));
        $this->assertEquals(true, file_exists('thumbnails/test-directory/'));
        $this->assertEquals(true, file_exists('thumbnails/test-directory/'));

        $this->assertEquals(true, file_exists('upload/empty.jpg'));
        $this->assertEquals(true, file_exists('thumbnails/systemempty.jpg'));

        $response = $this->makeRequest('default', 'GET', array(
            'action' => 'default',
            'pathToRemove' => '/empty.jpg'
        ), array(), 'deleteFile');

        $this->assertInstanceOf('Nette\Application\Responses\ForwardResponse', $response);
        $this->assertEquals(false, file_exists('upload/empty.jpg'));
        $this->assertEquals(false, file_exists('thumbnails/empty.jpg'));
    }

    public function testDownloadFile()
    {
        file_put_contents('upload/test.txt', 'Test text.');

        $response = $this->makeRequest('downloadFile', 'GET', array(
            'action' => 'downloadFile',
            'path' => 'test.txt'
        ));

        $this->assertInstanceOf('Nette\Application\Responses\FileResponse', $response);
    }

    public function testFilesDialog()
    {
        ob_start();
        $this->makeRequest('filesDialog');
        $response = ob_get_contents();
        ob_end_clean();

        $this->assertNotEmpty($response);
    }

    public function testRegenerateThumbnails()
    {
        $this->createSystemThumbnail();

        system('convert -size 32x32 xc:white upload/empty.jpg');

        $response = $this->makeRequest('filesDialog', 'GET', array(), array(), 'regenerateThumbnails');

        $this->assertInstanceOf('Nette\Application\Responses\ForwardResponse', $response);

        $this->assertEquals(true, file_exists('upload/empty.jpg'));
        $this->assertEquals(true, file_exists('thumbnails/systemempty.jpg'));
    }
}
