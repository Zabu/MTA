<?php

class AdminController extends Zend_Controller_Action {
	function init() {
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		Zend_Loader::loadClass('Admin');
		Zend_Loader::loadClass('Utilisateur');
		Zend_Loader::loadClass('Avatar');
		Zend_Loader::loadClass('Classe');
		Zend_Loader::loadClass('Objet');
		Zend_Loader::loadClass('Equipement');
		Zend_Loader::loadClass('EquipementArme');
		Zend_Loader::loadClass('EquipementArmure');
		Zend_Loader::loadClass('Monstre');
		Zend_Loader::loadClass('Zone');
		Zend_Loader::loadClass('ZoneMonstre');
		Zend_Loader::loadClass('LigneInventaire');
		Zend_Loader::loadClass('Competence');
		Zend_Loader::loadClass('AvatarCompetence');
		Zend_Loader::loadClass('CompetenceMonstre');
		// Zend_Loader::loadClass('Niveau');
		// Zend_Loader::loadClass('Objetmarchand');
		Zend_Loader::loadClass('ObjetMonstre');
		// Zend_Loader::loadClass('Objet_Soin');
		// Zend_Loader::loadClass('Quete');
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
	}
	
	function preDispatch() {
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity()) {
			$this->_redirect('auth/loginneeded');
			return;
		}
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$utilisateur = new Utilisateur();
		if(!($utilisateur->isAdmin($this->user->id_utilisateur))){
			$this->_redirect('/');
		}
	}
	
	function adminAction(){
		$this->view->title = "Administration du site";
		$avatar      = new Avatar();
		$classe      = new Classe();
		$arme        = new EquipementArme();
		$armure      = new EquipementArmure();
		$utilisateur = new Utilisateur();
		$equipement  = new Equipement();
	}
	
	function utilisateurAction() {
		$this->view->title = "Administration des utilisateurs";
		$utilisateur = new Utilisateur();
		$this->view->utilisateur = $utilisateur->fetchAll();
	}
	
	function supprimerutilisateurAction() {
		$this->view->title = "Suppression d'un utilisateur";
		$utilisateuradmin = new Utilisateur();
		$utilisateur = new Utilisateur();
		$avatar = new Avatar();
		$objet  = new LigneInventaire();
		$compavatar = new AvatarCompetence();
		
		$id = $this->_request->getParam('id');
		$utilisateur = $utilisateur->findById($id);
		
		// L'admin ne peut pas supprimer son propre compte !
		if($utilisateuradmin->isAdmin($id)) {
			$this->_redirect('admin/utilisateur');
			return;
		}
		
		if($this->_request->isPost()) {
			$del = $this->_request->getPost('del');
			if ($del == 'Oui' && $id > 0) {
				// Recuperation de tous les avatars de l'utilisateur
				$avatars = $avatar->findByUser($id);
				foreach($avatars as $avatar) {
					// Recuperation des competences de chaque avatar
					$compavatars = $compavatar->findByIdAvatar($avatar->id_avatar);
					foreach($compavatars as $compavatar)
						$compavatar->delete('id_competence = '. $compavatar->id_competence); // Suppression des competences
					$compavatar = new AvatarCompetence();
					// Recuperation des objets de chaque avatar
					$objets = $objet->findByIdAvatar($avatar->id_avatar);
					foreach($objets as $objet)
						$objet->delete('id_objet = ' . $objet->id_objet); // Suppression des objets
					$objet = new LigneInventaire;
					$avatar->delete('id_avatar = ' . $avatar->id_avatar); //Suppression de l'avatar
				}

				$utilisateur = new Utilisateur();
				// Recuperation de tous les avatars du joueur
				$where = "id_utilisateur = " . $id;
				$utilisateur->delete($where);
			}
			$this->_redirect('admin/utilisateur');
			return;
		}
		$this->view->utilisateur = $utilisateur;
	}
	
	function avatarAction() {
		$this->view->title = "Administration des avatars";
		$avatar = new Avatar();
		$utilisateur = new Utilisateur();
		$this->view->utilisateur = $utilisateur;
		$this->view->avatar = $avatar->fetchAll();
	}
	
	function supprimeravatarAction() {
		$this->view->title = "Suppression de l'avatar";
		$id = (int)$this->_request->getParam('id');
		$avatar = new Avatar();
		$avatar = $avatar->findById($id);
		if($this->_request->isPost()) {
			$del = $this->_request->getPost('del');
			if ($del == 'Oui' && $id > 0) {
				foreach($avatars as $avatar) {
					// Recuperation des competences de chaque avatar
					$compavatars = $compavatar->findByIdAvatar($avatar->id_avatar);
					foreach($compavatars as $compavatar) 
						$compavatar->delete('id_competence = '. $compavatar->id_competence); // Suppression des competences
					$compavatar = new AvatarCompetence();
					// Recuperation des objets de chaque avatar
					$objets = $objet->findByIdAvatar($avatar->id_avatar);
					foreach($objets as $objet) {
						$objet->delete('id_objet = ' . $objet->id_objet); // Suppression des objets
						$objet = new LigneInventaire;
					}
				}
				$avatar->delete('id_avatar = ' . $avatar->id_avatar); //Suppression de l'avatar
			}
			$this->_redirect('admin/avatar');
			return;
		}
		$this->view->avatar = $avatar;
	}
	
	function classeAction() {
		$this->view->title = "Administration des classes";
		$classe = new Classe();
		$this->view->classe = $classe->fetchAll();
	}
	
	function objetAction() {
		$this->view->title = "Administration des equipements";
		$equipement = new Equipement();
		$this->view->obj = new Objet;
		$this->view->equipement = $equipement->fetchAll();
	}

	function modifclasseAction() {
		$classe = new Classe();
        $id     = (int)$this->_request->getParam('id');
		$nom    = $classe->getNameById($id);
		$classe = $classe->findByNom($nom);
		$this->view->title  = "Modification de la classe ".$nom;
		$this->view->classe = $classe;
		// Tester si tous les champs sont correctement remplis.
		if ($this->_request->isPost()) {
			$data = array(
				'att_classe'    => $this->_request->getPost('att'),
				'attsp_classe'  => $this->_request->getPost('attspe'),
				'def_classe' 	=> $this->_request->getPost('def'),
				'defsp_classe'	=> $this->_request->getPost('defspe'),
				'vit_classe' 	=> $this->_request->getPost('vit'),
				'hp_classe' 	=> $this->_request->getPost('hp'),
				'mp_classe' 	=> $this->_request->getPost('mp'),
			);
			$classe = new Classe();
			$where = $classe->getAdapter()->quoteInto('id_classe=?', (string) $id);
			$classe->update($data, $where);
			$this->_redirect('admin/classe');
			return;
		}
	}
	
	function monstreAction() {
		$this->view->title = "Gestion des monstres";
		$monstre = new Monstre();
		$this->view->monstre = $monstre->fetchAll();
	}
	
	function modifmonstreAction() {
		$monstre = new Monstre();
		$id = (int)$this->_request->getParam('id');
		$monstre = $monstre->findById($id);
		$this->view->title = "Modification du monstre : ".$monstre->nom_monstre;
		$this->view->monstre = $monstre;
		// Tester si les champs sont correctement remplis
		if ($this->_request->isPost()) {
			$data = array(
				'niv_monstre' =>$this->_request->getPost('niv'),
				'exp_monstre' => $this->_request->getPost('exp'),
				'hp_monstre' => $this->_request->getPost('hp'),
				'mp_monstre' => $this->_request->getPost('mp'),
				'att_monstre' => $this->_request->getPost('att'),
				'attsp_monstre' => $this->_request->getPost('attspe'),
				'def_monstre' => $this->_request->getPost('def'),
				'defsp_monstre' => $this->_request->getPost('defspe'),
				'vit_monstre' => $this->_request->getPost('vit'),
			);
			$monstre = new Monstre();
			$where = $monstre->getAdapter()->quoteInto('id_monstre=?', (string) $id);
			$monstre->update($data, $where);
			$this->_redirect('admin/monstre');
			return;
		}
	}
	
	function supprmonstreAction() {
		$this->view->title = "Suppression d'un monstre";
		$monstre = new Monstre();
		$zoneM = new ZoneMonstre();
		$id = (int)$this->_request->getParam('id');
		$monstre = $monstre->findById($id);
		if($this->_request->isPost()) {
			$del = $this->_request->getPost('del');
			if ($del == 'Oui' && $id > 0) {
				echo "monstre supprimé";
				$zoneM = $zoneM->findByIdMonstre($id);
				foreach($zoneM as $zone) {
					echo $zone->id_zone;
					$zone->delete('id_zone = ' . $zone->id_zone);
				}
				//suppression du monstre
				// TODO suppression des zones, competences, objets
				$monstre->delete('id_monstre = ' . $id);
			}
			$this->_redirect('admin/monstre');
			return;
		}
		$this->view->monstre = $monstre;
	}
	
	function addmonstreAction() {
		$this->view->title = "Creation d'un monstre";
		$mon = array();
		$erreurs = array();
		$monstre = new Monstre();
		$tmp = array();
		$zone = new Zone();
		$zone = $zone->fetchAll();
		$competence = new Competence();
		$compMonstre = new CompetenceMonstre();
		$compMonstre = $compMonstre->fetchAll();
		$objetMonstre = new ObjetMonstre();
		if($this->_request->isPost()) {
			Zend_Loader::loadClass('Zend_Filter_StripTags');
            $filter = new Zend_Filter_StripTags();
			$tmp['nom'] = trim($filter->filter($this->_request->getPost('nom')));
			$tmp['description'] = trim($filter->filter($this->_request->getPost('description')));
			$tmp['exp'] = trim($filter->filter($this->_request->getPost('exp')));
			$tmp['niv'] = trim($filter->filter($this->_request->getPost('niv')));
			$tmp['hp'] = trim($filter->filter($this->_request->getPost('hp')));
			$tmp['mp'] = trim($filter->filter($this->_request->getPost('mp')));
			$tmp['att'] = trim($filter->filter($this->_request->getPost('att')));
			$tmp['attspe'] = trim($filter->filter($this->_request->getPost('attspe')));
			$tmp['def'] = trim($filter->filter($this->_request->getPost('def')));
			$tmp['defspe'] = trim($filter->filter($this->_request->getPost('defspe')));
			$tmp['vit'] = trim($filter->filter($this->_request->getPost('vit')));
			// TODO Lier les zones au monstre
			if(verifInfoMonstre($tmp, $erreurs)) {
				$data = array(
					'nom_monstre' => $tmp['nom'],
					'description_monstre' => $tmp['description'],
					'exp_monstre' => $tmp['exp'],
					'niv_monstre' => $tmp['niv'],
					'hp_monstre' => $tmp['hp'],
					'mp_monstre' => $tmp['mp'],
					'att_monstre' => $tmp['att'],
					'attsp_monstre' => $tmp['attspe'],
					'def_monstre' => $tmp['def'],
					'defsp_monstre' => $tmp['defspe'],
					'vit_monstre' => $tmp['vit'],
				);
				$monstre->insert($data);
				$this->_redirect('admin/monstre');
				return;
			}
		}
		$this->view->zone = $zone;
		$this->view->competence = $competence;
		$this->view->comp = $compMonstre;
		$this->view->erreur = $erreurs;
		$this->view->monstre = $tmp;
	}
	
}

