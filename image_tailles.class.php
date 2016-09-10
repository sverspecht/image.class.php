<?PHP

class image_tailles extends image {

	/* Construction de ma référence à mon image */
    public function __construct($image) {
        $this->img =& $image->img;
		$this->width =& $image->width;
        $this->height =& $image->height;
    }

	/*	Recadre une image en spécifiant la hauteur et la largeur finale */
	public function recadrer($w, $h){ 
		$rapport_final = $w/$h;
		$rapport_original = $this->width/$this->height;
	
		$temp = new image($w, $h);
	
		if($rapport_final >= $rapport_original){ 
			$temp_w = $this->width;
			$temp_h = $this->width*($h/$w);
			$x=0; 
			$y= round(($this->height - $temp_h)/2);
		}elseif($rapport_final < $rapport_original){ 
			$temp_w = $this->height*($w/$h) ;
			$temp_h = $this->height;
			$x= round(($this->width - $temp_w)/2);
			$y= 0; 
		}
		imagecopyresampled($temp->img, $this->img, 0, 0, $x, $y, $w, $h, $temp_w, $temp_h);
		$this->copier($temp);
		return $this;
	}

	/*	Redimensionne une image en spécifiant les maximums de hauteur et de largeur */
	public function redimensionner($max_w, $max_h, $option = false){ 
		if($option == 'no_enlarge' && $max_w > $this->width && $max_h > $this->height) return $this;
			
		$ratio = $this->width/$this->height;
		if(!empty($max_w) && !empty($max_h)):
			$max_ratio = $max_w/$max_h;
			if($max_ratio >= $ratio):
				$height = $max_h;
				$width = $max_h * $ratio;
			else:
				$width = $max_w;
				$height = $max_w / $ratio;
			endif;
		elseif($max_w):
			$width = $max_w;
			$height = $max_w /$ratio;
		else:
			$height = $max_h;
			$width = $max_h * $ratio;			
		endif;	
		$temp = new image(intval($width), intval($height));
		$resample = imagecopyresampled ($temp->img, $this->img, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		if($resample === FALSE) return FALSE;
		$this->copier($temp->img, $width, $height);
		return $this;
	}
	
		/*	Agrandit une image en spécifiant les minimas de hauteur et de largeur */
	public function agrandir($min){ 
		if($this->width > $min && $this->height > $min) return TRUE;
		$ratio = $this->width/$this->height;
		
		if($this->width < $this->height){
				$width = $min;
				$height = $min / $ratio;
		}else{
				$height = $min;
				$width = $min * $ratio;
		}
		$temp = new image(intval($width), intval($height));
		$resample = imagecopyresampled ($temp->img, $this->img, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		if($resample === FALSE) return FALSE;
		$this->copier($temp->img, $width, $height);
		return $this;
	}

	
	/*	Réechantillonne une image en spécifiant la hauteur et la largeur finale
		Si une couleur est fournie, elle servira de fond pour conserver le ratio */
	public function reechantillonner($w, $h, $bgcolor = false){ 
		if(is_array($bgcolor)):
			$this->redimensionner($w, $h);
			$temp = new image($w, $h, $bgcolor);
			imagecopy($temp->img, $this->img, ($w-$this->width)/2, ($h-$this->height)/2, 0, 0, $this->width, $this->height);		
		else:
			$temp = new image($w, $h);
			imagecopyresampled ($temp->img, $this->img, 0, 0, 0, 0, $w, $h, $this->width, $this->height);			
		endif;
		$this->copier($temp);
		return $this;
	}

}
?>