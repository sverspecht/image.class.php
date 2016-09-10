<?php
class image {

	public	$img, $width, $height, $table;
	
	/* 	Nouvelle image à partir d'une image existante ou
		En fournissant la largeur, la hauteur et la couleur */
	public function __construct($varx, $vary = false, $col = array(0,0,0)){
		if(is_int($varx) && is_int($vary)){
			$this->width = intval($varx);
			$this->height = intval($vary);
			$this->img = imagecreatetruecolor($this->width, $this->height);
			$bgcolor = imagecolorallocate($this->img, $col[0], $col[1], $col[2]);
		
			imagefilledrectangle($this->img, 0, 0, $this->width, $this->height, $bgcolor);
		}else{
			if(empty($vary)){
				$type = strtolower(substr(strrchr($varx, '.'), 1));
				$my_fct = 'imagecreatefrom'.str_replace('jpg', 'jpeg', $type);
			}else{
		    	$my_fct = 'imagecreatefrom'.$vary;	
			}
			if(!function_exists($my_fct)) return false;
			$this->img = $my_fct($varx);
			$this->width = imagesx($this->img);
			$this->height = imagesy($this->img);
		}
	}
	
	/*	Gère l'affichage de l'image au format désiré */
	public function afficher($type = false){
		$type = function_exists('image'.$type)?$type:'png';
		header('Content-type: image/'.$type);
		$my_fct = 'image'.$type;
		$my_fct($this->img);
	}
	
	/* Enregistrer l'image au format désiré en qualité désirée */
	public function enregistrer($url, $qualite = 100){
		$type = strtolower(substr(strrchr($url, '.'), 1));
		$my_fct = function_exists('image'.$type)?'image'.$type:'imagepng';
		if($my_fct == 'imagepng'):
			return imagepng($this->img, $url, 9-floor(9*$qualite/100));
		elseif($my_fct == 'imagejpeg' || $my_fct == 'imagejpg'):
			return imagejpeg($this->img, $url, $qualite);
		else: 
			return $my_fct($this->img, $url);
		endif;
	}
	
	/*	Effectue une rotation de l'image */
	public function math_rotation($degre = 90, $color = 0, $more = 0){ 
		if($temp = imagerotate($this->img, $degre, $color, $more)){
			$this->copier($temp, imagesx($temp), imagesy($temp));	
			return TRUE; }
		return FALSE;
	}	
	
	/*	Effectue symétrie d'axe (Haut/Bas) X ou Y (Gauche/Droite) */
	public function math_symetrie($axe = 'X'){ 
		$temp = new image($this->width, $this->height);
		$dimension = (strtoupper($axe) == 'Y')?'width':'height';
		
		for($i=0; $i < $this->$dimension; $i++):
			if($dimension == 'width'):
				imagecopy($temp->img, $this->img, $this->width-1-$i, 0, $i, 0, 1, $this->height);
			else:
				imagecopy($temp->img, $this->img, 0, $this->height-1-$i, 0, $i, $this->width, 1);
			endif;
		endfor;
		$this->copier($temp);
	}	
	
	public function selectionner($xa, $ya, $xb, $yb, $return = false){
		$temp = new image($xb-$xa, $yb-$ya);
		imagecopy($temp->img, $this->img, 0, 0, $xa, $ya, $temp->width, $temp->height);
		if($return) return $temp;
		$this->copier($temp);
	}
	
	public function somme($xa, $ya, $xb, $yb){
		$level = 15;
		$copie = new image($xb-$xa, $yb-$ya);
		$copie->copier($this);
		$copie->selectionner($xa, $ya, $xb, $yb);
		$color = 0;
		for($j=0;$j < $copie->height; $j++){
			for($i=0;$i < $copie->width; $i++){ //On passe en revue tous les pixels de la ligne
				$temp = $copie->get_color_at($i, $j);
				$color += (abs($temp[0]-$temp[1])>$level && abs($temp[0]-$temp[2]) >$level)?1:0; //rouge
			}
		}
		return $color/($copie->width*$copie->height);
	
	}
	
	public function is_recto($level = 0.08){
		if($this->somme(0,0,intval($this->height*0.1),intval($this->height*0.1))>$level) return TRUE;
		else return FALSE;
	}

	/*	Récupère la couleur (R,G,B) du pixel indiqué */
    public function get_color_at($x, $y){ //on spécifie l'image considérée, et la postion en x et en y du pixel
        $rgb = ImageColorAt($this->img, $x, $y); //on récupére l'information voulue (mais en binaire)
        
        $r = ($rgb >> 16) & 0xFF; //On décale la couleur de 16 bits pour obtenir la valeur du ROUGE
        $g = ($rgb >> 8)  & 0xFF; //On décale la couleur de 8 bits pour obtenir la valeur du VERT
        $b = $rgb & 0xFF; //On récupère simplement la valeur du BLEU
        
        return array($r,$g,$b); //On renvoie la couleur su pixel sous forme complète hexadécimale
    }
	/* 	Retourne le tableau de pixels de l'image */
	public function get_table($a = 1, $b = 1){
		for($j=0;$j < $this->height; $j++){
			for($i=0;$i < $this->width; $i++){ //On passe en revue tous les pixels de la ligne
				$temp = $this->get_color_at($i, $j);
				$x = round($i/$a);
				$y = round($j/$b);
				for($z=0; $z < 3; $z++) $out[$y][$x][$z] += $temp[$z];
				$out[$y][$x]['nb']++;
			}
		}
		return $out;
	}


	/*	Pixelise l'image en pixels aux dimensions indiquées */
	public function pixelise($a, $b){
		$out = $this->get_table($a, $b);
		$destination = new image($this->width, $this->height);

		for($y=0; $y < count($out); $y++){
			for($x=0; $x < count($out[$y]); $x++){
				$color = imagecolorallocate($destination->img, $out[$y][$x][0]/$out[$y][$x]['nb'],$out[$y][$x][1]/$out[$y][$x]['nb'],$out[$y][$x][2]/$out[$y][$x]['nb']);
				imagefilledrectangle($destination->img,$x*$a, $y*$b, ($x+1)*$a, ($y+1)*$b, $color);
			}
		}
		$this->copier($destination);
	}
	

	/* Charge une sous-class de fonctionnalités */
	public function load($fonctionnalite) {
	    // syntaxe de la fonctinnalite
	    $fonctionnalite = strtolower($fonctionnalite);
	    // syntaxe de la classe
	    $className = 'image_' . $fonctionnalite;
	    // on va chercher le fichier qui va bien
	    require_once $className . '.class.php';
	    // On lie les méthodes de Image_Fonctionnalite et l'objet courant
	    $this->$fonctionnalite = new $className($this);
		return $this->$fonctionnalite;
	}

	
	/*	Importe des données d'un objet dans l'actuel */	
	public function copier($temp, $width = false, $height = false){
	if($width == false && $height == false):
		$this->img 		= $temp->img;
		$this->width 	= $temp->width;
		$this->height 	= $temp->height;
	else:
		$this->img 		= $temp;
		$this->width 	= $width;
		$this->height 	= $height;		
	endif;
	}
	
	/*	Vide la ressource mémoire d'une image */
	public function vider(){
		imageDestroy($this->img);
	}
}
?>