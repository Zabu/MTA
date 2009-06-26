<?php
	
class UtilisateurController extends Zend_Controller_Action {
	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		Zend_Loader::loadClass('Utilisateur');
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
	}
	
	function indexAction() {
		$this->_redirect('/');
	}
	
	function gestioncompteAction() {
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->title = "Modifiez votre profil";
		$utilisateur = new Utilisateur();
		$uti = array();
		$erreurs = array();
		if ($this->_request->isPost()) {
            Zend_Loader::loadClass('Zend_Filter_StripTags');
            $filter = new Zend_Filter_StripTags();
            $id = $this->user->id_utilisateur;
            $uti['login'] = trim($filter->filter($this->_request->getPost('login')));
            $uti['pass'] = trim($filter->filter($this->_request->getPost('pass')));
			$uti['pass1'] = trim($filter->filter($this->_request->getPost('pass1')));
			$uti['pass2'] = $filter->filter($this->_request->getPost('pass2'));
			$uti['mail'] = $filter->filter($this->_request->getPost('mail'));
			$uti['mail1'] = trim($filter->filter($this->_request->getPost('mail1')));
			$uti['mail2'] = trim($filter->filter($this->_request->getPost('mail2')));
            $pass = $this->user->pass_utilisateur;
            if (verifInfoUtilisateur($uti, $erreurs, $utilisateur, $pass)) {
				$id = $this->user->id_utilisateur;
				$data = array(
					'login_utilisateur' => $this->user->login_utilisateur,
					'pass_utilisauter' => md5($uti['pass1']),
					'mail_utilisateur' => $uti['mail1'],
				);
				$where = 'id_utilisateur = ' . $id;
				$utilisateur->update($data, $where);
				$this->_redirect('/');
				return;
            }
        }
		$this->view->action = "gestioncompte";
		$this->view->utilisateur = $utilisateur->createRow();
		$this->view->erreurs = $erreurs;
	}
}

function verifInfoUtilisateur($uti, &$erreurs, $utilisateur, $pass){
	if($pass!= md5($uti['pass']))
		$erreurs['pass'] = '<span class="erreur">Mot de passe incorrect.</span>';
	if(!preg_match('`^[[:alnum:]]{6,15}$`', $uti['pass1']))
		$erreurs['pass1'] = '<span class="erreur">Mot de passe incorrect.</span>';
	if($uti['pass1'] != $uti['pass2'])
	$erreurs['pass2'] = '<span class="erreur">Confirmation échouée.</span>';
	if(!preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $uti['mail1']))
		$erreurs['mail1'] = '<span class="erreur">Mail incorrect.</span>';
	if($utilisateur->findByMail($uti['mail1']))
		$erreurs['mail1'] = '<span class="erreur">Nous n\'acceptons qu\'un compte par Email.</span>';
	if($uti['mail1'] != $uti['mail2'])
		$erreurs['mail2'] = '<span class="erreur">Confirmation échouée.</span>';
	foreach($erreurs as $err){
		if ($err != "")
			return false;
	}
	return true;
}