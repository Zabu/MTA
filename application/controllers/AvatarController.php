<?php

class AvatarController extends Zend_Controller_Action {
	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		Zend_Loader::loadClass('Utilisateur');
		Zend_Loader::loadClass('Avatar');
		Zend_Loader::loadClass('Classe');
		Zend_Loader::loadClass('Ville');
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
	}
	
	function indexAction() {
		$this->_redirect('/');
	}
	
	function preDispatch() {
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity()) {
			$this->_redirect('auth/loginneeded');
		}
	}
	
	function avatarAction(){
		$this->view->title = "Gestion des avatars";
		$avatar = new Avatar();
		$classe = new Classe();
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$avatars = $avatar->findByUser($this->user->id_utilisateur);
		$this->view->avatar = $avatars;
		$this->view->classe = $classe;
	}
	
	function creationavatarAction(){
		$this->view->title = "Créer votre avatar";
		$this->view->action = "creationavatar";
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$avatar = new Avatar();
		$classe = new Classe();
		$ville = new Ville();
		$ava = array();
		$erreurs = array();
		$this->view->classe = $classe->fetchAll();
		$this->view->ville = $ville->fetchAll();
		
		if($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$filter = new Zend_Filter_StripTags();
			$ava['avatarpseudo'] = trim($filter->filter($this->_request->getPost('avatarpseudo')));
			$ava['classe'] =  trim($filter->filter($this->_request->getPost('classe')));
			$ava['ville'] =  trim($filter->filter($this->_request->getPost('ville')));
			if(!preg_match('`^[[:alnum:]]{3,20}$`', $ava['avatarpseudo']))
				$erreurs['avatarpseudo'] = '<span class="erreur">Nom incorrect.</span>';
			if($avatar->findByNom($ava['avatarpseudo']))
				$erreurs['avatarpseudo'] = '<span class="erreur">Veuillez choisir un nom différent.</span>';
			if(empty($ava['classe']))
				$erreurs['classe'] = '<span class="erreur">Vous devez choisir une classe.</span>';
			if(empty($ava['ville']))
				$erreurs['ville'] = '<span class="erreur">Vous devez choisir un village.</span>';
			$ok = true;
			foreach($erreurs as $erreur){
				if($erreur != "")
					$ok = false;
			}
			if($ok){
			    if(!empty($_FILES['fichier']['name'])) {
					$max_size   = 100000;     // Taille max en octets du fichier
					$width_max  = 100;        // Largeur max de l'image en pixels
					$height_max = 100;        // Hauteur max de l'image en pixels 
					$nom_file   = $_FILES['fichier']['name'];
					$taille     = $_FILES['fichier']['size'];
					$tmp        = $_FILES['fichier']['tmp_name'];
					$extension = substr($nom_file, -4);
			        if($extension == '.png') {
			            $infos_img = getimagesize($_FILES['fichier']['tmp_name']);
			            if(($infos_img[0] <= $width_max) && ($infos_img[1] <= $height_max) && ($_FILES['fichier']['size'] <= $max_size)) {
							$fichier_temp = $_FILES['fichier']['tmp_name'];
			            }
						else {
			                // Sinon on affiche une erreur pour les dimensions et taille de l'image
							$erreurs["portrait"] = '<span class="erreur">Les dimensions et/ou la taille de l\'image dépassent nos limites exigées.</span>';
						}
					}
					else {
			            // Sinon on affiche une erreur pour l'extension
			            $erreurs["portrait"] = '<span class="erreur">Votre image doit être en .png .</span>';
					}
				}
				else {
					$extension = ".png";
					$fichier_default = $_SERVER['DOCUMENT_ROOT'].'/Magic_TA/public/images/avatar/default_avatar.png';
				}
				if($extension == '.png') {
					$data = array(
						'id_utilisateur' => $this->user->id_utilisateur,
						'id_classe' => $ava['classe'],
						'id_case' => $ville->findById($ava['ville'])->id_case,
						'nom_avatar' => $ava['avatarpseudo'],
					);
					$target = $_SERVER['DOCUMENT_ROOT'].'/Magic_TA/public/images/avatar/';  // Repertoire cible
					$avatar->insert($data);
					$avatar = $avatar->findByNom($ava['avatarpseudo']);
					$idava = $avatar->id_avatar;
					$fichier = $idava.$extension;
					if(!empty($_FILES['fichier']['name'])) {
						move_uploaded_file($fichier_temp, $target.$fichier);
					}
					else {
						copy($fichier_default, $target.$fichier);
					}
				}
				$ok = true;
				foreach($erreurs as $erreur){
					if($erreur != "")
						$ok = false;
				}
				if($ok){
					$this->_redirect('avatar/avatar');
					return;
				}
			}
			$this->view->avatar = $ava;
			$this->view->erreurs = $erreurs;
		}
	}
	
	function supprimeravatarAction(){
		$this->view->title = "Suppression de l'avatar";
		$avatar = new Avatar();
		if ($this->_request->isPost()) {
            Zend_Loader::loadClass('Zend_Filter_Alpha');
            $filter = new Zend_Filter_Alpha();
            $id = (int)$this->_request->getPost('id');
            $del = $filter->filter($this->_request->getPost('del'));
            if ($del == 'Oui' && $id > 0) {
                $where = $avatar->getAdapter()->quoteInto('id_avatar =?', $id);
                $rows_affected = $avatar->delete($where);
				$fichier = $_SERVER['DOCUMENT_ROOT'].'/Magic_TA/public/images/avatar/'.$id.".png";
				unlink($fichier);
            }
			$this->_redirect('avatar/avatar');
        }
		else {
            $id = (int)$this->_request->getParam('id');
			$this->user = Zend_Auth::getInstance()->getIdentity();
			if(avatarViolation($id,$this->user->id_utilisateur)){
				$this->_redirect('avatar/avatar');
				return;
			}
            if ($id > 0) {
                $this->view->avatar = $avatar->fetchRow('id_avatar='.$id);
                if ($this->view->avatar->id_avatar > 0)
                    return;
				else
					$this->_redirect('avatar/avatar');
            }
        }
	}
	
	function modifieravatarAction() {
		$this->view->title = "Créer votre avatar";
		$this->view->action = "creationavatar";
		$this->user = Zend_Auth::getInstance()->getIdentity();
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		$filter = new Zend_Filter_StripTags();
		$ava = array();
		$avatar = new Avatar();
		$erreurs = array();
		if($this->_request->isPost()) {
			$id = (int)$this->_request->getPost('id');
			$ava['id'] = $this->_request->getPost('id');
			$extension ="";
			if(!empty($_FILES['fichier']['name'])) {
				$max_size   = 100000;     // Taille max en octets du fichier
				$width_max  = 100;        // Largeur max de l'image en pixels
				$height_max = 100;        // Hauteur max de l'image en pixels 
				$nom_file   = $_FILES['fichier']['name'];
				$taille     = $_FILES['fichier']['size'];
				$tmp        = $_FILES['fichier']['tmp_name'];
				$extension = substr($nom_file, -4);
				if($extension == '.png') {
					$infos_img = getimagesize($_FILES['fichier']['tmp_name']);
					if(($infos_img[0] <= $width_max) && ($infos_img[1] <= $height_max) && ($_FILES['fichier']['size'] <= $max_size))
						$fichier_temp = $_FILES['fichier']['tmp_name'];
					else // Sinon on affiche une erreur pour les dimensions et taille de l'image
						$erreurs["portrait"] = '<span class="erreur">Les dimensions et/ou la taille de l\'image dépassent nos limites exigées.</span>';
				}
				else {
					// Sinon on affiche une erreur pour l'extension
					$erreurs["portrait"] = '<span class="erreur">Votre image doit être en .png .</span>';
				}
			}
			else {
				$erreurs["portrait"] = '<span class="erreur">Il n\'y a pas d\'image .</span>';
			}
			$ok = true;
			foreach($erreurs as $erreur){
				if($erreur != "")
					$ok = false;
			}
			if($ok){
				if($extension == '.png') {
					$data = array(
						'id_utilisateur' => $this->user->id_utilisateur,
						'id_classe' => $avatar->findById($id)->id_classe,
						'id_case' => 1, //à revoir pour la ville
						'nom_avatar' => $avatar->findById($id)->nom,
					);
					$target = $_SERVER['DOCUMENT_ROOT'].'/Magic_TA/public/images/avatar/';  // Repertoire cible
					$where = 'id_avatar = ' . $id;
					$avatar->update($data, $where);
					$fichier = $id.$extension;
					move_uploaded_file($fichier_temp, $target.$fichier);
				}
				
				$this->_redirect('avatar/avatar');
				return;
			}
		}
		else {
            $id = (int)$this->_request->getParam('id', 0);
			$ava['id'] = $id;
    	    if($id > 0)
    	        $this->view->avatar = $avatar->fetchRow('id_avatar='.$id);
			else
				$this->_redirect('avatar/avatar');
		}
		$this->view->erreurs = $erreurs;
		$this->view->avatar = $ava;
	}
}

function avatarViolation($id, $id_utilisateur){
	$avatar = new Avatar();
	$avatar = $avatar->findById($id);
	if ($id_utilisateur != $avatar->id_utilisateur)
		return true;
	return false;
}
