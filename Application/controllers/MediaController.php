<?php

	require_once(dirname(__FILE__) . '/../models/Medias.php');
	
	class MediaController extends Zend_Controller_Action
	{
		
		public function indexAction()
		{
			
		}
		
		public function videoAction()
		{
			$width = 			($this->getRequest()->getParam('width') != '')?			$this->getRequest()->getParam('width') 			: false;
			$height = 			($this->getRequest()->getParam('height') != '')?		$this->getRequest()->getParam('height') 		: false;
			$ratio_width = 		($this->getRequest()->getParam('ratio_width') != '')?	$this->getRequest()->getParam('ratio_width') 	: false;
			$ratio_height = 	($this->getRequest()->getParam('ratio_height') != '')?	$this->getRequest()->getParam('ratio_height') 	: false;
			$rate = 			($this->getRequest()->getParam('rate') != '')?			$this->getRequest()->getParam('rate') 			: false;
			$bits = 			($this->getRequest()->getParam('bits') != '')?			$this->getRequest()->getParam('bits') 			: false;
			$disable_audio = 	($this->getRequest()->getParam('disable_audio') != '')?	true 											: false;
			$limit_size = 		($this->getRequest()->getParam('limit_size') != '')?	$this->getRequest()->getParam('limit_size') 	: false;
			$media_uiid = 		$this->getRequest()->getParam('media_uiid');
			$format = 			$this->getRequest()->getParam('format');
    		
    		switch($format)
    		{
    			case 'avi':
    				$this->getResponse()->setHeader('Content-type', 'video/x-sgi-movie');
    				break;
    			case 'mov':
    				$this->getResponse()->setHeader('Content-type', 'video/quicktime');
    				break;
    			case '3gp':
    				$this->getResponse()->setHeader('Content-type', 'video/3gpp');
    				break;
    			case 'flv':
    				$this->getResponse()->setHeader('Content-type', 'video/x-flv');
    				break;
    			case 'mp4':
    				$this->getResponse()->setHeader('Content-type', 'video/mp4');
    				break;
    			case 'wmv':
    				$this->getResponse()->setHeader('Content-type', 'video/x-ms-wmv');
    				break;
    			case 'ogv':
    				$this->getResponse()->setHeader('Content-type', 'video/ogg');
    				break;
    			case 'webm':
    				$this->getResponse()->setHeader('Content-type', 'video/x-webm');
    				break;
    		}
			
			$mediasTable = new Medias();
			$select = $mediasTable->select();
			$select->where("uiid = ?", $media_uiid);
			$row = $mediasTable->fetchRow($select);
			if (!$row)
			{
				$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); 
				$this->getResponse()->appendBody('<p>Not found</p>'); 
				return;
			}
			$row = $row->toArray();
			
			$media_path = APPLICATION_PATH . '/../' . Zend_Registry::get('config')->get('mediasDirectory') . '/' . $media_uiid . '/';
			
			$outputFile = $media_path.$media_uiid;
			if($width && $height)
				$outputFile .= "_".$width."x".$height;
			if($ratio_width && $ratio_height)
				$outputFile .= "_".$ratio_width."r".$ratio_height;
			if($rate)
				$outputFile .= "_".$rate."Hz";
			if($bits)
				$outputFile .= "_".$bits."bits-s";
			if($disable_audio)
				$outputFile .= "_disable_audio";
			if($limit_size)
				$outputFile .= "_".$limit_size."k";
			$outputFile .= ".".$format;
			
			$query = '/usr/local/bin/ffmpeg ';
			$query .= "-i '".$media_path.$row['original']."' ";
			if($bits)
				$query .= "-b ".$bits."k ";
			
			// Video codec	
			switch($format)
    		{
    			case 'mp4':
					$query .= "-vcodec mpeg4 ";
    				break;
				case 'ogv':
					$query .= "-vcodec libtheora -strict experimental ";
					break;
		    	case 'mov':
					$query .= "-vcodec libx264 -vpre medium -coder 0 -trellis 0 -bf 0 -subq 6 -refs 5 ";
		    		break;
			}
			
			// Audio codec
			switch($format)
    		{
    			case 'mp4':
					$query .= "-acodec libmp3lame -ar 44100 ";
    				break;
		    	case 'mov':
					$query .= "-acodec libfaac -ar 44100 ";
		    		break;
		    	case 'ogv':
					$query .= "-acodec vorbis ";
		    		break;
		    	case 'webm':
					$query .= "-acodec vorbis -strict experimental ";
		    		break;
				default:
					$query .= "-acodec libmp3lame -ar 44100 ";
					break;
			}
			
			if($width && $height)
				$query .= "-s ".$width."x".$height." ";
			if($ratio_width && $ratio_height)
				$query .= "-aspect ".$ratio_width.":".$ratio_height." ";
			if($rate)
				$query .= "-r ".$rate." ";
			if($disable_audio)
				$query .= "-an ";
			if($limit_size)
				$query .= "-fs ".$limit_size." ";
			$query .= "'".$outputFile."'";
			
			$this->_helper->viewRenderer->setNoRender(true);
			
			if(!file_exists($outputFile))
			{
				Zend_Registry::get('logger')->info('[Video Convertion] exec: ' . escapeshellcmd($query));
				
				ob_start();
				passthru(escapeshellcmd($query));
				$output = ob_get_contents();
				ob_end_clean();
				
				Zend_Registry::get('logger')->info(Zend_Debug::dump($output, 'output', false));
			}
			
			if(!file_exists($outputFile))
			{
				Zend_Registry::get('logger')->err('[Video Convertion] ' . $outputFile . ' don\'t exists.');
				$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); 
				$this->getResponse()->appendBody('<p>Not found</p>'); 
				return;
			}
			
			$f= @fopen($outputFile,"r"); 
			if($f) 
			{ 
				$content = fread($f, filesize($outputFile));
				if(strlen($content) == 0)
				{
					fclose($f); 
					unlink($outputFile);
					
					Zend_Registry::get('logger')->err('[Video Convertion] Cache has no size (' . $outputFile . ')');
					
					$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); 
					$this->getResponse()->appendBody('<p>Not found</p>'); 
					return;
				}
				
				//Zend_Registry::get('logger')->info('[Video Convertion] loaded from cache (' . $outputFile . ')');
				
				print($content);
				fclose($f); 
			}
		}
		
		public function previewAction()
		{		
			$width = 			($this->getRequest()->getParam('width') != '')?			$this->getRequest()->getParam('width') 			: false;
			$height = 			($this->getRequest()->getParam('height') != '')?		$this->getRequest()->getParam('height') 		: false;
			$time_hour = 		($this->getRequest()->getParam('time_hour') != '')?		$this->getRequest()->getParam('time_hour') 		: "00";
			$time_minute = 		($this->getRequest()->getParam('time_minute') != '')?	$this->getRequest()->getParam('time_minute') 	: "00";
			$time_seconde = 	($this->getRequest()->getParam('time_seconde') != '')?	$this->getRequest()->getParam('time_seconde') 	: "00";
			$media_uiid = 		$this->getRequest()->getParam('media_uiid');
			$format = 			$this->getRequest()->getParam('format');
			
			$mediasTable = new Medias();
			$select = $mediasTable->select();
			$select->where("uiid = ?", $media_uiid);
			$row = $mediasTable->fetchRow($select);
			if (!$row)
			{
				$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); 
				$this->getResponse()->appendBody('<p>Not found</p>'); 
				return;
			}
			$row = $row->toArray();
			
			$media_path = APPLICATION_PATH . '/../' . Zend_Registry::get('config')->get('mediasDirectory') . '/' . $media_uiid . '/';
			
			switch ($format)
			{
				case 'jpeg':
				case 'JPG':
				case 'JPEG':
					$format = 'jpg';
					break;
				case 'PNG':
					$format = 'png';
					break;
			}
			
			$outputFile = $media_path.$media_uiid;
			$outputFile .= "_preview";
			if($width && $height)
				$outputFile .= "_".$width."x".$height;
			$outputFile .= "_".$time_hour."-".$time_minute."-".$time_seconde;
			$outputFile .= ".".$format;
			
			$query = '/usr/local/bin/ffmpeg ';
			$query .= "-i '".$media_path.$row['original']."' ";
			if($width && $height)
				$query .= "-s ".$width."x".$height." ";
			$query .= "-ss ".$time_hour.":".$time_minute.":".$time_seconde." ";
			$query .= "-vframes 1 ";
			$query .= "'".$outputFile."'";
			
			$this->_helper->viewRenderer->setNoRender(true);
    		
    		switch($format)
    		{
    			case 'jpg':
    				$this->getResponse()->setHeader('Content-type', 'image/jpeg');
    				break;
    			case 'png':
    				$this->getResponse()->setHeader('Content-type', 'image/png');
    				break;
    		}
			
			if(!file_exists($outputFile))
			{
				Zend_Registry::get('logger')->info('[Video Preview Convertion] exec: ' . escapeshellcmd($query));
				
				ob_start();
				passthru(escapeshellcmd($query));
				$output = ob_get_contents();
				ob_end_clean();
				
				Zend_Registry::get('logger')->info(Zend_Debug::dump($output, 'output', false));
			}
			
			if(!file_exists($outputFile))
			{
				$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); 
				$this->getResponse()->appendBody('<p>Not found</p>'); 
				return;
			}
			
			$f= @fopen($outputFile,"r"); 
			if($f) 
			{ 
				$content = fread($f, filesize($outputFile));
				if(strlen($content) == 0)
				{
					fclose($f); 
					unlink($outputFile);
					
					$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); 
					$this->getResponse()->appendBody('<p>Not found</p>'); 
					return;
				}
				print($content);
				fclose($f); 
			}
		}
		
		public function audioAction()
		{	
			$bit_rate = 		($this->getRequest()->getParam('bit_rate') != '')?			$this->getRequest()->getParam('bit_rate') 			: false;
			$sampling_rate = 	($this->getRequest()->getParam('sampling_rate') != '')?		$this->getRequest()->getParam('sampling_rate') 		: false;
			$limit_size = 		($this->getRequest()->getParam('limit_size') != '')?		$this->getRequest()->getParam('limit_size') 		: false;
			$media_uiid = 		$this->getRequest()->getParam('media_uiid');
			$format = 			$this->getRequest()->getParam('format');
			
			$mediasTable = new Medias();
			$select = $mediasTable->select();
			$select->where("uiid = ?", $media_uiid);
			$row = $mediasTable->fetchRow($select);
			if (!$row)
			{
				$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); 
				$this->getResponse()->appendBody('<p>Not found</p>'); 
				return;
			}
			$row = $row->toArray();
			
			$media_path = APPLICATION_PATH . '/../' . Zend_Registry::get('config')->get('mediasDirectory') . '/' . $media_uiid . '/';
			
			$outputFile = $media_path.$media_uiid;
			if($bit_rate)
				$outputFile .= "_".$bit_rate."bits-s";
			if($sampling_rate)
				$outputFile .= "_".$sampling_rate."Hz";
			if($limit_size)
				$outputFile .= "_".$limit_size."k";
			$outputFile .= ".".$format;
			
			$query = '/usr/local/bin/ffmpeg ';
			$query .= "-i '".$media_path.$row['original']."' ";
    		switch($format)
    		{
    			case 'mp3':
					$query .= "-acodec libmp3lame ";
    				break;
    			case 'wav':
    				break; 
    			case 'aac':
					$query .= "-acodec aac -strict experimental ";
    				break;
    			case 'm4a':
					$query .= "-acodec aac ";
    				break;
    		}
			if($bit_rate)
				$query .= "-ab ".$bit_rate." ";
			if($sampling_rate)
				$query .= "-ar ".$sampling_rate." ";
			if($limit_size)
				$query .= "-fs ".$limit_size." ";
			$query .= "-map_meta_data ";
			$query .= "'".$outputFile."'";
			
			$this->_helper->viewRenderer->setNoRender(true);
    		
    		switch($format)
    		{
    			case 'mp3':
    				$this->getResponse()->setHeader('Content-type', 'audio/mpeg');
    				break;
    			case 'wav':
    				$this->getResponse()->setHeader('Content-type', 'audio/vnd.wave');
    				break;
    			case 'aac':
    				$this->getResponse()->setHeader('Content-type', 'audio/aac');
    				break;
    			case 'm4a':
    				$this->getResponse()->setHeader('Content-type', 'audio/x-m4a');
    				break;
    		}
			
			if(!file_exists($outputFile))
			{
				Zend_Registry::get('logger')->info('[Audio Convertion] exec: ' . escapeshellcmd($query));
				
				ob_start();
				passthru(escapeshellcmd($query));
				$output = ob_get_contents();
				ob_end_clean();
				
				Zend_Registry::get('logger')->info(Zend_Debug::dump($output, 'output', false));
			}
			
			if(!file_exists($outputFile))
			{
				$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); 
    			$this->getResponse()->setHeader('Content-type', 'text/html');
				$this->getResponse()->appendBody('<p>Not found</p>'); 
				return;
			}
			
			$f= @fopen($outputFile,"r"); 
			if($f) 
			{ 
				$content = fread($f, filesize($outputFile));
				if(strlen($content) == 0)
				{
					fclose($f); 
					unlink($outputFile);
					
					$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); 
	    			$this->getResponse()->setHeader('Content-type', 'text/html');
					$this->getResponse()->appendBody('<p>Not found</p>'); 
					return;
				}
				print($content);
				fclose($f); 
			}
		}
		
		public function pictureAction()
		{		
			$width = 		($this->getRequest()->getParam('width') != '')?		$this->getRequest()->getParam('width') 	: false;
			$height = 		($this->getRequest()->getParam('height') != '')?	$this->getRequest()->getParam('height') : false;
			$media_uiid = 	$this->getRequest()->getParam('media_uiid');
			$format = 		$this->getRequest()->getParam('format');
			
			$mediasTable = new Medias();
			$select = $mediasTable->select();
			$select->where("uiid = ?", $media_uiid);
			$row = $mediasTable->fetchRow($select);
			if (!$row)
			{
				$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); 
				$this->getResponse()->appendBody('<p>Not found</p>'); 
				return;
			}
			$row = $row->toArray();
			
			$media_path = APPLICATION_PATH . '/../' . Zend_Registry::get('config')->get('mediasDirectory') . '/' . $media_uiid . '/';
			
			switch ($format)
			{
				case 'jpeg':
				case 'JPG':
				case 'JPEG':
					$format = 'jpg';
					break;
				case 'PNG':
					$format = 'png';
					break;
				case 'GIF':
					$format = 'gif';
					break;
			}
			
			$outputFile = $media_path.$media_uiid;
			if($width && $height)
				$outputFile .= "_".$width."x".$height;
			$outputFile .= ".".$format;
			
			$inputFile = $media_path.$row['original'];
			
			$this->_helper->viewRenderer->setNoRender(true);
    		
    		switch($format)
    		{
    			case 'jpg':
    				$this->getResponse()->setHeader('Content-type', 'image/jpeg');
    				break;
    			case 'png':
    				$this->getResponse()->setHeader('Content-type', 'image/png');
    				break;
    			case 'gif':
    				$this->getResponse()->setHeader('Content-type', 'image/gif');
    				break;
    		}
			
			if(!file_exists($outputFile))
			{
				Zend_Registry::get('logger')->info('[Picture Convertion]');
				
				// DŽterminer l'extension ˆ partir du nom de fichier
				$extension = substr( $inputFile, -3 );
				// Afin de simplifier les comparaisons, on met tout en minuscule
				$extension = strtolower( $extension );
			
				switch ( $extension ) {
			
				    case "jpg":
				    case "peg": //pour le cas o l'extension est "jpeg"
				        $src_im = imagecreatefromjpeg( $inputFile );
				        break;
			
				    case "gif":
				        $src_im = imagecreatefromgif( $inputFile );
				        break;
			
				    case "png":
				        $src_im = imagecreatefrompng( $inputFile );
				        break;
			
				    default:
				        Zend_Registry::get('logger')->error("L'image n'est pas dans un format reconnu. Extensions autorisŽes : jpg/jpeg, gif, png");
						$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); 
						$this->getResponse()->appendBody('<p>Not found</p>'); 
						return;
				        break;
				}
			
				$size = getimagesize($inputFile);
				$src_w = $size[0];
				$src_h = $size[1];
				
				$src_x = 0;
				$src_y = 0;
				
				if($width && $height)
				{
					$dst_w = $width;
					$dst_h = $height;
					
					if($dst_w*$src_h/$dst_h > $src_w)
					{
						$h = $dst_h * $src_w / $dst_w;
						$w = $src_w;
					}
					else
					{
						$w = $dst_w * $src_h / $dst_h;
						$h = $src_h;
					}
				}
				else 
				{
					$dst_w = $src_w;
					$dst_h = $src_h;
				}
				
				$dst_im = imagecreatetruecolor($dst_w,$dst_h);
				imagecopyresampled($dst_im, $src_im, 0, 0, ($src_w - $w) / 2, ($src_h - $h) / 2, $dst_w, $dst_h, $w, $h);			
    		
	    		switch($format)
	    		{
	    			case 'jpg':
	    				imagejpeg($dst_im, $outputFile);
	    				break;
	    			case 'png':
	    				imagepng($dst_im, $outputFile);
	    				break;
	    			case 'gif':
	    				imagegif($dst_im, $outputFile);
	    				break;
	    		}
				
				imagedestroy($dst_im);
				imagedestroy($src_im);
			}
			
			if(!file_exists($outputFile))
			{
				$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); 
				$this->getResponse()->appendBody('<p>Not found</p>'); 
				return;
			}
			
			$f= @fopen($outputFile,"r"); 
			if($f) 
			{ 
				$content = fread($f, filesize($outputFile));
				if(strlen($content) == 0)
				{
					fclose($f); 
					unlink($outputFile);
					
					$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); 
					$this->getResponse()->appendBody('<p>Not found</p>'); 
					return;
				}
				print($content);
				fclose($f); 
			}
		}
		
	}