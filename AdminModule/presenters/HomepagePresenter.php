<?php

namespace AdminModule;

use Dubture\Monolog\Reader\LogReader;
use Nette\Utils\Finder;

/**
 * Admin presenter.
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class HomepagePresenter extends \AdminModule\BasePresenter
{
    /* @var logContent */
    private $logContent;

    /* @var exceptions */
    private $exceptions;

    protected function beforeRender()
    {
        parent::beforeRender();

        $this->reloadContent();

        $parameters = $this->getContext()->getParameters();

        $logFile = $parameters['tempDir'].'/../log/webcms.log';
        $reader = new LogReader($logFile, 2);

        $logs = array();
        foreach ($reader as $log) {
            if (!empty($log) && $log['level'] === 'INFO') {
                $logs[] = $log;
            }
        }

        $exceptions = array();
        if ($this->getUser()->getRoles()[0] === 'superadmin') {
            foreach (Finder::findFiles('exception-*.html')->in(APP_DIR . '/../log') as $key => $file) {
                $filename = $file->getFileName();
                $parsed = explode('-', $filename);
                $date = $parsed[1] . '-' . $parsed[2] . '-' . $parsed[3] . ' ' . $parsed[4] . ':' . $parsed[5] . ':' . $parsed[6];
                $exceptions[] = array('id' => substr($parsed[7], 0, -5), 'date' => $date, 'filename' => $filename);
            }
        }        

        if (count($exceptions) > 0) {
            $this->exceptions = $exceptions;
            $this->template->showExceptions = true;
        } else {
            $this->template->showExceptions = false;
        }

        // favourite links
        $user = $this->em->getRepository('WebCMS\Entity\User')->find($this->getUser()->getId());
        $favourites = $this->em->getRepository('WebCMS\Entity\Favourites')->findBy(array(
            'user' => $user,
        ));

        $this->template->logReader = array_reverse($logs);
        $this->template->links = $favourites;
    }

    protected function startup()
    {
        parent::startup();
    }

    public function createComponentExceptionLogsGrid()
    {
        $grid = new \Grido\Grid($this, 'exceptionLogsGrid');
        $grid->AddColumnDate('date', 'Date')
            ->setDateFormat(\Grido\Components\Columns\Date::FORMAT_DATETIME)
            ->setSortable();
        $grid->AddColumnText('filename', 'Filename');

        $grid->setModel($this->exceptions);

        $grid->addActionHref("showExceptionLog", 'Show')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary'), 'target' => '_blank'));
        $grid->addActionHref("deleteExceptionLog", 'Delete')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger'), 'data-confirm' => 'Are you sure you want to delete this item?'));

        $grid->setDefaultSort(array('date' => 'DESC'));
        $grid->setRememberState(true);
        $grid->setDefaultPerPage(10);
        $grid->setTranslator($this->translator);
        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        return $grid;
    }

    public function renderShowExceptionLog($id)
    {
        $this->template->content = $this->logContent;
    }

    public function actionShowExceptionLog($id)
    {
        foreach (Finder::findFiles('exception-*' . $id . '.html')->in(APP_DIR . '/../log') as $key => $file) {
            $contents = file_get_contents(APP_DIR . '/../log/' . $file->getFileName());
        }

        if (!empty($contents)) {
            $this->logContent = $contents;         
        } else {
            $this->logContent = 'Unable to show the exception log - file not found.';
        }      
    }

    public function actionDeleteExceptionLog($id)
    {
        foreach (Finder::findFiles('exception-*' . $id . '.html')->in(APP_DIR . '/../log') as $key => $file) {
            $filename = $file->getFileName();
        }

        if (!empty($filename)) {
            unlink(APP_DIR . '/../log/' . $filename);
            $this->flashMessage('Exception log has been deleted.', 'success');
        } else {
            $this->flashMessage('Unable to delete exception log - file not found.', 'error');
        }

        $this->forward('default');
    }

    public function actionDeleteAllExceptionLogs()
    {
        if ($this->getUser()->getRoles()[0] === 'superadmin') {
            foreach (Finder::findFiles('exception-*.html')->in(APP_DIR . '/../log') as $key => $file) {
                $filename = $file->getFileName();
                unlink(APP_DIR . '/../log/' . $filename);
            }
        }
        $this->flashMessage('All exception logs have been deleted.', 'success');
        $this->forward('default');
    }
}
