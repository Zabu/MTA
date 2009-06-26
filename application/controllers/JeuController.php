<?php

class JeuController extends Zend_Controller_Action
{
	function init(){
		$this->view->baseUrl = $this->_request->getBaseUrl();
		Zend_Loader::loadClass('Utilisateur');
		Zend_Loader::loadClass('Avatar');
		Zend_Loader::loadClass('Classe');
		Zend_Loader::loadClass('Case_');
		Zend_Loader::loadClass('Competence');
		Zend_Loader::loadClass('Zone');
		Zend_Loader::loadClass('Equipement');
		Zend_Loader::loadClass('EquipementArme');
		Zend_Loader::loadClass('EquipementArmure');
		Zend_Loader::loadClass('Objet');
		Zend_Loader::loadClass('ObjetMonstre');
		Zend_Loader::loadClass('ZoneMonstre');
		Zend_Loader::loadClass('Monstre');
		Zend_Loader::loadClass('LigneInventaire');
		Zend_Loader::loadClass('ObjetSoin');
		Zend_Loader::loadClass('Niveau');
		Zend_Loader::loadClass('Pnj');
		Zend_Loader::loadClass('Instructeur');
		Zend_Loader::loadClass('Marchand');
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
	
	function navigationAction(){
		$this->view->title = "Magic The Awakening";
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$id = (int)$this->_request->getParam('id', 0);
		$id_user = $this->user->id_utilisateur;
		if(avatarViolation($id, $id_user) || $id < 1){
			$this->_redirect('avatar/avatar');
			return;
		}
		$appMonstre = new Zend_Session_Namespace('appMonstre');
		$appMonstre->id_monstre = NULL;
		$case   = new Case_();
		$classe = new Classe();
		$avatar = new Avatar();
		$avatar  = $avatar->findById($id);
		
		if($avatar->hp_avatar <= 0){
			$navigation = false;
			$resurrection = true;
		}
		else{
			$navigation = true;
			$resurrection = false;
		}
		
		// Pour afficher la zone
		$zone = new Zone();
		
		//  Pour afficher l'inventaire dans le menu
		$arme_   = new Objet();
		$armure_ = new Objet();
		
		$ligne   = $case->getLigne($avatar->id_case);
		$colonne = $case->getColonne($avatar->id_case);
		$classe  = $classe->getNameById($avatar->id_classe);
		$arme_   = $arme_->findById($avatar->id_arme);
		$armure_ = $armure_->findById($avatar->id_armure);
		$case = $case->findById($avatar->id_case);
		//zone
		$zone = $zone->findById($case->id_zone);
		$zonemonstre = new ZoneMonstre();
		$zonemonstre = $zonemonstre->findByIdZone($zone->id_zone);
		$chasse = false;
		
		if(count($zonemonstre) > 0){
			$chasse = true;
			if($appMonstre->apparition == true){
				$appMonstre->apparition = false;
				//Gestion d'apparition des monstres
				$monstre = new Monstre();
				$nbaleatoire = rand(1,count($zonemonstre));
				$compteur = 1;
				$taux = 0;
				foreach($zonemonstre as $zm){
					if($nbaleatoire == $compteur){
						$monstre = $monstre->findById($zm->id_monstre);
						$taux = $zm->taux_apparition; //forcer apparition
						break;
					}
					$compteur++;
				}
				$nbaleatoire = rand(1,100);
				if($nbaleatoire < $taux){
					$appMonstre->id_monstre = $monstre->id_monstre;
					$this->_redirect('jeu/combat/id/'.$id);
					return;
				}
			}
		}
		
		$healing = new Ville();
		$healing = $healing->findByCase($case->id_case);
		
		$marchands = new Pnj();
		$marchands = $marchands->findMarchandByCase($avatar->id_case);
		$instructeurs = new Pnj();
		$instructeurs = $instructeurs->findInstructeurByCase($avatar->id_case);
		
		$ville = new Ville();
		$ville = $ville->findByCase($avatar->id_case);
		//gérer avec findByCase, findMarchandByCase, findInstructeurByCase
		$nav = new Zend_Session_Namespace('nav');
		if($marchands){
			$this->view->title = "Bienvenue à ".$ville->nom_ville;
			$nav->id_marchand = $marchands->id_pnj;}
		else{
			$this->view->title = "Magic The Awakening";
			$nav->id_marchand = false;}
		if($instructeurs)
			$nav->id_instructeur = $instructeurs->id_pnj;
		else
			$nav->id_instructeur = false;
		
		// Envoi vers la vue du jeu
		$this->view->healing = $healing;
		$this->view->marchand = $nav->id_marchand;
		$this->view->instructeur = $nav->id_instructeur;
		$this->view->chasse = $chasse;
		$this->view->resurrection = $resurrection;
		$this->view->ligne = $ligne;
		$this->view->navigation = $navigation;
		$this->view->colonne = $colonne;
		$this->view->avatar = $avatar;
		$this->view->classe = $classe;
		$this->view->arme_ = $arme_;
		$this->view->armure_ = $armure_;
		$this->view->zone = $zone;
	}
	
	function soinsAction(){
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$id = (int)$this->_request->getParam('id', 0);
		$id_user = $this->user->id_utilisateur;
		if(avatarViolation($id, $id_user) || $id < 1){
			$this->_redirect('avatar/avatar');
			return;
		}
		$avatar = new Avatar();
		$avatar = $avatar->findById($id);
		$data = array(
			'hp_avatar' => $avatar->hpmax_avatar,
			'mp_avatar' => $avatar->mpmax_avatar,
		);
		$where = 'id_avatar = '.$id;
		$avatar = new Avatar();
		$avatar->update($data,$where);
		$this->_redirect('jeu/navigation/id/'.$id);
	}
	
	function achatinstructeurAction(){
		$this->view->title = "Magic The Awakening";
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$id = (int)$this->_request->getParam('id', 0);
		$id_user = $this->user->id_utilisateur;
		$id_instructeur = (int)$this->_request->getParam('id_instructeur', 0);
		$navigation = true;
		if(avatarViolation($id, $id_user) || $id < 1){
			$this->_redirect('avatar/avatar');
			return;
		}
		$appMonstre = new Zend_Session_Namespace('appMonstre');
		$appMonstre->id_monstre = NULL;
		$case   = new Case_();
		$classe = new Classe();
		$avatar = new Avatar();
		$avatar  = $avatar->findById($id);
		// Pour afficher la zone
		$zone = new Zone();
		
		//  Pour afficher l'inventaire dans le menu
		$arme_   = new Objet();
		$armure_ = new Objet();
		
		$ligne   = $case->getLigne($avatar->id_case);
		$colonne = $case->getColonne($avatar->id_case);
		$classe  = $classe->getNameById($avatar->id_classe);
		$arme_   = $arme_->findById($avatar->id_arme);
		$armure_ = $armure_->findById($avatar->id_armure);
		$case = $case->findById($avatar->id_case);
		//zone
		$zone = $zone->findById($case->id_zone);
		
		//gestion de l'achat
		$pnj_instructeur = new Pnj();
		$pnj_instructeur = $pnj_instructeur->findById($id_instructeur);
		$competence = new Competence;
		$competence = $competence->getByClasse($avatar->id_classe);
		//$objet_achat = new Objet();
		//$objet_achat = $objet_achat->getByMarchand($id_marchand);
		
		$equip = new Equipement();
		
		// Envoi vers la vue du jeu
		$this->view->equip = $equip;
		$this->view->competence = $competence;
		$this->view->instructeur = $pnj_instructeur;
		//$this->view->objets = $objet_achat;
		$this->view->ligne = $ligne;
		$this->view->navigation = $navigation;
		$this->view->colonne = $colonne;
		$this->view->avatar = $avatar;
		$this->view->classe = $classe;
		$this->view->arme_ = $arme_;
		$this->view->armure_ = $armure_;
		$this->view->zone = $zone;
	}
	
	function achatAction(){
		$this->view->title = "Magic The Awakening";
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$id = (int)$this->_request->getParam('id', 0);
		$id_user = $this->user->id_utilisateur;
		$id_marchand = (int)$this->_request->getParam('id_marchand', 0);
		if(avatarViolation($id, $id_user) || $id < 1){
			$this->_redirect('avatar/avatar');
			return;
		}
		$appMonstre = new Zend_Session_Namespace('appMonstre');
		$appMonstre->id_monstre = NULL;
		$case   = new Case_();
		$classe = new Classe();
		$avatar = new Avatar();
		$avatar  = $avatar->findById($id);
		// Pour afficher la zone
		$zone = new Zone();
		
		//  Pour afficher l'inventaire dans le menu
		$arme_   = new Objet();
		$armure_ = new Objet();
		
		$ligne   = $case->getLigne($avatar->id_case);
		$colonne = $case->getColonne($avatar->id_case);
		$classe  = $classe->getNameById($avatar->id_classe);
		$arme_   = $arme_->findById($avatar->id_arme);
		$armure_ = $armure_->findById($avatar->id_armure);
		$case = $case->findById($avatar->id_case);
		//zone
		$zone = $zone->findById($case->id_zone);
		
		//gestion de l'achat
		$pnj_marchand = new Pnj();
		$pnj_marchand = $pnj_marchand->findById($id_marchand);
		$objet_achat = new Objet();
		$objet_achat = $objet_achat->getByMarchand($id_marchand);
		
		$equip = new Equipement();
		
		// Envoi vers la vue du jeu
		$this->view->equip = $equip;
		$this->view->marchand = $pnj_marchand;
		$this->view->objets = $objet_achat;
		$this->view->ligne = $ligne;
		$this->view->navigation = $navigation;
		$this->view->colonne = $colonne;
		$this->view->avatar = $avatar;
		$this->view->classe = $classe;
		$this->view->arme_ = $arme_;
		$this->view->armure_ = $armure_;
		$this->view->zone = $zone;
	}
	
	function chasseAction(){
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$id = (int)$this->_request->getParam('id', 0);
		$id_user = $this->user->id_utilisateur;
		if(avatarViolation($id, $id_user) || $id < 1){
			$this->_redirect('avatar/avatar');
			return;
		}
		$appMonstre = new Zend_Session_Namespace('appMonstre');
		$avatar = new Avatar();
		$avatar  = $avatar->findById($id);
		$case   = new Case_();
		$zone = new Zone();
		$case = $case->findById($avatar->id_case);
		$zone = $zone->findById($case->id_zone);
		$zonemonstre = new ZoneMonstre();
		$zonemonstre = $zonemonstre->findByIdZone($zone->id_zone);
		//Gestion d'apparition des monstres
		$monstre = new Monstre();
		$nbaleatoire = rand(1,count($zonemonstre));
		$compteur = 1;
		foreach($zonemonstre as $zm){
			if($nbaleatoire == $compteur){
				$monstre = $monstre->findById($zm->id_monstre);
				break;
			}
			$compteur++;
		}
		$appMonstre->id_monstre = $monstre->id_monstre;
		$this->_redirect('jeu/combat/id/'.$id);
		return;
	}
	
	function combatAction() {
		$this->view->title = "Combat";
		$id = (int)$this->_request->getParam('id', 0);		
		// Pour afficher la zone
		$zone = new Zone();
		$monstre = new Monstre();
		$avatar = new Avatar();
		$classe = new Classe();
		$case   = new Case_();
		//  Pour afficher l'inventaire dans le menu
		$arme_   = new Objet();
		$armure_ = new Objet();
		$avatar  = $avatar->findById($id);
		$ligne   = $case->getLigne($avatar->id_case);
		$colonne = $case->getColonne($avatar->id_case);
		$classe  = $classe->getNameById($avatar->id_classe);
		$arme_   = $arme_->findById($avatar->id_arme);
		$armure_ = $armure_->findById($avatar->id_armure);
		$case = $case->findById($avatar->id_case);
		$appMonstre = new Zend_Session_Namespace('appMonstre');
		$fin_combat = false;
		//zone
		$zone = $zone->findById($case->id_zone);
		if(!isset($appMonstre->round)){
			$appMonstre->round = 0;
			$id_monstre = $appMonstre->id_monstre;
			echo $id_monstre;
			$monstre = $monstre->findById($id_monstre);
			$appMonstre->hp_monstre = $monstre->hp_monstre;
			$appMonstre->mp_monstre = $monstre->mp_monstre;
			$appMonstre->att_monstre = $monstre->att_monstre;
			$appMonstre->attsp_monstre = $monstre->attsp_monstre;
			$appMonstre->def_monstre = $monstre->def_monstre;
			$appMonstre->defsp_monstre = $monstre->defsp_monstre;
			$appMonstre->vit_monstre = $monstre->vit_monstre;
		}
		else
			$monstre = $monstre->findById($appMonstre->id_monstre);
			
		if($appMonstre->hp_monstre == 0){
			$message_victoire = "Vous avez vaincu ". $monstre->nom_monstre;
			$fin_combat = true;
			$appMonstre->round = NULL;
			$this->view->fin_message = $message_victoire;
		}
		if($avatar->hp_avatar == 0){
			$message_defaite = "Vous avez été vaincu par ". $monstre->nom_monstre;
			$fin_combat = true;
			$appMonstre->round = NULL;
			$this->view->fin_message = $message_defaite;
		}
		
		if($fin_combat && $avatar->hp_avatar > 0){
			$dropsmonstre = new ObjetMonstre();
			$dropsmonstre = $dropsmonstre->getByMonstre($appMonstre->id_monstre);
			$cpt= 0;
			$drops = array();
			foreach($dropsmonstre as $drop){
				$nbaleatoire = rand(0,100);
				if($nbaleatoire < $drop->taux_drop){
					$item = new LigneInventaire();
					if($item = $item->findItem($avatar->id_avatar,$drop->id_objet)){
						$data = array(
							'quantite_ligne' => $item->quantite_ligne + 1,
						);
						$where = " id_avatar = " . $avatar->id_avatar . " AND id_objet = ". $drop->id_objet;
						$item = new LigneInventaire();
						$item->update($data,$where);
					}
					else{
						$data = array(
							'id_avatar' => $avatar->id_avatar,
							'id_objet' => $drop->id_objet,
							'quantite_ligne' => 1,
						);
						$item = new LigneInventaire();
						$item->insert($data);
					}
					$objet = new Objet;
					$drops[$cpt] =  $objet->findById($drop->id_objet)->nom_objet;
					$cpt++;
				}
			}
			$this->view->dropsmonstre = $drops;
			$this->view->cpt = $cpt;
			$avatar_ = new Avatar();
			$exp = $avatar->exp_avatar + $monstre->exp_monstre;
			$data = array(
				'exp_avatar' => $exp,
			);
			$where = ' id_avatar = ' .$id;
			$avatar_->update($data, $where);
			lvlUp($id);
			$this->view->exp = $exp;
		}
		//gestion de la fuite
		$fuite = false;
		$this->view->fuite = "";
		if(isset($appMonstre->fuite)){
			if($appMonstre->fuite == true){
				$this->view->message_fuite = "Fuite réussie!";
				$fuite = true;
				$appMonstre->round = NULL;
			}
			else{
				$this->view->message_fuite = "Fuite échouée!";
			}
			$appMonstre->fuite = NULL;
		}
		$this->view->fuite = $fuite;
		$this->view->fin_combat= $fin_combat;
		$this->view->ligne = $ligne;
		$this->view->colonne = $colonne;
		$this->view->avatar  = $avatar;
		$this->view->classe  = $classe;
		$this->view->monstre = $monstre;
		$this->view->monstre_ = $appMonstre;
		$this->view->arme_ = $arme_;
		$this->view->armure_ = $armure_;
		$this->view->zone = $zone;
	}
	
	function attaquerAction(){
		$appMonstre = new Zend_Session_Namespace('appMonstre');
		$id = (int)$this->_request->getParam('id', 0);
		$avatar = new Avatar();
		$avatar = $avatar->findById($id);
		$act = choisirActionMonstre($appMonstre->id_monstre,$id);
		$coefficient_avatar = (rand(100,200))/100;
		$coefficient_monstre = rand(100,200);
		
		if($act){
			$competence = new CompetenceAttaqueSp();
			$competence = $competence->getCompAttCoef($act);
			$mp_comp = new Competence();
			$mp_comp = $mp_comp->findById($act)->mp_competence;
			$coefficient_monstre += $competence;
		}
		$coefficient_monstre /= 100;
		
		if(prioriteVts($appMonstre->vit_monstre, $avatar->vit_avatar)){
			$degat_avatar = (int)($coefficient_avatar * ($avatar->att_avatar + getbAtt($avatar->id_avatar))) - $appMonstre->def_monstre;
			if($degat_avatar < 0)
				$degat_avatar = 0;
			if($degat_avatar >= $appMonstre->hp_monstre){
				$appMonstre->hp_monstre = 0;
			}
			else{
				if($act){
					$att_monstre = $appMonstre->attsp_monstre;
					$def_avatar = $avatar->defsp_avatar;
				}
				else {
					$att_monstre = $appMonstre->att_monstre;
					$def_avatar = $avatar->def_avatar;
				}
				$appMonstre->hp_monstre -= $degat_avatar;
				$degat_monstre = (int)($coefficient_monstre * $att_monstre) - $def_avatar;
				if($degat_monstre < 0)
					$degat_monstre = 0;
				if($degat_monstre >= $avatar->hp_avatar)
					$avatar->hp_avatar = 0;
				else
					$avatar->hp_avatar -= $degat_monstre;
			}
		}
		else{
			if($act){
				$att_monstre = $appMonstre->attsp_monstre;
				$def_avatar = $avatar->defsp_avatar;
			}
			else {
				$att_monstre = $appMonstre->att_monstre;
				$def_avatar = $avatar->def_avatar;
			}
			$appMonstre->hp_monstre -= $degat_avatar;
			$degat_monstre = (int)($coefficient_monstre * $att_monstre) - $def_avatar;
			if($degat_monstre < 0)
					$degat_monstre = 0;
			if($degat_monstre >= $avatar->hp_avatar)
				$avatar->hp_avatar = 0;
			else{
				$avatar->hp_avatar -= $degat_monstre;
				$degat_avatar = (int)($coefficient_avatar * ($avatar->att_avatar + getbAtt($avatar->id_avatar))) - $appMonstre->def_monstre;
				if($degat_avatar < 0)
					$degat_avatar = 0;
				if($degat_avatar >= $appMonstre->hp_monstre)
					$appMonstre->hp_monstre = 0;
				else
					$appMonstre->hp_monstre -= $degat_avatar;
			}
		}
		if(!isset($degat_avatar))
			$appMonstre->degat_avatar = 0;
		else
			$appMonstre->degat_avatar = $degat_avatar;
		if(!isset($degat_monstre))
			$appMonstre->degat_monstre = 0;
		else
			$appMonstre->degat_monstre = $degat_monstre;
		if($act)
			$appMonstre->mp_monstre -= $mp_comp;
		$data = array (
			'hp_avatar' => $avatar->hp_avatar,
		);
		echo $appMonstre->degat_avatar." <br />";
		echo $appMonstre->degat_monstre."<br />";
		echo $appMonstre->hp_monstre."<br />";
		$where = 'id_avatar = ' .$avatar->id_avatar;
		$avatar = new Avatar();
		$avatar->update($data,$where);
		$appMonstre->round++;
		$this->_redirect('jeu/combat/id/'. $id);
	}
	
	function defendreAction(){
		$appMonstre = new Zend_Session_Namespace('appMonstre');
		$id = (int)$this->_request->getParam('id', 0);
		$avatar = new Avatar();
		$avatar = $avatar->findById($id);
		$act = choisirActionMonstre($appMonstre->id_monstre,$id);
		$coefficient_avatar = (rand(150,200))/100;
		$coefficient_monstre = rand(100,200);
		
		if($act){
			$competence = new CompetenceAttaqueSp();
			$competence = $competence->getCompAttCoef($act);
			$mp_comp = new Competence();
			$mp_comp = $mp_comp->findById($act)->mp_competence;
			$coefficient_monstre += $competence;
		}
		$coefficient_monstre /= 100;
		
		$degat_avatar = 0;
		if($act){
			$att_monstre = $appMonstre->attsp_monstre;
			$def_avatar = (int)($avatar->defsp_avatar * $coefficient_avatar);
		}
		else {
			$att_monstre = $appMonstre->att_monstre;
			$def_avatar = (int)($avatar->def_avatar * $coefficient_avatar);
		}
		
		$degat_monstre = (int)($coefficient_monstre * $att_monstre) - $def_avatar;
		if($degat_monstre < 0)
			$degat_monstre = 0;
		if($degat_monstre >= $avatar->hp_avatar)
			$avatar->hp_avatar = 0;
		else
			$avatar->hp_avatar -= $degat_monstre;
		if(!isset($degat_avatar))
			$appMonstre->degat_avatar = 0;
		else
			$appMonstre->degat_avatar = $degat_avatar;
		if(!isset($degat_monstre))
			$appMonstre->degat_monstre = 0;
		else
			$appMonstre->degat_monstre = $degat_monstre;
		if($act)
			$appMonstre->mp_monstre -= $mp_comp;
		$data = array (
			'hp_avatar' => $avatar->hp_avatar,
		);
		$where = 'id_avatar = ' .$avatar->id_avatar;
		$avatar = new Avatar();
		$avatar->update($data,$where);
		$appMonstre->round++;
		$this->_redirect('jeu/combat/id/'. $id);
	}
	
	function fuirAction(){
		$appMonstre = new Zend_Session_Namespace('appMonstre');
		$id = (int)$this->_request->getParam('id', 0);
		$avatar = new Avatar();
		$avatar = $avatar->findById($id);
		$act = choisirActionMonstre($appMonstre->id_monstre,$id);
		$coefficient_fuite = (int)($avatar->vit_avatar / $appMonstre->vit_monstre) * 50;
		$coefficient_monstre = rand(100,200);
		$nbaleatoire = rand(0,100);
		
		if($act){
			$competence = new CompetenceAttaqueSp();
			$competence = $competence->getCompAttCoef($act);
			$mp_comp = new Competence();
			$mp_comp = $mp_comp->findById($act)->mp_competence;
			$coefficient_monstre += $competence;
		}
		$coefficient_monstre /= 100;
		$degat_avatar = 0;
		if(prioriteVts($appMonstre->vit_monstre, $avatar->vit_avatar)){
			if($nbaleatoire < $coefficient_fuite){
				$degat_monstre = 0;
				$fuite_reussie = true;
			}
			else{
				$fuite_reussie = false;
				if($act){
					$att_monstre = $appMonstre->attsp_monstre;
					$def_avatar = $avatar->defsp_avatar;
				}
				else {
					$att_monstre = $appMonstre->att_monstre;
					$def_avatar = $avatar->def_avatar;
				}
				$degat_monstre = (int)($coefficient_monstre * $att_monstre) - $def_avatar;
				if($degat_monstre < 0)
					$degat_monstre = 0;
				if($degat_monstre >= $avatar->hp_avatar)
					$avatar->hp_avatar = 0;
				else
					$avatar->hp_avatar -= $degat_monstre;
			}
		}
		else{
			if($act){
				$att_monstre = $appMonstre->attsp_monstre;
				$def_avatar = $avatar->defsp_avatar;
			}
			else {
				$att_monstre = $appMonstre->att_monstre;
				$def_avatar = $avatar->def_avatar;
			}
			$degat_monstre = (int)($coefficient_monstre * $att_monstre) - $def_avatar;
			if($degat_monstre < 0)
					$degat_monstre = 0;
			if($degat_monstre >= $avatar->hp_avatar){
				$avatar->hp_avatar = 0;
				$fuite_reussie = false;
			}
			else{
				$avatar->hp_avatar -= $degat_monstre;
				if($nbaleatoire < $coefficient_fuite){
					$fuite_reussie = true;
				}
				else{
					$fuite_reussie = false;
				}
			}
		}
		if(!isset($degat_avatar))
			$appMonstre->degat_avatar = 0;
		else
			$appMonstre->degat_avatar = $degat_avatar;
		if(!isset($degat_monstre))
			$appMonstre->degat_monstre = 0;
		else
			$appMonstre->degat_monstre = $degat_monstre;
		if($act)
			$appMonstre->mp_monstre -= $mp_comp;
		$data = array (
			'hp_avatar' => $avatar->hp_avatar,
		);
		$where = 'id_avatar = ' .$avatar->id_avatar;
		$avatar = new Avatar();
		$avatar->update($data,$where);
		$appMonstre->fuite = $fuite_reussie;
		$appMonstre->round++;
		$this->_redirect('jeu/combat/id/'. $id);
	}
	
	function inventaireAction(){
		$this->view->title = "Magic The Awakening";
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$id = (int)$this->_request->getParam('id', 0);
		$id_user = $this->user->id_utilisateur;
		$navigation = false;
		if(avatarViolation($id, $id_user) || $id < 1){
			$this->_redirect('avatar/avatar');
			return;
		}
		$case   = new Case_();
		$classe = new Classe();
		$avatar = new Avatar();
		
		// Pour afficher la zone
		$zone = new Zone();
		
		// Pour afficher l'inventaire dans le menu
		$arme_   = new Objet();
		$armure_ = new Objet();
		
		$avatar  = $avatar->findById($id);
		$ligne   = $case->getLigne($avatar->id_case);
		$colonne = $case->getColonne($avatar->id_case);
		$classe  = $classe->getNameById($avatar->id_classe);
		$arme_   = $arme_->findById($avatar->id_arme);
		$armure_ = $armure_->findById($avatar->id_armure);
		$case = $case->findById($avatar->id_case);
		
		//zone
		$zone = $zone->findById($case->id_zone);
		
		//Gestionnaire d'inventaire
		$inventaire = new LigneInventaire();
		$inventaire = $inventaire->findByIdAvatar($avatar->id_avatar);
		$objet = new Objet();
		$equip = new Equipement();
		$armes = new LigneInventaire();
		$armes = $armes->getArmes($avatar->id_avatar);
		$armures = new LigneInventaire();
		$armures = $armures->getArmures($avatar->id_avatar);
		$soins = new LigneInventaire();
		$soins = $soins->getSoins($avatar->id_avatar);
		$objetSoin = new ObjetSoin();
		
		// Envoi vers la vue du jeu
		$this->view->objetSoin = $objetSoin;
		$this->view->soins = $soins;
		$this->view->armes = $armes;
		$this->view->armures = $armures;
		$this->view->objet = $objet;
		$this->view->equip = $equip;
		$this->view->ligne = $ligne;
		$this->view->navigation = $navigation;
		$this->view->colonne = $colonne;
		$this->view->avatar = $avatar;
		$this->view->classe = $classe;
		$this->view->arme_ = $arme_;
		$this->view->armure_ = $armure_;
		$this->view->zone = $zone;
	}
	
	function equiparmeAction(){
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$id_user = $this->user->id_utilisateur;
		$id = (int)$this->_request->getParam('id', 0);
		if(avatarViolation($id, $id_user) || $id < 1){
			$this->_redirect('avatar/avatar');
			return;
		}
		$idarme = (int)$this->_request->getParam('arme', 0);
		$avatar = new Avatar();
		$avatar = $avatar->findById($id);
		$invent = new LigneInventaire();
		if(!($invent = $invent->findItem($avatar->id_avatar,$idarme))){
			$this->_redirect('avatar/avatar');
			return;
		}
		$data = array(
			'id_arme' => $idarme,
		);
		$where = 'id_avatar = ' . $id;
		$avatar = new Avatar();
		$avatar->update($data,$where);
		$this->_redirect('jeu/inventaire/id/'. $id);
	}
	
	function unequiparmeAction(){
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$id_user = $this->user->id_utilisateur;
		$id = (int)$this->_request->getParam('id', 0);
		if(avatarViolation($id, $id_user) || $id < 1){
			$this->_redirect('avatar/avatar');
			return;
		}
		$avatar = new Avatar();
		$avatar = $avatar->findById($id);
		$data = array(
			'id_arme' => NULL,
		);
		$where = 'id_avatar = ' . $id;
		$avatar = new Avatar();
		$avatar->update($data,$where);
		$this->_redirect('jeu/inventaire/id/'. $id);
	}
	
	function equiparmureAction(){
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$id_user = $this->user->id_utilisateur;
		$id = (int)$this->_request->getParam('id', 0);
		if(avatarViolation($id, $id_user) || $id < 1){
			$this->_redirect('avatar/avatar');
			return;
		}
		$idarmure = (int)$this->_request->getParam('armure', 0);
		$avatar = new Avatar();
		$avatar = $avatar->findById($id);
		$invent = new LigneInventaire();
		if(!($invent = $invent->findItem($avatar->id_avatar,$idarmure))){
			$this->_redirect('avatar/avatar');
			return;
		}
		$data = array(
			'id_armure' => $idarmure,
		);
		$where = 'id_avatar = ' . $id;
		$avatar = new Avatar();
		$avatar->update($data,$where);
		$this->_redirect('jeu/inventaire/id/'. $id);
	}
	
	function unequiparmureAction(){
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$id_user = $this->user->id_utilisateur;
		$id = (int)$this->_request->getParam('id', 0);
		if(avatarViolation($id, $id_user) || $id < 1){
			$this->_redirect('avatar/avatar');
			return;
		}
		$avatar = new Avatar();
		$avatar = $avatar->findById($id);
		$data = array(
			'id_armure' => NULL,
		);
		$where = 'id_avatar = ' . $id;
		$avatar = new Avatar();
		$avatar->update($data,$where);
		$this->_redirect('jeu/inventaire/id/'. $id);
	}
	
	function usehealAction(){
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$id_user = $this->user->id_utilisateur;
		$id = (int)$this->_request->getParam('id', 0);
		if(avatarViolation($id, $id_user) || $id < 1){
			$this->_redirect('avatar/avatar');
			return;
		}
		$idsoin = (int)$this->_request->getParam('soin', 0);
		$avatar = new Avatar();
		$avatar = $avatar->findById($id);
		$invent = new LigneInventaire();
		$invent = $invent->findItem($avatar->id_avatar,$idsoin);
		if(!$invent){
			$this->_redirect('avatar/avatar');
			return;
		}
		$qte = $invent->quantite_ligne;
		//gérer le soin
		$soin = new ObjetSoin();
		if(!($soin = $soin->findById($invent->id_objet))){
			$this->_redirect('avatar/avatar');
			return;
		}
		$current_hp = $avatar->hp_avatar + $soin->hp_soin;
		if($current_hp > $avatar->hpmax_avatar)
			$current_hp = $avatar->hpmax_avatar;
		$current_mp = $avatar->mp_avatar + $soin->mp_soin;
		if($current_mp > $avatar->mpmax_avatar)
			$current_mp = $avatar->mpmax_avatar;
		$adata = array(
			'hp_avatar' => $current_hp,
			'mp_avatar' => $current_mp,
		);
		$where = 'id_avatar = ' . $id;
		$avatar = new Avatar();
		$avatar->update($adata, $where);
		if($qte > 1){
			$data = array(
				'quantite_ligne' => $qte-1,
			);
			$where = 'id_objet = ' . $invent->id_objet . ' AND id_avatar = ' . $id ;
			$invent = new LigneInventaire();
			$invent->update($data, $where);
		}
		else{
			$where = 'id_objet = ' . $invent->id_objet . ' AND id_avatar = ' . $id ;
			$invent = new LigneInventaire();
			$row_affected = $invent->delete($where);
		}
		$this->_redirect('jeu/inventaire/id/'. $id);
	}
	
	function hautAction() {
		$avatar = new Avatar();
		$case_n = new Case_();
		$id = (int)$this->_request->getParam('id', 0);
		$avatar = $avatar->findById($id);
		$ligne_c = $case_n->getLigne($avatar->id_case);
		$colonne_c = $case_n->getColonne($avatar->id_case);
		$ligne_n = $ligne_c - 1;
		$case_n = $case_n->findByCoord($ligne_n, $colonne_c);
		if($case_n){
			if($case_n->id_zone){
				$data = array(
					'id_case' => $case_n->id_case,
				);
				$where = 'id_avatar = ' . $id;
				$avatar = new Avatar();
				$avatar->update($data,$where);
			}
		}
		$appMonstre = new Zend_Session_Namespace('appMonstre');
		$appMonstre->apparition = true;
		$this->_redirect('jeu/navigation/id/'. $id);
	}
	
	function gaucheAction() {
		$avatar = new Avatar();
		$case_n = new Case_();
		$id = (int)$this->_request->getParam('id', 0);
		$avatar = $avatar->findById($id);
		$ligne_c = $case_n->getLigne($avatar->id_case);
		$colonne_c = $case_n->getColonne($avatar->id_case);
		$colonne_n = $colonne_c - 1;
		$case_n = $case_n->findByCoord($ligne_c, $colonne_n);
		if($case_n){
			if($case_n->id_zone){
				$data = array(
					'id_case' => $case_n->id_case,
				);
				$where = 'id_avatar = ' . $id;
				$avatar = new Avatar();
				$avatar->update($data,$where);
			}
		}
		$appMonstre = new Zend_Session_Namespace('appMonstre');
		$appMonstre->apparition = true;
		$this->_redirect('jeu/navigation/id/'. $id);
	}
	function droiteAction() {
		$avatar = new Avatar();
		$case_n = new Case_();
		$id = (int)$this->_request->getParam('id', 0);
		$avatar = $avatar->findById($id);
		$ligne_c = $case_n->getLigne($avatar->id_case);
		$colonne_c = $case_n->getColonne($avatar->id_case);
		$colonne_n = $colonne_c + 1;
		$case_n = $case_n->findByCoord($ligne_c, $colonne_n);
		if($case_n){
			if($case_n->id_zone){
				$data = array(
					'id_case' => $case_n->id_case,
				);
				$where = 'id_avatar = ' . $id;
				$avatar = new Avatar();
				$avatar->update($data,$where);
			}
		}
		$appMonstre = new Zend_Session_Namespace('appMonstre');
		$appMonstre->apparition = true;
		$this->_redirect('jeu/navigation/id/'. $id);
	}
	function basAction() {
		$avatar = new Avatar();
		$case_n = new Case_();
		$id = (int)$this->_request->getParam('id', 0);
		$avatar = $avatar->findById($id);
		$ligne_c = $case_n->getLigne($avatar->id_case);
		$colonne_c = $case_n->getColonne($avatar->id_case);
		$ligne_n = $ligne_c + 1;
		$case_n = $case_n->findByCoord($ligne_n, $colonne_c);
		if($case_n){
			if($case_n->id_zone){
				$data = array(
					'id_case' => $case_n->id_case,
				);
				$where = 'id_avatar = ' . $id;
				$avatar = new Avatar();
				$avatar->update($data,$where);
			}
		}
		$appMonstre = new Zend_Session_Namespace('appMonstre');
		$appMonstre->apparition = true;
		$this->_redirect('jeu/navigation/id/'. $id);
	}
	
	function statsAction(){
		$this->view->title = "Magic The Awakening";
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$id = (int)$this->_request->getParam('id', 0);
		$id_user = $this->user->id_utilisateur;
		$navigation = false;
		if(avatarViolation($id, $id_user) || $id < 1){
			$this->_redirect('avatar/avatar');
			return;
		}
		$case   = new Case_();
		$classe = new Classe();
		$avatar = new Avatar();
		
		// Pour afficher la zone
		$zone = new Zone();
		
		// Pour afficher l'inventaire dans le menu
		$arme_   = new Objet();
		$armure_ = new Objet();
		
		$avatar  = $avatar->findById($id);
		$ligne   = $case->getLigne($avatar->id_case);
		$colonne = $case->getColonne($avatar->id_case);
		$classe  = $classe->getNameById($avatar->id_classe);
		$arme_   = $arme_->findById($avatar->id_arme);
		$armure_ = $armure_->findById($avatar->id_armure);
		$case = $case->findById($avatar->id_case);
		
		//zone
		$zone = $zone->findById($case->id_zone);
		
		//bonus statistiques
		$bonus_att = getbAtt($avatar->id_avatar);
		$bonus_attS = getbAttS($avatar->id_avatar);
		$bonus_def = getbDef($avatar->id_avatar);
		$bonus_defS = getbDefS($avatar->id_avatar);
		$niveau = new Niveau();
		
		//compétences
		
		// Envoi vers la vue du jeu
		$this->view->niveau = $niveau;
		$this->view->bonus_att = $bonus_att;
		$this->view->bonus_attS = $bonus_attS;
		$this->view->bonus_def = $bonus_def;
		$this->view->bonus_defS = $bonus_defS;
		
		$this->view->ligne = $ligne;
		$this->view->navigation = $navigation;
		$this->view->colonne = $colonne;
		$this->view->avatar = $avatar;
		$this->view->classe = $classe;
		$this->view->arme_ = $arme_;
		$this->view->armure_ = $armure_;
		$this->view->zone = $zone;
	}
	
	function resurrectionAction(){
		$this->view->title = "Magic The Awakening";
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$id = (int)$this->_request->getParam('id', 0);
		$id_user = $this->user->id_utilisateur;
		$navigation = false;
		if(avatarViolation($id, $id_user) || $id < 1){
			$this->_redirect('avatar/avatar');
			return;
		}
		$avatar = new Avatar();
		$avatar = $avatar->findById($id);
		if($avatar->hp_avatar == 0){
			$reshp_up = (int)($avatar->hpmax_avatar)/10;
			$resmp_up = (int)($avatar->mpmax_avatar)/10;
		}
		else{
			$reshp_up = $avatar->hp_avatar;
			$resmp_up = $avatar->mp_avatar;
		}
		$data = array(
			'hp_avatar' => $reshp_up,
			'mp_avatar' => $resmp_up,
			'or_avatar' => 0,
		);
		$where = "id_avatar = ".$id;
		$avatar = new Avatar();
		$avatar->update($data,$where);
		$this->_redirect('jeu/navigation/id/'.$id);
	}
}

function avatarViolation($id, $id_utilisateur){
	$avatar = new Avatar();
	$avatar = $avatar->findById($id);
	if ($id_utilisateur != $avatar->id_utilisateur)
		return true;
	return false;
}

function getbAtt($id_avatar){
	$equip = new Equipement();
	$avatar = new Avatar();
	$avatar = $avatar->findById($id_avatar);
	$bonus = 0;
	if(isset($avatar->id_arme))
		$bonus += $equip->getAtt($avatar->id_arme);
	if(isset($avatar->id_armure))
		$bonus += $equip->getAtt($avatar->id_armure);
	return $bonus;
}

function getbAttS($id_avatar){
	$equip = new Equipement();
	$avatar = new Avatar();
	$avatar = $avatar->findById($id_avatar);
	$bonus = 0;
	if(isset($avatar->id_arme))
		$bonus += $equip->getAttS($avatar->id_arme);
	if(isset($avatar->id_armure))
		$bonus += $equip->getAttS($avatar->id_armure);
	return $bonus;
}

function getbDef($id_avatar){
	$equip = new Equipement();
	$avatar = new Avatar();
	$avatar = $avatar->findById($id_avatar);
	$bonus = 0;
	if(isset($avatar->id_arme))
		$bonus += $equip->getDef($avatar->id_arme);
	if(isset($avatar->id_armure))
		$bonus += $equip->getDef($avatar->id_armure);
	return $bonus;
}

function getbDefS($id_avatar){
	$equip = new Equipement();
	$avatar = new Avatar();
	$avatar = $avatar->findById($id_avatar);
	$bonus = 0;
	if(isset($avatar->id_arme))
		$bonus += $equip->getDefS($avatar->id_arme);
	if(isset($avatar->id_armure))
		$bonus += $equip->getDefS($avatar->id_armure);
	return $bonus;
}

function lvlUp($id_avatar){
	$test = true;
	$niveau = new Niveau();
	$avatar = new Avatar();
	$avatar = $avatar->findById($id_avatar);
	$classe = new Classe();
	$classe = $classe->findById($avatar->id_classe);
	$up_hp = $classe->hp_classe;
	$up_mp = $classe->mp_classe;
	$up_att = $classe->att_classe;
	$up_def = $classe->def_classe;
	$up_attS = $classe->attsp_classe;
	$up_defS = $classe->defsp_classe;
	$up_vit = $classe->vit_classe;
	while($test){
		$lvup = $niveau->getNextLvlExp($avatar->id_niveau);
		if($lvup && ($avatar->exp_avatar >= $lvup)){
			$data = array (
				'id_niveau' => $avatar->id_niveau + 1,
				'hpmax_avatar'   => $avatar->hpmax_avatar + $up_hp,
				'mpmax_avatar'   => $avatar->mpmax_avatar + $up_mp,
				'att_avatar'     => $avatar->att_avatar + $up_att,
				'attsp_avatar'  => $avatar->attsp_avatar + $up_attS,
				'def_avatar'     => $avatar->def_avatar + $up_def,
				'defsp_avatar'  => $avatar->defsp_avatar + $up_defS,
				'vit_avatar'     => $avatar->vit_avatar + $up_vit,
			);
			$where = 'id_avatar = ' . $id_avatar;
			$avatar = new Avatar();
			$avatar->update($data, $where);
		}
		else{
			$test = false;
		}
		$avatar = new Avatar();
		$avatar = $avatar->findById($id_avatar);
	}
}

function choisirActionMonstre($id_monstre,$id_avatar){
	//Renvoie 0 pour une attaque ou l'id de la compétence
	$marge_atk = 10;
	$marge_totale = 10;
	$cpsMonstre = new Competence();
	$cpsMonstre = $cpsMonstre->getCompByMonstre($id_monstre);
	if(count($cpsMonstre) > 0){
		$compteur = 1;
		$nbaleatoire = rand(1, count($cpsMonstre));
		foreach($cpsMonstre as $cpm){
			if(nbaleatoire == $compteur){
				$competence = $cpm->id_competence;
				break;
			}
			$compteur++;
		}
		$appMonstre = new Zend_Session_Namespace('appMonstre');
		$mp_monstre = $appMonstre->monstre->mp_monstre;
		$comp = new Competence();
		$comp = $comp->findById($competence);
		if($comp->mp_competence <= $mp_monstre)
			$marge_atk = 8;
	}
	$nbaleatoire = rand(1,$marge_totale);
	if($nbaleatoire <= $marge_atk){
		return 0;
	}
	else{
		return $comp->id_competence;
	}
}

/**
	Calcule la priorité selon la vitesse.
	Return true si le joueur à la priorité
	Return false si le monstre  à la priorité
*/
function prioriteVts($vts_monstre, $vts_avatar){
	if($vts_avatar > $vts_monstre)
		return true;
	return false;
}