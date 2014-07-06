<?php

namespace AdminModule;

use Nette\Application\UI,
    Nette\Security as NS;

class LoginPresenter extends BasePresenter
{
    public function beforeRender()
    {
        parent::beforeRender();

        if ($this->getUser()->isLoggedIn()) {
            $this->forward('Homepage:');
        }
    }

    /**
     * Sign in form component factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm()
    {
        $form = $this->createForm();

        $form->addText('username', 'Username')
            ->setRequired('Please provide a username.')
            ->setAttribute('placeholder', $this->translation['Username']);

        $form->addPassword('password', 'Password')
            ->setRequired('Please provide a password.')
            ->setAttribute('placeholder', $this->translation['Password']);

        $form->addCheckbox('remember', 'Permanent login?');

        $form->addSubmit('send', 'Log in');

        $form->onSuccess[] = callback($this, 'signInFormSubmitted');

        return $form;
    }

    public function renderDefault()
    {
        $this->setLayout('login');
    }

    public function signInFormSubmitted($form)
    {
        try {
            $values = $form->getValues();
            if ($values->remember) {
            $this->getUser()->setExpiration('+ 14 days', FALSE);
            } else {
            $this->getUser()->setExpiration('+ 20 minutes', TRUE);
            }
            $this->getUser()->login($values->username, $values->password);
            $this->forward('Homepage:');
        } catch (NS\AuthenticationException $e) {
            $this->flashMessage($this->translation[$e->getMessage()], 'danger');
            $form->addError($e->getMessage());
        }
    }

    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage($this->translation['You have been signed out.']);
        $this->forward('Login:');
    }

}
