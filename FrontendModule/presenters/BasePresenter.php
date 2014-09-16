<?php

namespace FrontendModule;

use Nette;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\UI;

/**
 * Base class for all application presenters.
 *
 * @author     Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package    WebCMS2
 */
class BasePresenter extends \WebCMS2\Common\BasePresenter
{
    /** @var Doctrine\ORM\EntityManager */
    protected $em;

    /* @var \WebCMS\Translation\Translation */
    public $translation;

    /* @var \WebCMS\Translation\Translator */
    public $translator;

    /* @var Nette\Http\SessionSection */
    public $language;

    /* @var User */
    public $systemUser;

    /* @var \WebCMS\Settings */
    public $settings;

    /* @var Page */
    public $actualPage;

    /* @var string */
    public $abbr;

    /* @var Array */
    public $languages;

    /* @var Array */
    private $breadcrumbs = array();

    /* Method is executed before render. */

    protected function beforeRender()
    {
        if (is_object($this->actualPage)) {
            if ($this->actualPage->getDefault())
                $this->setLayout("layoutDefault");
            else
                $this->setLayout("layout");
        }

        if ($this->isAjax()) {
            $this->invalidateControl('flashMessages');
        }

        $this->template->registerHelperLoader('\WebCMS\Helpers\SystemHelper::loader');

        // get top page for sidebar menu
        if (is_object($this->actualPage)) {
            $top = $this->actualPage;
            while ($top->getParent() != NULL && $top->getLevel() > 1) {
                $top = $top->getParent();
            }
        }

        // set up boxes
        $this->setUpBoxes();

        // set default seo settings
        if (is_object($this->actualPage)) {
            $this->template->breadcrumb = $this->getBreadcrumbs();
            $this->template->sidebar = $this->getStructure($this, $top, $this->em->getRepository('WebCMS\Entity\Page'), FALSE, $this->settings->get('Sidebar class', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue(), FALSE, FALSE, NULL, $this->settings->get('Sidebar class', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue());
        }

        $this->template->abbr = $this->abbr;
        $this->template->settings = $this->settings;
        // !params load from settings
        $this->template->structures = $this->getStructures(!$this->settings->get('Navbar dropdown', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue(), $this->settings->get('Navbar class', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue(), $this->settings->get('Navbar dropdown', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue(), $this->settings->get('Navbar id', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue());
        $this->template->setTranslator($this->translator);
        $this->template->actualPage = $this->actualPage;
        $this->template->user = $this->getUser();
        $this->template->activePresenter = $this->getPresenter()->getName();
        $this->template->language = $this->language;
        $this->template->languages = $this->em->getRepository('WebCMS\Entity\Language')->findAll();
    }

    private function setDefaultSeo()
    {
        $temp = $this->actualPage->getMetaKeywords();
        if (!empty($temp)) {
            $this->template->seoKeywords = $this->actualPage->getMetaKeywords();
        } else {
            $this->template->seoKeywords = $this->settings->get('Seo keywords', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue();
        }

        $temp = $this->actualPage->getMetaDescription();
        if (!empty($temp)) {
            $this->template->seoDescription = $this->actualPage->getMetaDescription();
        } else {
            $this->template->seoDescription = $this->settings->get('Seo description', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue();
        }

        $temp = $this->actualPage->getMetaTitle();
        if (!empty($temp)) {
            $this->template->seoTitle = $this->actualPage->getMetaTitle();
        } else {
            $this->template->seoTitle = $this->actualPage->getTitle();
        }

        if ($this->settings->get('Seo title before', \WebCMS\Settings::SECTION_BASIC, 'checkbox')->getValue()) {
            $this->template->seoTitle = $this->settings->get('Seo title', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue() . $this->template->seoTitle;
        } else {
            $this->template->seoTitle = $this->template->seoTitle . $this->settings->get('Seo title', \WebCMS\Settings::SECTION_BASIC, 'text')->getValue();
        }
    }

    /* Startup method. */

    protected function startup()
    {
        parent::startup();

        // change language
        if (is_numeric($this->getParam('l'))) {
            $this->changeLanguage($this->getParam('l'));
        }

        // set language
        if (is_numeric($this->getParam('language')))
            $this->language = $this->em->find('WebCMS\Entity\Language', $this->getParam('language'));
        else
            $this->language = $this->em->getRepository('WebCMS\Entity\Language')->findOneBy(array(
                'defaultFrontend' => TRUE
            ));

        $this->abbr = $this->language->getDefaultFrontend() ? '' : $this->language->getAbbr() . '/';

        // load languages
        $this->languages = $this->em->getRepository('WebCMS\Entity\Language')->findAll();

        setlocale(LC_ALL, $this->language->getLocale());
        \WebCMS\Helpers\PriceFormatter::setLocale($this->language->getLocale());

        // translations
        $translation = new \WebCMS\Translation\Translation($this->em, $this->language, 0, $this->getContext()->getService('cacheStorage'));
        $this->translation = $translation->getTranslations();
        $this->translator = new \WebCMS\Translation\Translator($this->translation);

        // system settings
        $this->settings = new \WebCMS\Settings($this->em, $this->language);
        $this->settings->setSettings($this->getSettings());

        // system helper sets variables
        \WebCMS\Helpers\SystemHelper::setVariables(array(
            'baseUrl' => $this->presenter->getHttpRequest()->url->baseUrl,
            'infoEmail' => $this->settings->get('Info email', 'basic')->getValue()
        ));

        $id = $this->getParam('id');
        if ($id) {
            $this->actualPage = $this->em->find('WebCMS\Entity\Page', $id);

            if ($this->actualPage->getRedirect() != NULL) {
                $this->redirectUrl($this->presenter->getHttpRequest()->url->baseUrl . $this->actualPage->getRedirect());
            }
        }

        if (!file_exists('sitemap.xml')) {
            $this->generateSitemap();
        }

        if (is_object($this->actualPage)) {
            $this->setDefaultSeo();
        }

        // generate sitemap if doesn't exist yet
        if (!file_exists('sitemap.xml')) {
            $this->generateSitemap();
        }

        if ($this->isAjax()) {
            $this->invalidateControl();

            $this->payload->title = $this->template->seoTitle;
            $this->payload->url = $this->link('this', array(
                'path' => $this->actualPage->getPath(),
                'abbr' => $this->abbr
            ));
            $this->payload->nameSeo = $this->actualPage->getSlug();
            $this->payload->name = $this->actualPage->getTitle();
            $this->payload->class = $this->actualPage->getClass();
        }
    }

    public function generateSitemap()
    {
        $sitemapXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        $repository = $this->em->getRepository('WebCMS\Entity\Page');
        $pages = $repository->findAll();

        foreach ($pages as $page) {
            if ($page->getParent() !== null) {
            $sitemapXml .= "<url>\n\t<loc>" . $this->getSitemapLink($page) . "</loc>\n</url>\n";
            }
        }

        $sitemapXml .= '</urlset>';

        file_put_contents('./sitemap.xml', $sitemapXml);
    }

    private function getSitemapLink($page)
    {
        $url = $this->context->httpRequest->url->baseUrl;
        $url .=!$page->getLanguage()->getDefaultFrontend() ? $page->getLanguage()->getAbbr() . '/' : '';
        $url .= $page->getPath();

        return $url;
    }

    public function createTemplate($class = NULL)
    {
        $template = parent::createTemplate($class);

        $template->setTranslator($this->translator);
        $template->registerHelperLoader('\WebCMS\Helpers\SystemHelper::loader');

        return $template;
    }

    protected function getLanguageId()
    {
        return $this->language->getId();
    }

    public function createForm($do = '', $action = 'default', $context = null)
    {
        $form = new UI\Form();

        if ($context != null) {
            $page = $context->actualPage;
            $abbr = $context->abbr;
            $translator = $context->translator;
            $c = $context;
        } else {
            $page = $this->actualPage;
            $abbr = $this->abbr;
            $translator = $this->translator;
            $c = $this;
        }

        $form->getElementPrototype()->action = $c->link($action, array(
            'path' => $page->getPath(),
            'abbr' => $abbr,
            'do' => $do
        ));

        $form->setTranslator($translator);
        $form->setRenderer(new BootstrapRenderer);

        return $form;
    }

    public function createComponentLanguagesForm()
    {
        $form = $this->createForm();

        $form->getElementPrototype()->action = $this->link('this', array(
            'id' => $this->actualPage->getId(),
            'path' => $this->actualPage->getPath(),
            'abbr' => $this->abbr,
            'do' => 'languagesForm-submit'
        ));

        $items = array();
        foreach ($this->languages as $lang) {
            $items[$lang->getId()] = $lang->getName();
        }

        $form->addSelect('language', 'Change language')->setItems($items)->setDefaultValue($this->language->getId());
        $form->addSubmit('submit', 'Change');
        $form->onSuccess[] = callback($this, 'languagesFormSubmitted', array('abbr' => '', 'path' => $this->actualPage->getPath()));

        return $form;
    }

    public function languagesFormSubmitted($form)
    {
        $values = $form->getValues();

        $this->changeLanguage($values->language);
    }

    private function changeLanguage($idLanguage)
    {
        $home = $this->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'language' => $idLanguage,
            'default' => TRUE
        ));

        if (is_object($home)) {

            $abbr = $home->getLanguage()->getDefaultFrontend() ? '' : $home->getLanguage()->getAbbr() . '/';

            $this->redirectUrl(
                    $this->getHttpRequest()->url->baseUrl .  $abbr . $home->getPath()
            );
        } else {
            $this->flashMessage('No default page for selected language.', 'error');
        }
    }

    /**
     * Set up boxes (call box function and save it into array) and give them to the tempalte.
     */
    private function setUpBoxes()
    {
        $parameters = $this->context->getParameters();
        $boxes = $parameters['boxes'];

        $finalBoxes = array();
        if (is_array($boxes)) {
            foreach ($boxes as $key => $box) {
                $finalBoxes[$key] = NULL;
            }
        }

        $assocBoxes = $this->em->getRepository('WebCMS\Entity\Box')->findBy(array(
            'pageTo' => $this->actualPage
        ));

        foreach ($assocBoxes as $box) {
            $presenter = 'FrontendModule\\' . $box->getModuleName() . 'Module\\' . $box->getPresenter() . 'Presenter';
            $object = new $presenter;

            if (method_exists($object, $box->getFunction())) {
                $function = $box->getFunction();
                $pageFrom = $box->getPageFrom();
                $finalBoxes[$box->getBox()] = call_user_func(array($object, $function), $this, $pageFrom);
            }
        }

        $this->template->boxes = $finalBoxes;
    }

    /**
     * Load all system structures.
     * @return type
     */
    private function getStructures($direct = TRUE, $rootClass = 'nav navbar-nav', $dropDown = FALSE, $rootId = '')
    {
        $repo = $this->em->getRepository('WebCMS\Entity\Page');

        $structs = $repo->findBy(array(
            'language' => $this->language,
            'parent' => NULL
        ));

        $structures = array();
        foreach ($structs as $s) {
            $structures[$s->getTitle()] = $this->getStructure($this, $s, $repo, $direct, $rootClass, $dropDown, TRUE, NULL, '', null, $rootId);
        }

        return $structures;
    }

    /**
     * TODO refactor, maybe it will be better in template
     * Get structure by node. In node is set to null whole tree is returned.
     * @param  type       $node
     * @param  Repository $repo
     * @param  type       $direct
     * @param  type       $rootClass
     * @param  type       $dropDown
     * @param BasePresenter $context
     * @return type
     */
    protected function getStructure($context, $node = NULL, $repo, $direct = TRUE, $rootClass = 'nav navbar-nav', $dropDown = FALSE, $system = TRUE, $fromPage = NULL, $sideClass = 'nav navbar', $moduleNameAbstract = null, $rootId = '')
    {
        return $repo->childrenHierarchy($node, $direct, array(
                    'decorate' => true,
                    'html' => true,
                    'rootOpen' => function ($nodes) use ($rootClass, $dropDown, $sideClass, $rootId) {

                $drop = $nodes[0]['level'] == 2 ? TRUE : FALSE;
                $class = $nodes[0]['level'] < 2 ? $rootClass : $sideClass;

                if ($drop && $dropDown)
                    $class .= ' dropdown-menu submenu';

        $htmlId = '';
        if (!empty($rootId)) {
            $htmlId = ' id = "' . $rootId . '"';
        }

                return '<ul class="' . $class . '"' . $htmlId . '>';
            },
                    'rootClose' => '</ul>',
                    'childOpen' => function ($node) use ($dropDown, $context) {
                $hasChildrens = count($node['__children']) > 0 ? TRUE : FALSE;
                $param = $context->getRequest()->getParameters();
                $active = $context->getParam('id') == $node['id'] ? TRUE : FALSE;
                $class = '';

        if (!array_key_exists('fullPath', $param)) {
            $param['fullPath'] = '/defPath';
        }

                if (array_key_exists('redirect', $node)) {
                    if ($param['fullPath'] == $node['redirect']) {
                        $active = TRUE;
                    }
                }

                if ($context->getParam('lft') > $node['lft'] && $context->getParam('lft') < $node['rgt'] && $context->getParam('root') == $node['root']) {
                    $class .= ' active';
                }

                if ($hasChildrens && $dropDown)
                    $class .= ' dropdown';

                if ($active)
                    $class .= ' active';

                if (!$node['visible'])
                    $class .= ' hidden';

                return '<li class="' . $class . '">';
            },
                    'childClose' => '</li>',
                    'nodeDecorator' => function ($node) use ($dropDown, $system, $context, $fromPage, $moduleNameAbstract) {
                $hasChildrens = count($node['__children']) > 0 ? TRUE : FALSE;
                $params = '';
                $class = '';

                $moduleName = array_key_exists('moduleName', $node) ? $node['moduleName'] : $moduleNameAbstract;
                $presenter = array_key_exists('presenter', $node) ? $node['presenter'] : 'Categories';
                $path = $moduleName === $moduleNameAbstract && !$system ? (is_object($fromPage) ? $fromPage->getPath() . '/' : '') . $node['path'] : $node['path'];

                $link = $context->link(':Frontend:' . $moduleName . ':' . $presenter . ':default', array('id' => $node['id'], 'path' => $path, 'abbr' => $context->abbr));

                $span = '';
                if ($hasChildrens && $node['level'] == 1 && $dropDown) {
                    $params = ' data-toggle="dropdown"';
                    $class .= ' dropdown-toggle';
                    //$link = '#';
                    $span = '<span class="caret"></span>';
                }

                if (!empty($node['class']))
                    $class .= ' ' . $node['class'];

                return '<a ' . $params . ' data-seo="' . $path . '" class="' . $class . '" href="' . $link . '"><span>' . $node['title'] . $span . '</span></a>';
            }
        ));
    }

    public function getBreadcrumbs()
    {
        // bredcrumb
        $default = $this->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'default' => TRUE,
            'language' => $this->language
        ));

        if ($this->actualPage->getDefault())
            $default = array();
        else
            $default = array($default);

        // system breadcrumbs
        $system = $default + $this->em->getRepository('WebCMS\Entity\Page')->getPath($this->actualPage);
        $finalSystem = array();
        foreach ($system as $item) {
            if ($item->getParent()) {
                $finalSystem[] = new \WebCMS\Entity\BreadcrumbsItem($item->getId(), $item->getModuleName(), $item->getPresenter(), $item->getTitle(), $item->getPath()
                );
            }
        }

        foreach ($this->breadcrumbs as $b) {
            array_push($finalSystem, $b);
        }

        return $finalSystem;
    }

    /**
     *
     */
    public function addToBreadcrumbs($id, $moduleName, $presenter, $title, $path)
    {
        $this->breadcrumbs[] = new \WebCMS\Entity\BreadcrumbsItem($id, $moduleName, $presenter, $title, $path);
    }

    public function selfRedirect($path = '')
    {
        $this->redirect('this', array(
            'id' => $this->actualPage->getId(),
            'path' => $this->actualPage->getPath() . $path,
            'abbr' => $this->abbr,
        ));
    }

    /* @deprecated */

    public function flashMessageTranslated($message, $type = 'info')
    {
        $this->flashMessage($this->translation[$message], $type);
    }

    public function flashMessage($text, $type = 'info')
    {
        parent::flashMessage($this->translation[$text], $type);
    }
    
    /**
    * Formats view template file names.
    * @return string[]
    */
    public function formatTemplateFiles()
    {
        $name = $this->getName();
        $presenter = substr($name, strrpos(':' . $name, ':'));
        $dir = dirname($this->getReflection()->getFileName());
        $dir = is_dir("$dir/templates") ? $dir : dirname($dir);
        $appPath = APP_DIR . "/templates/" . lcfirst($presenter) . "-module/$presenter/$this->view.latte";
        
        return array(
            $appPath,
            "$dir/templates/$presenter/$this->view.latte",
            "$dir/templates/$presenter.$this->view.latte",
            "$dir/templates/$presenter/$this->view.phtml",
            "$dir/templates/$presenter.$this->view.phtml",
        );
    }
    
     /**
      * Formats layout template file names.
      * @return array
      */
     public function formatLayoutTemplateFiles()
     {
         $name = $this->getName();
         $presenter = substr($name, strrpos(':' . $name, ':'));
         $layout = $this->layout ? $this->layout : 'layout';
         $dir = dirname($this->getReflection()->getFileName());
         $dir = is_dir("$dir/templates") ? $dir : dirname($dir);
         $list = array(
             APP_DIR . "/templates/@$layout.latte",
             "$dir/templates/$presenter/@$layout.latte",
             "$dir/templates/$presenter.@$layout.latte",
             "$dir/templates/$presenter/@$layout.phtml",
             "$dir/templates/$presenter.@$layout.phtml",
         );
         do {
             $list[] = "$dir/templates/@$layout.latte";
             $list[] = "$dir/templates/@$layout.phtml";
             $dir = dirname($dir);
         } while ($dir && ($name = substr($name, 0, strrpos($name, ':'))));
         return $list;
     }

    /**
    * Formats view template file names.
    * @return array
    */
    public function formatTemplateFiles()
    {
        $name = $this->getName();

        $path = explode(':', $name);
        $module = $path[1];
        $presenter = $path[2];
        $dir = dirname($this->getReflection()->getFileName());
        $dir = is_dir("$dir/templates") ? $dir : dirname($dir);
        
        $appPath = APP_DIR . "/templates/" . lcfirst($module) . "-module/$presenter/$this->view.latte";
        
        return array(
            $appPath,
            "$dir/templates/$presenter/$this->view.latte",
            "$dir/templates/$presenter.$this->view.latte",
            "$dir/templates/$presenter/$this->view.phtml",
            "$dir/templates/$presenter.$this->view.phtml",
        );
    }
    
     /**
      * Formats layout template file names.
      * @return array
      */
     public function formatLayoutTemplateFiles()
     {
         $name = $this->getName();
         $presenter = substr($name, strrpos(':' . $name, ':'));
         $layout = $this->layout ? $this->layout : 'layout';
         $dir = dirname($this->getReflection()->getFileName());
         $dir = is_dir("$dir/templates") ? $dir : dirname($dir);
         $list = array(
             APP_DIR . "/templates/@$layout.latte",
             "$dir/templates/$presenter/@$layout.latte",
             "$dir/templates/$presenter.@$layout.latte",
             "$dir/templates/$presenter/@$layout.phtml",
             "$dir/templates/$presenter.@$layout.phtml",
         );
         do {
             $list[] = "$dir/templates/@$layout.latte";
             $list[] = "$dir/templates/@$layout.phtml";
             $dir = dirname($dir);
         } while ($dir && ($name = substr($name, 0, strrpos($name, ':'))));
         return $list;
     }

}
