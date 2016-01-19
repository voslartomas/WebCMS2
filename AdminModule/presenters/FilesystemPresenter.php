<?php

namespace AdminModule;

use Nette\Utils\Finder;

/**
 * Filesystem presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class FilesystemPresenter extends \AdminModule\BasePresenter
{
    const DESTINATION_BASE = './upload/';

    private $path;

    /* @var \WebCMS\Helpers\ThumbnailCreator */
    private $thumbnailCreator;

    protected function beforeRender()
    {
        parent::beforeRender();
    }

    protected function startup()
    {
        parent::startup();

        $thumbnails = $this->em->getRepository('WebCMS\Entity\Thumbnail')->findAll();

        $this->thumbnailCreator = new \WebCMS\Helpers\ThumbnailCreator($this->settings, $thumbnails);
    }

    public function actionDefault($path)
    {
        if (!empty($path)) {
            $this->path = self::DESTINATION_BASE.$path.'/';
        } else {
            $this->path = self::DESTINATION_BASE;
        }
    }

    public function renderDefault($path, $dialog, $multiple)
    {
        $finder = new \Nette\Utils\Finder();

        $files = $finder->findFiles('*')
                ->exclude('.htaccess')
                ->in(realpath($this->path));
        $directories = $finder->findDirectories('*')->in(realpath($this->path));

        if (empty($dialog)) {
            $this->reloadContent();
        } else {
            $this->reloadModalContent();
        }

        $this->path = str_replace(self::DESTINATION_BASE, '', $path).'/';

        $this->template->fsPath = $this->path;
        $this->template->backLink = $this->createBackLink($this->path);
        $this->template->files = $files;
        $this->template->directories = $directories;
        $this->template->multiple = $multiple;
        $this->template->maxUploadFileSize = $this->getMaxUploadFileSize();
    }

        /**
         * @param string $path
         */
        private function createBackLink($path)
        {
            $exploded = explode('/', $path);

            array_pop($exploded);
            array_pop($exploded);

            return implode("/", $exploded);
        }

    public function handleMakeDirectory($name)
    {
        @mkdir($this->path.\Nette\Utils\Strings::webalize($name));

        $this->flashMessage('Directory has been created.', 'success');
    }

    public function handleUploadFile($path)
    {
        $files = $this->getRequest()->getFiles();
        $files = $files['file'];

        foreach ($files as $file) {
            $this->uploadSingleFile($file);
        }

        $this->reloadContent();
        $this->flashMessage($this->translation['File has been uploaded']);
        $this->sendPayload();
    }

    private function uploadSingleFile($file)
    {
        $filePath = $this->path.''.$file->getSanitizedName();
        $file->move($filePath);

        $f = new \SplFileInfo($filePath);

        if ($file->isImage()) {
            $this->thumbnailCreator->createThumbnails($f->getBasename(), str_replace($f->getBasename(), '', $filePath));
        }
    }

    public function handleDeleteFile($pathToRemove)
    {
        $pathToRemove = self::DESTINATION_BASE.$pathToRemove;
        if (is_file($pathToRemove)) {
            // delete all thumbnails if this file is image
            try {
                if (getimagesize($pathToRemove)) {
                    $image = \Nette\Image::fromFile($pathToRemove);

                    $thumbs = $this->em->getRepository('WebCMS\Entity\Thumbnail')->findAll();
                    foreach ($thumbs as $t) {
                        $file = pathinfo($pathToRemove);
                        $filename = $file['filename'].'.'.$file['extension'];

                // $this->path contains symlinked path, that is not the right way @see handleRegenerateThumbnails() function for the fix
                $toRemove = str_replace('upload', 'thumbnails', $pathToRemove);
                        $toRemove = str_replace($filename, $t->getKey().$filename, $toRemove);

                        unlink($toRemove);
                    }
                }
            } catch (UnknownImageFileException $exc) {
                // image is not file, so there is nothing to do
            }

            unlink($pathToRemove);
        }

        if (is_dir($pathToRemove)) {
            \WebCMS\Helpers\SystemHelper::rrmdir($pathToRemove);
            \WebCMS\Helpers\SystemHelper::rrmdir(str_replace('upload', 'thumbnails', $pathToRemove));
        }

        $this->flashMessage('File has been removed.', 'success');

        $this->forward('this');
    }

    public function actionDownloadFile($path)
    {
        $file = pathinfo($path);
        $filename = $file['filename'].'.'.$file['extension'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension

        $path = self::DESTINATION_BASE.$path;
        $mimeType = finfo_file($finfo, $path);

        $this->sendResponse(new \Nette\Application\Responses\FileResponse($path, $filename, $mimeType));
    }

    public function actionFilesDialog($path)
    {
        if (!empty($path)) {
            $this->path = $path.'/';
        } else {
            $this->path = realpath(self::DESTINATION_BASE).'/';
        }
    }

    public function renderFilesDialog()
    {
        $finder = new \Nette\Utils\Finder();

        $template = $this->createTemplate();
        $template->setFile($this->template->basePathModule.'AdminModule/templates/Filesystem/filesDialog.latte');

        $template->files = $finder->findFiles('*')->in($this->path);
        $template->directories = $finder->findDirectories('*')->in($this->path);
        $template->setTranslator($this->translator);
        $template->registerHelperLoader('\WebCMS\Helpers\SystemHelper::loader');
        $template->backLink = strpos($this->createBackLink($this->path), self::DESTINATION_BASE) === false ? realpath(self::DESTINATION_BASE) : $this->createBackLink($this->path);

        $template->render();

        $this->terminate();
    }

    public function handleRegenerateThumbnails()
    {
        set_time_limit(0);

        \WebCMS\Helpers\SystemHelper::rrmdir('thumbnails', true);

        $timeStart = time();

        foreach (Finder::findFiles('*.jpg', '*.jpeg', '*.png', '*.gif')->from('upload') as $key => $file) {
            if (file_exists($key) && @getimagesize($key)) {
                $this->thumbnailCreator->createThumbnails($file->getBasename(), str_replace($file->getBasename(), '', $key));
            }
        }

        $timeOver = time();

        $seconds = $timeOver - $timeStart;

        $hours = floor($seconds / 3600);
        $mins = floor(($seconds - ($hours * 3600)) / 60);
        $secs = floor($seconds % 60);
        // TODO log spent time
        $this->flashMessage('Thumbnails has been regenerated by recent settings.', 'success');
        $this->forward('default');
    }

    /**
     * Get the maximal file upload size from the environment variables.
     *
     * @author Taken from the Drupal.org project
     * @license GPL 2
     * @return int
     */
    public function getMaxUploadFileSize()
    {
        static $max_size = -1;

        if ($max_size < 0) {
            // Start with post_max_size.
            $max_size = $this->parseFileSize(ini_get('post_max_size'));

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = $this->parseFileSize(ini_get('upload_max_filesize'));

            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    /**
     * Parse file size.
     *
     * @author Taken from the Drupal.org project
     * @license GPL 2
     * @return int
     */
    public function parseFileSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

}
