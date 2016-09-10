<?PHP

class image_algorithmes extends image {

	/* Construction de ma référence à mon image */
	public function __construct($image) {
		$this->img =& $image->img;
		$this->width =& $image->width;
		$this->height =& $image->height;
	}

	/* Copie un pixel d'une image à une autre */
	public function copypoint($from, $frompxl, $topxl){
		imagecopy($this->img, $from->img, $topxl[0], $topxl[1], $frompxl[0], $frompxl[1], 1, 1);
	}
	
	/*	Applique la transformation du "photomaton" à l'image actuelle avec un nombre de mini-photos réglable */
	public function photomaton($nb = 2, $action = 'do'){
		$nbx = is_array($nb)?$nb[0]:$nb;
		$nby = is_array($nb)?$nb[1]:$nb;
		$width = $this->width;
		$height = $this->height;

		$width -= $width%$nbx;
		$height -= $height%$nby;
		$copy = imagecreatetruecolor($width, $height);

		for($px = $mx = 0; $px < $width; $px += $nbx, $mx++):
			for($py = $my = 0; $py < $height; $py += $nby, $my++):	
				for($tx =0; $tx < $nbx; $tx++):
					for($ty = 0; $ty < $nby; $ty++):
						if($action == 'do')	imagecopy($copy, $this->img, intval($width/$nbx)*$tx + $mx, intval($height/$nby)*$ty + $my, $px+$tx, $py+$ty, 1,1);
						else imagecopy($copy, $this->img, $px+$tx, $py+$ty, intval($width/$nbx)*$tx + $mx, intval($height/$nby)*$ty + $my, 1,1);
					endfor;
				endfor;
			endfor;
		endfor;
		$this->copier($copy, $width, $height);
		return $this;
	}

	/* 	Applique la transformation du boulanger à l'image actuelle avec un nombre de morceaux à couper réglable */
	public function boulanger($n = 2, $action = 'do'){
		$width = $this->width;
		$height = $this->height;

		$height -= $height%$n;
		$copy = imagecreatetruecolor($width, $height);

		for($x=0; $x < $width; $x++):
			for($y=0; $y <$height; $y++):
				$tx = $n*$x + $y%$n;
				$ty = floor($y/$n);
				$nx = (floor($tx/$width)%2 == 0)?$tx%$width:$width-($tx%$width)-1;
				$ny = (floor($tx/$width)%2 == 0)?floor($tx/$width)*$height/$n + $ty: (floor($tx/$width)+1)*$height/$n -$ty-1;
				if($action == 'do')	imagecopy($copy, $this->img, $nx, $ny, $x, $y, 1,1);
				else imagecopy($copy, $this->img, $x, $y, $nx, $ny, 1,1);
			endfor;
		endfor;
		$this->copier($copy, $width, $height);
		return $this;
	}

	/*	Applique la transformation de la "fleur" à l'image actuelle avec un nombre de pétales réglable,
		et un mode 'reverse' pour qui définit la première pétale */
	public function fleur($nb = 2, $action = 'do', $mode = 'standard'){
		$nbx = is_array($nb)?$nb[0]:$nb;
		$nby = is_array($nb)?$nb[1]:$nb;
		$width = $this->width;
		$height = $this->height;

		$width -= $width%$nbx;
		$height -= $height%$nby;
		$copy = imagecreatetruecolor($width, $height);

		for($px = $mx = 0; $px < $width; $px += $nbx, $mx++):
			for($py = $my = 0; $py < $height; $py += $nby, $my++):	
				for($tx =0; $tx < $nbx; $tx++):
					$dx = (($tx%2 && $mode == 'reverse') || ($tx%2 == 0 && $mode != 'reverse')) ? (intval($width/$nbx)*$tx + $mx) : (intval($width/$nbx)*($tx+1) - $mx -1);
					for($ty = 0; $ty < $nby; $ty++):
						$dy = (($ty%2 && $mode == 'reverse') || ($ty%2 == 0 && $mode != 'reverse')) ? (intval($height/$nby)*$ty + $my) : (intval($height/$nby)*($ty+1) - $my -1);
						if($action == 'do')	imagecopy($copy, $this->img, $dx, $dy, $px+$tx, $py+$ty, 1,1);
						else imagecopy($copy, $this->img, $px+$tx, $py+$ty, $dx, $dy, 1,1);
					endfor;
				endfor;
			endfor;
		endfor;
		$this->copier($copy, $width, $height);
		return $this;
	}

	/*	Applique une scission en lignes ou en colonnes entralacées de l'image actuelle avec un nombre de "tour" réglable,*/
	public function lignes($type, $nb = 2, $action = 'do'){
		if($type != 'x'){ $c1 = 'y'; $c2 = 'x'; }
		else{ $c1 = 'x'; $c2 = 'y'; }

		$size['x'] = $this->width;
		$size['y'] = $this->height;

		$copy = imagecreatetruecolor($size['x'] , $size['y']);

		for($x=0; $x < $size['x']; $x++):
			for($y=0; $y < $size['y']; $y++):

				${'n'.$c1} = fmod($nb*${$c1}, $size[$c1]);
				${'n'.$c1} += floor($nb*${$c1}/$size[$c1])*(1 - fmod($size[$c1], $nb));
				${'n'.$c2} = ${$c2};

				if($action == 'do')	imagecopy($copy, $this->img, $nx, $ny, $x, $y, 1,1);
				else imagecopy($copy, $this->img, $x, $y, $nx, $ny, 1,1);
			endfor;
		endfor;
		$this->copier($copy, $size['x'], $size['y']);
		return $this;
	}

	/*	Applique une translation parallèlement à l'axe $type et d'un vecteur $nb*/
	public function translation($type, $nb = 1, $action = 'do'){
		$copy = imagecreatetruecolor($this->width, $this->height);
		if($type == 'x'){ 
			$nb = ($action == 'undo')?abs(fmod($this->width-$nb, $this->width)):fmod($nb, $this->width);
			imagecopy ($copy, $this->img, $nb, 0, 0, 0, $this->width, $this->height);
			imagecopy ($copy, $this->img, $nb-$this->width, 0, 0, 0, $this->width, $this->height);
		}else{
			$nb = ($action == 'undo')?abs(fmod($this->height-$nb, $this->height)):fmod($nb, $this->height);
			imagecopy ($copy, $this->img, 0, $nb, 0, 0, $this->width, $this->height);
			imagecopy ($copy, $this->img, 0, $nb-$this->height, 0, 0, $this->width, $this->height);
		}

		$this->copier($copy, $this->width, $this->height);
		return $this;
	}

}
?>