<?php

/**
 * account actions.
 *
 * @package    sflimetracker
 * @subpackage account
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class accountActions extends sfActions
{
  public function executeLogin($request)
  {
    
    $this->form = new LoginForm($this->getUser());

    if($this->getUser()->isAuthenticated())
      $this->redirect('@homepage');
    if($request->getMethod () == sfRequest::POST && !$this->getUser()->isAuthenticated())
    {
      $this->form->bind($request->getPostParameters());
      
      if($this->form->isValid())
      {
        $this->getUser()->setAuthenticated(true);
        if($request->getReferer())
        {
          $this->redirect($request->getReferer());
        }
        {
          $this->redirect('@homepage');
        }
      }
      else
      {
        return sfView::ERROR;
      }
    }
  }
 
  public function executeLogout($request)
  {
    if($request->getMethod () == sfRequest::POST && $this->getUser()->isAuthenticated())
    {
      $this->getUser()->setAuthenticated(false);
      $this->redirect('@homepage');
    }
  }
  public function executePassword($request)
  {
    $user=$this->getUser();
    $this->form = $form = new PasswordForm($user);
    if($request->getMethod () == sfRequest::POST)
    {
      $form->bind($request->getPostParameters());
      if($form->isValid())
      {
        if($user->isAuthenticated())
        {
          $can_write=$user->canWritePasswd();
          $payload=$user->setPassword($form->getValue('password'),$can_write);
          if($can_write)
          {
            $user->setAuthenticated(false);
            $user->setFlash('notice','Password changed');
            return $this->redirect('@homepage');
          }
        }
        else
        {
          $payload=$user->setPassword($form->getValue('password'),FALSE);
        }
        $this->payload=$payload;
        if(isset($e))
          $this->exception=$e;
      }
    }
  }
}
