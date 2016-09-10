<?PHP

class image_distortions extends image {

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

	/* Copie un segment d'une image sur une autre */
	public function copyline($from, $frompxls, $topxls, $precision = 1){
		$fromdx = $frompxls[0] - $frompxls[2];
		$fromdy = $frompxls[1] - $frompxls[3];
		$todx 	= $topxls[0] - $topxls[2];
		$tody 	= $topxls[1] - $topxls[3];

		$fromlg = sqrt(pow($fromdx, 2) + pow($fromdy, 2));
		$tolg 	= sqrt(pow($todx, 2) + pow($tody, 2));

		for($z=0, $max = max($fromlg, $tolg); $z < $max; $z++){
			$pct = -$z/$max;
			$this->copypoint(	$from,  array($frompxls[0]+$pct*$fromdx, $frompxls[1]+$pct*$fromdy),
								array($topxls[0]+$pct*$todx, $topxls[1]+$pct*$tody));
		}
		return $this;
	}
	
	/* Copie un quadrilatère sur un autre */
	public function copyquad($from, $frompxls, $topxls, $precision = 1){
		//Possibilité de charger une image entière
		if($frompxls == "all"){ $frompxls = array(0,0, $from->width,0, $from->width, $from->height, 0, $from->height); }

		$fromdxup = $frompxls[0] - $frompxls[2];
		$fromdyup = $frompxls[1] - $frompxls[3];
		$fromdxdw = $frompxls[6] - $frompxls[4];
		$fromdydw = $frompxls[7] - $frompxls[5];
		$todxup	  = $topxls[0] - $topxls[2];
		$todyup   = $topxls[1] - $topxls[3];		
		$todxdw	  = $topxls[6] - $topxls[4];
		$todydw   = $topxls[7] - $topxls[5];

		$fromlgup = sqrt(pow($fromdxup, 2) + pow($fromdyup, 2));
		$fromlgdw = sqrt(pow($fromdxdw, 2) + pow($fromdydw, 2));
		$tolgup = sqrt(pow($todxup, 2) + pow($todyup, 2));
		$tolgdw = sqrt(pow($todxdw, 2) + pow($todydw, 2));

		for($z=0, $max = $precision*max($fromlgup,$fromlgdw, $tolgup, $tolgdw); $z < $max; $z++){
			$pct = -$z/$max;
			$this->copyline(	$from,  array(	$frompxls[0]+$pct*$fromdxup, $frompxls[1]+$pct*$fromdyup,
									 			$frompxls[6]+$pct*$fromdxdw, $frompxls[7]+$pct*$fromdydw),
										array(  $topxls[0]+$pct*$todxup, $topxls[1]+$pct*$todyup,
								   	 			$topxls[6]+$pct*$todxdw, $topxls[7]+$pct*$todydw), $precision);
		}
		return $this;
	}

	/* Copie une image sur un sphére de centre et de rayon donné */
	public function copysphere($from, $cx, $cy, $proportion = 1){
		
		$ATANSCALE = 2/(pi()*$proportion);
	
		$height = $from->height;
		$width = $from->width;	
	
		for($y=0;$y<$height;$y++) {
			for($x=0;$x<$width;$x++) {
				
				$xk=($x/$width)*2-1;
				$yk=($y/$height)*2-1;
				
				// On vérifie être dans notre sphère
				if ( (pow($yk,2)+pow($xk,2))<1 ){
		 
					// Pythagore pour trouver la coordonné en Z
					$zk = sqrt(1-(pow($yk,2)+pow($xk,2)));
					
					// Changement de repère
					$xs = atan($xk / $zk ) * $ATANSCALE;
					$ys = atan($yk / $zk ) * $ATANSCALE;
					
					// Recherche du pixl de texture correspondant
					$xtex = ($width*($xs+1)/2);
					$ytex = ($height*($ys+1)/2);
					
					// Si on est sur la sphère, ...
					if($xtex > 0 && $xtex < $from->width && $ytex > 0 && $ytex < $from->height){
						$this->copypoint($from, array($xtex, $ytex), array($cx+$x-$width/2, $cy+$y-$height/2));
					}
				}
			}
		}
		return $this;
	}

}
?>