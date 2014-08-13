<?php

namespace AdminModule;

use Nette\Utils\Finder;

/**
 * Filesystem presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class FilesystemPresenter extends \AdminModule\BasePresenter {

    const DESTINATION_BASE = 'upload/';

    private $path;

    /* @var \WebCMS\Helpers\ThumbnailCreator */
    private $thumbnailCreator;

    protected function beforeRender() {
	parent::beforeRender();
    }

    protected function startup() {
	parent::startup();

	$thumbnails = $this->em->getRepository('WebCMS\Entity\Thumbnail')->findAll();

	$this->thumbnailCreator = new \WebCMS\Helpers\ThumbnailCreator($this->settings, $thumbnails);
    }

    public function actionDefault($path) {
	if (!empty($path))
	    $this->path = $path . '/';
	else
	    $this->path = realpath(self::DESTINATION_BASE) . '/';
    }

    public function renderDefault($path, $dialog, $multiple) {
	$finder = new \Nette\Utils\Finder();
	$files = $finder->findFiles('*')->in($this->path);
	$directories = $finder->findDirectories('*')->in($this->path);

	if (empty($dialog))
	    $this->reloadContent();
	else
	    $this->reloadModalContent();

	$this->template->backLink = strpos($this->createBackLink($this->path), self::DESTINATION_BASE) === false ? realpath(self::DESTINATION_BASE) : $this->createBackLink($this->path);
	$this->template->files = $files;
	$this->template->directories = $directories;
	$this->template->multiple = $multiple;
    }

    private function createBackLink($path) {
	$exploded = explode('/', $path);

	array_pop($exploded);
	array_pop($exploded);

	return implode("/", $exploded);
    }

    public function handleMakeDirectory($name) {
	@mkdir($this->path . \Nette\Utils\Strings::webalize($name));
	@mkdir(str_replace("upload", "thumbnails", $this->path) . \Nette\Utils\Strings::webalize($name));

	$this->flashMessage('Directory has been created.', 'success');
    }

    public function handleUploadFile($path) {
	$file = new \Nette\Http\FileUpload($_FILES['files']);

	$file->move($this->path . '' . $file->getSanitizedName());

	if ($file->isImage()) {
	    $this->thumbnailCreator->createThumbnails($file->getSanitizedName(), $this->path);
	}

	$this->reloadContent();
	$this->flashMessage($this->translation['File has been uploaded']);

	$this->sendPayload();
    }

    public function handleDeleteFile($pathToRemove) {
	if (is_file($pathToRemove)) {

	    // delete all thumbnails if this file is image
	    try {

		if (getimagesize($pathToRemove)) {

		    $image = \Nette\Image::fromFile($pathToRemove);

		    $thumbs = $this->em->getRepository('WebCMS\Entity\Thumbnail')->findAll();
		    foreach ($thumbs as $t) {
			$file = pathinfo($pathToRemove);
			$filename = $file['filename'] . '.' . $file['extension'];

			$toRemove = str_replace('upload', 'thumbnails', $pathToRemove);
			$toRemove = str_replace($filename, $t->getKey() . $filename, $toRemove);

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

	if (!$this->isAjax())
	    $this->redirect('this');
    }

    public function actionDownloadFile($path) {

	$file = pathinfo($path);
	$filename = $file['filename'] . '.' . $file['extension'];

	$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension

	$mimeType = finfo_file($finfo, $path);

	$this->sendResponse(new \Nette\Application\Responses\FileResponse($path, $filename, $mimeType));
    }

    public function actionFilesDialog($path) {
	if (!empty($path))
	    $this->path = $path . '/';
	else
	    $this->path = realpath(self::DESTINATION_BASE) . '/';
    }

    public function renderFilesDialog() {

	$finder = new \Nette\Utils\Finder();

	$template = $this->createTemplate();
	$template->setFile('../libs/webcms2/webcms2/AdminModule/templates/Filesystem/filesDialog.latte');

	$template->files = $finder->findFiles('*')->in($this->path);
	$template->directories = $finder->findDirectories('*')->in($this->path);
	$template->setTranslator($this->translator);
	$template->registerHelperLoader('\WebCMS\Helpers\SystemHelper::loader');
	$template->backLink = strpos($this->createBackLink($this->path), self::DESTINATION_BASE) === false ? realpath(self::DESTINATION_BASE) : $this->createBackLink($this->path);

	$template->render();

	$this->terminate();
    }

    public function handleRegenerateThumbnails() {

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
    }

}
