<?php

class IndexController extends Zend_Controller_Action
{
	function init(){
		$this->view->baseUrl = $this->_request->getBaseUrl();
		Zend_Loader::loadClass('Utilisateur');
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
	}
	
	function indexAction(){
		$this->view->title = "Bienvenue à Magic : The Awakening";
	}
	
	function inscriptionAction(){
		$this->view->title = "Inscription à Magic : The Awakening";
		Zend_Loader::loadClass('Zend_View');
		$utilisateur = new Utilisateur();
		$view = new Zend_View();
		$erreurs = array();
		$uti = array();
		if ($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter_StripTags');
            $filter = new Zend_Filter_StripTags();
			$uti['login'] = trim($filter->filter($this->_request->getPost('login')));
            $uti['pass1'] = trim($filter->filter($this->_request->getPost('pass1')));
			$uti['pass2'] = trim($filter->filter($this->_request->getPost('pass2')));
			$uti['mail1'] = $filter->filter($this->_request->getPost('mail1'));
			$uti['mail2'] = $filter->filter($this->_request->getPost('mail2'));
			if(verifInfoUtilisateur($uti, $erreurs, $utilisateur)){
                $data = array(
					'login_utilisateur' => $uti['login'],
					'pass_utilisateur' => md5($uti['pass1']),
					'mail_utilisateur' => $uti['mail1'],
				);
				$utilisateur->insert($data);
                $this->_redirect('index/inscriptionsuccess');
                return;
            }
		}
		$utilisateuradmin = new Utilisateur();
		$this->view->utilisateuradmin = $utilisateuradmin;
		$this->view->erreurs = $erreurs;
        $this->view->utilisateur = $utilisateur->createRow();
		$this->view->utiltmp = $uti;
        $this->view->action = 'inscription';
        $this->view->buttonText = 'Valider';
	}
	
	function inscriptionsuccessAction(){
		$this->view->title = "Inscription à Magic : The Awakening";
		$utilisateuradmin = new Utilisateur();
		$this->view->utilisateuradmin = $utilisateuradmin;
	}
	
	function presentationAction(){
		$this->view->title = "Présentation de Magic : The Awakening";
		$utilisateuradmin = new Utilisateur();
		$this->view->utilisateuradmin = $utilisateuradmin;
	}
	
	function contactAction(){
		$this->view->title = "Contacter Magic : The Awakening";
		Zend_Loader::loadClass('Zend_View');
		$erreurs = array();
		$mess = array();
		if($this->_request->isPost()){
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$filter = new Zend_Filter_StripTags();
			$mess['mail'] = trim($filter->filter($this->_request->getPost('mail')));
			$mess['sujet'] = trim($filter->filter($this->_request->getPost('sujet')));
			$mess['message'] = trim($filter->filter($this->_request->getPost('message')));
			if(!preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $mess['mail']))
				$erreurs['mail'] = '<span class="erreur">Email incorrect.</span>';
			$ok = true;
			foreach($erreurs as $err){
				if ($err != "")
					$ok = false;
			}
			if($ok){
				require_once 'Zend/Mail.php';
				$mail = new Zend_Mail();
				$mail->setBodyText($mess['message']);
				$mail->setFrom($mess['mail'], 'Contact');
				$mail->addTo('ren.laurent@hotmail.fr', 'Contact MTA');
				$mail->setSubject('Contact');
				$mail->send();
				$this->_redirect('/');
                return;
			}
		}
		$utilisateuradmin = new Utilisateur();
		$this->view->utilisateuradmin = $utilisateuradmin;
		$this->view->erreurs = $erreurs;
		$this->view->mess = $mess;
	}
}

function verifInfoUtilisateur($uti, &$erreurs, $user){
	if(!preg_match('`^[[:alnum:]]{3,20}$`', $uti['login']))
		$erreurs['login'] = '<span class="erreur">Login incorrect.</span>';
	if($user->findByLogin($uti['login']))
		$erreurs['login'] = '<span class="erreur">Login déjà existant.</span>';
	if(!preg_match('`^[[:alnum:]]{6,15}$`', $uti['pass1']))
		$erreurs['pass1'] = '<span class="erreur">Mot de passe incorrect.</span>';
	if($uti['pass1'] != $uti['pass2'])
		$erreurs['pass2'] = '<span class="erreur">Confirmation échouée.</span>';
	if(!preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $uti['mail1']))
		$erreurs['mail1'] = '<span class="erreur">Mail incorrect.</span>';
	if($user->findByMail($uti['mail1']))
		$erreurs['mail1'] = '<span class="erreur">Nous n\'acceptons qu\'un compte par Email.</span>';
	if($uti['mail1'] != $uti['mail2'])
		$erreurs['mail2'] = '<span class="erreur">Confirmation échouée.</span>';
	foreach($erreurs as $err){
		if ($err != "")
			return false;
	}
	return true;
}