function verifInfoMonstre($tmp, &$erreurs){
	if(!preg_match('`^[[:alnum:] éèêàâùüûoôïî,\'-]{1,20}$`', $tmp['nom']))
		$erreurs['nom'] = '<span class="erreur">Nom incorrect.</span>';
	if(!preg_match('`^[[:alnum:] ,./;!&]{0,500}$`', $tmp['description']))
		$erreurs['description'] = '<span class="erreur">Description trop longue.</span>';
	if(!preg_match('`^[[:digit:]]{1,11}$`', $tmp['exp']))
		$erreurs['exp'] = '<span class="erreur">Experience : Valeur incorrecte.</span>';
	if(!preg_match('`^[[:digit:]]{1,11}$`', $tmp['niv']))
		$erreurs['niv'] = '<span class="erreur">Niveau : Valeur incorrecte.</span>';
	if(!preg_match('`^[[:digit:]]{1,11}$`', $tmp['hp']))
		$erreurs['hp'] = '<span class="erreur">HP : Valeur incorrecte.</span>';
	if(!preg_match('`^[[:digit:]]{1,11}$`', $tmp['mp']))
		$erreurs['mp'] = '<span class="erreur">MP : Valeur incorrecte.</span>';
	if(!preg_match('`^[[:digit:]]{1,11}$`', $tmp['att']))
		$erreurs['att'] = '<span class="erreur">Attaque : Valeur incorrecte.</span>';
	if(!preg_match('`^[[:digit:]]{1,11}$`', $tmp['attspe']))
		$erreurs['attspe'] = '<span class="erreur">Attaque speciale : Valeur incorrecte.</span>';
	if(!preg_match('`^[[:digit:]]{1,11}$`', $tmp['def']))
		$erreurs['def'] = '<span class="erreur">Defense : Valeur incorrecte.</span>';
	if(!preg_match('`^[[:digit:]]{1,11}$`', $tmp['defspe']))
		$erreurs['defspe'] = '<span class="erreur">Defense speciale : Valeur incorrecte.</span>';
	if(!preg_match('`^[[:digit:]]{1,11}$`', $tmp['vit']))
		$erreurs['vit'] = '<span class="erreur">Vitesse : Valeur incorrecte.</span>';

	foreach($erreurs as $err){
		if ($err != "")
			return false;
	}
	return true;

}
