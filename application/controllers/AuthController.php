<?php

class AuthController extends Zend_Controller_Action {
	function init(){
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
	}
	
	function indexAction(){
		$this->_redirect('/');
	}
	
	function loginAction(){
		$this->view->message = '';
		if($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$f = new Zend_Filter_StripTags();
			$username = $f->filter($this->_request->getPost('username'));
			$password = md5($f->filter($this->_request->getPost('password')));
			if (!empty($username)) {
				Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
				$dbAdapter = Zend_Registry::get('dbAdapter');
				$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
				$authAdapter->setTableName('utilisateur');
				$authAdapter->setIdentityColumn('login_utilisateur');
				$authAdapter->setCredentialColumn('pass_utilisateur');
				
				$authAdapter->setIdentity($username);
				$authAdapter->setCredential($password);
				
				$auth = Zend_Auth::getInstance();
				$result = $auth->authenticate($authAdapter);
				if ($result->isValid()) {
					$data = $authAdapter->getResultRowObject(null, 'password');
					$auth->getStorage()->write($data);
					$this->_redirect('/');
				}
			}
			$this->_redirect('auth/loginfail');
		}
	}
	
	function logoutAction(){
		Zend_Auth::getInstance()->clearIdentity();
		require_once 'Zend/Session.php';
		Zend_Session::destroy();
		$this->_redirect('/');
	}
	
	function loginfailAction(){
		$this->view->title = "Connexion échouée";
	}
	
	function loginneededAction(){
		$this->view->title = "Connexion nécessaire";
	}
}