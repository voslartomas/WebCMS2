<?php

namespace AdminModule;

/**
 * Description of UpdatePresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class UpdatePresenter extends \AdminModule\BasePresenter
{
    protected function beforeRender()
    {
        parent::beforeRender();
    }

    protected function startup()
    {
        parent::startup();
    }

    public function renderDefault()
    {
        $this->reloadContent();

        $packages = \WebCMS\Helpers\SystemHelper::getPackages();

        foreach ($packages as &$package) {
            if ($package['module']) {
            $module = $this->createObject($package['package']);

            $package['registered'] = $this->isRegistered($module->getName());
            }
        }

        $this->template->packages = $packages;
    }

    public function handleUpdateSystem()
    {
        $installLog = './log/install.log';
        $installErrorLog = './log/install-error.log';

        putenv("COMPOSER_HOME=/usr/bin/.composer");

        exec("cd ../;git pull;composer update -n > $installLog 2> $installErrorLog");

        $successMessage = $this->getMessageFromFile('.' . $installLog);

        if (strpos($successMessage, 'System has been updated.') !== FALSE) {
            $this->flashMessage('System has been udpated.', 'success');
        }

        $errorMessage = $this->getMessageFromFile('.' . $installErrorLog);
        if (!empty($errorMessage)) {
            $this->flashMessage('Error while updating system. Please contact administrator.', 'danger');
        }

        $this->handleCheckUpdates();

        if (!$this->isAjax())
            $this->redirect('Update:');
        else {
            $this->invalidateControl('footer');
        }
    }

    private function getMessageFromFile($file)
    {
        if (file_exists($file)) {
            $message = file_get_contents($file);
        } else {
            $message = 'error';
        }

        return $message;
    }

    public function actionClearCache()
    {
        $this->context->cacheStorage->clean(array(\Nette\Caching\Cache::ALL => TRUE));

        $this->flashMessage('Cache has been cleared.', 'success');
        $this->redirect("Update:functions");
    }

    public function handleBackupDatabase()
    {
        $par = $this->context->getParameters();

        if (!file_exists('./upload/backups')) {
            mkdir('./upload/backups');
        }

        $user = $par['database']['user'];
        $password = $par['database']['password'];
        $database = $par['database']['dbname'];

        exec("mysqldump -u $user -p$password $database > ./upload/backups/db-backup-" . time() . ".sql");

        $this->flashMessage('Backup has been create. You can download this backup in filesystem - backup directory.', 'success');
    }

    // REFACTOR
    public function actionRegister($name)
    {
        $module = $this->createObject($name);

        if (!$this->isRegistered($name)) {

            $exists = $this->em->getRepository('WebCMS\Entity\Module')->findOneBy(array(
            'name' => $module->getName()
            ));

            if (is_object($exists)) {
            $exists->setActive(TRUE);
            } else {
            $mod = new \WebCMS\Entity\Module;
            $mod->setName($module->getName());
            $mod->setPresenters($module->getPresenters());
            $mod->setActive(TRUE);

            $this->em->persist($mod);
            }

            $this->em->flush();
            $this->copyTemplates($name);
            $this->flashMessage('Module has been registered.', 'success');
        } else {

            $this->flashMessage('Module is already registered.', 'danger');
        }

        if (!$this->isAjax())
            $this->redirect('default');
    }

    private function copyTemplates($name)
    {
        if (!file_exists('../app/templates/' . $name))
            mkdir('../app/templates/' . $name);
        exec('cp -r ../libs/webcms2/' . $name . '/Frontend/templatesDefault/* ../app/templates/' . $name);
        }

        public function actionUnregister($name)
        {
        $module = $this->createObject($name);
        $module = $this->em->getRepository('WebCMS\Entity\Module')->findOneBy(array(
            'name' => $module->getName()
        ));

        $module->setActive(FALSE);
        $this->em->flush();

        $this->flashMessage('Module has been unregistered from system.', 'success');
        if (!$this->isAjax())
            $this->redirect('default');
    }

    private function isRegistered($name)
    {
        $exists = $this->em->getRepository('WebCMS\Entity\Module')->findOneBy(array(
            'name' => $name
        ));

        if (is_object($exists) && $exists->getActive()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function handleCheckUpdates()
    {
        $client = new \Packagist\Api\Client();

        $packages = \WebCMS\Helpers\SystemHelper::getPackages();

        $needUpdateCount = 0;
        foreach ($packages as &$package) {
            if ($package['vendor'] === 'webcms2') {

            $apiResult = $client->get($package['vendor'] . '/' . $package['package']);
            $versions = $apiResult->getVersions();

            $devVersion = $versions[$package['version']];
            if (count($versions) > 1) {

                $newestVersion = next($versions);
                $newestVersion = $newestVersion->getVersion();
                while (strpos($newestVersion, 'dev') !== false) {
                $newestVersion = next($versions);
                $newestVersion = $newestVersion->getVersion();
                }
            } else {
                $newestVersion = null;
            }

            // development or production version?
            if (strpos($package['version'], 'dev') !== false) {
                if ($package['versionHash'] !== mb_substr($devVersion->getSource()->getReference(), 0, 7)) {
                $needUpdateCount++;
                }
            } else {
                if ($package['version'] !== $newestVersion) {
                $needUpdateCount++;
                }
            }
            }
        }

        $nuc = $this->settings->get('needUpdateCount', 'system', 'text');

        $setting = $this->em->find('WebCMS\Entity\Setting', $nuc->getId());
        $setting->setValue($needUpdateCount);

        if ($needUpdateCount > 0) {
            $this->flashMessage('Available new updates.', 'success');
        } else {
            $this->flashMessage('Your system is up to date.', 'success');
        }

        $this->em->flush();

        $this->invalidateControl('header');
    }

    public function handleDeleteModule($name)
    {
        $config = json_decode(file_get_contents('../composer.json'));

        if (!empty($config->require->$name)) {
            unset($config->require->$name);
        }

        file_put_contents('../composer.json', json_encode($config, JSON_PRETTY_PRINT));

        $installLog = './log/install.log';
        $installErrorLog = './log/install-error.log';

        putenv("COMPOSER_HOME=/usr/bin/.composer");
        exec("cd ../;composer update $name -n > $installLog 2> $installErrorLog");
        exec("cd ../;composer dumpautoload");
        exec("./libs/webcms2/webcms2/install/install.sh 3");

        $this->redirect('default');
    }

    public function actionAddModule()
    {
    }

    public function renderAddModule()
    {
        $this->reloadModalContent();
    }

    public function createComponentAddModuleForm()
    {
        $form = $this->createForm();

        $form->addSelect('module', 'Module', $this->getModulesToInstall())->setAttribute('class', 'form-control');
        $form->addText('version', 'Module version')->setDefaultValue('0.*')->setAttribute('class', 'form-control');

        $form->addSubmit('install', 'Install module');
        $form->onSuccess[] = callback($this, 'addModuleFormSubmitted');

        return $form;
    }

    public function addModuleFormSubmitted($form)
    {
        $values = $form->getValues();

        putenv("COMPOSER_HOME=/usr/bin/.composer");

        $installLog = './log/install.log';
        $installErrorLog = './log/install-error.log';

        $module = $values->module;
        $version = $values->version;
        exec("cd ../;composer require $module $version > $installLog 2> $installErrorLog");
        exec("../libs/webcms2/webcms2/install/install.sh 3; >");

        $this->redirect('default');
    }

    private function getModulesToInstall()
    {
        $packages = \WebCMS\Helpers\SystemHelper::getPackages();

        $client = new \Packagist\Api\Client();
        $apiResult = $client->search('webcms2/*');

        $notInstalled = array();
        foreach ($apiResult as $package) {
            if (!array_key_exists($package->getName(), $packages)) {
            $notInstalled[$package->getName()] = $package->getName();
            }
        }

        return $notInstalled;
    }

    public function renderFunctions()
    {
        $this->reloadContent();
    }

    public function renderCreateModule()
    {
        $this->reloadModalContent();
    }

    public function createComponentCreateModuleForm()
    {
        $form = $this->createForm();

        $form->addText('name', 'Name')->setAttribute('class', 'form-control');
        $form->addText('author', 'Author')->setAttribute('class', 'form-control');
        $form->addText('email', 'Author\'s email')->setAttribute('class', 'form-control');
        $form->addTextArea('description', 'Description')->setAttribute('class', 'form-control');

        $form->addSubmit('install', 'Create new module')->setAttribute('class', 'btn btn-primary');
        $form->onSuccess[] = callback($this, 'createModuleFormSubmitted');

        return $form;
    }

    public function createModuleFormSubmitted($form)
    {
        $values = $form->getValues();

        $name = strtolower(trim($values->name));
        $nameBig = ucfirst($name);
        $author = trim($values->author);
        $email = trim($values->email);
        $description = trim($values->description);

        exec("cd ../libs/webcms2/webcms2/install;./module.sh create $name $nameBig '$author' '$email' '$description' > ../../../../log/install.log 2> ../../../../log/install-error.log");

        $this->flashMessage('Module has been created.', 'success');
        $this->redirect('default');
    }

    // render log tab
    public function renderLog()
    {
        $this->reloadContent();

        $this->template->installLog = $this->getLog('../log/install.log');
        $this->template->installErrorLog = $this->getLog('../log/install-error.log');
        $this->template->updateLog = $this->getLog('../log/auto-update.log');
        $this->template->errorLog = $this->getLog('../log/error.log');
    }

    private function getLog($path)
    {
        if (file_exists($path)) {
            return nl2br(file_get_contents($path));
        }

        return '';
    }
}
