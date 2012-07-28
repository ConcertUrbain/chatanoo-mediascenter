<?php

	require_once(dirname(__FILE__) . '/../models/Medias.php');

	class IndexController extends Zend_Controller_Action
	{
		
		public function indexAction()
		{
			
		}
		
		public function curluploadAction()
		{
			$this->_helper->viewRenderer->setNoRender(true);
			
			if(!isset($_FILES['file']))
			{
				print('No File Error');
				return;
			}
			
			$file = $_FILES['file'];
			$chars = str_split("abcdefghijklmnopkrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
			$filename = '';
			for($i = 0; $i < 10; $i++)
				$filename .= $chars[rand(0, count($chars) - 1)];
			$pathinfo = pathinfo($file['name']);
			$filename .= '.' . $pathinfo['extension'];
			
			$pathinfo = pathinfo($_FILES['file']['tmp_name']);
			$filepath = $pathinfo['dirname'] . '/' . $filename;
			
			if(!move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) 
				print('Move Uploaded File Error');
			
			$postdata = array();
			$postdata ['file'] = "@".$filepath.";type=".$_FILES['file']['type'];
			 
			$post_url = 'http://ms.dring93.org/upload';
			 
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $post_url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			$response = curl_exec($ch);
			print($response);
			curl_close ($ch);
			
			unlink($filepath);
		}
		
		public function uploadAction()
		{
			$this->_helper->viewRenderer->setNoRender(true);
			
			if(!isset($_FILES['file']))
			{
				print('No File Error');
				return;
			}
			
			$file = $_FILES['file'];
			
			$medias_dir_path = APPLICATION_PATH . '/../' . Zend_Registry::get('config')->get('mediasDirectory') . '/';
			
			$chars = str_split("abcdefghijklmnopkrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
			$uiid = 'MC-';
			for($i = 0; $i < 8; $i++)
				$uiid .= $chars[rand(0, count($chars) - 1)];
			
			$pathinfo = pathinfo($file['name']);	
			
			switch ($file['type'])
			{
				case 'image/jpeg':
				case 'image/png':
				case 'image/gif':
					$type = 'picture';
					$uiid .= '-P';
					break;
				case 'audio/mpeg':
				case 'audio/vnd.wave':
				case 'audio/aac':
				case 'audio/x-m4a':
				case 'audio/x-ms-wma':
				case 'audio/mp3':
				case 'audio/wav':
					$type = 'audio';
					$uiid .= '-A';
					break;
				case 'video/x-sgi-movie':
				case 'video/quicktime':
				case 'video/3gpp':
				case 'video/x-flv':
				case 'video/mp4':
				case 'video/x-ms-wmv':
					$type = 'video';
					$uiid .= '-V';
					break;
				case 'application/octet-stream': 
					switch(strtolower($pathinfo["extension"]))
					{
						case 'png':
					    case 'jpg':
						case 'jpeg':
					    case 'gif':
					    	$type = 'picture';
					        $uiid .= '-P';
					        break;
						case 'mp3':	
						case 'ogg':
					    case 'wav':
					    case 'aac':
					    case 'm4a':
					    	$type = 'audio';
					        $uiid .= '-A';
					        break;
						case 'mov':
						case '3gp':
						case '3g2':
						case 'flv':
						case 'mp4':
						case 'wmv':
							$type = 'video';
							$uiid .= '-V';
							break;
						default:
							print('Invalid file extension: ' . $file['extension']);
							return;
							break;
					}
					break;
				default:
					print('Invalid file type: ' . $file['type']);
					return;
					break;
			}
			
			$pathinfo = pathinfo($file['name']);
			$filename = $uiid . "." . $pathinfo["extension"];
			$filepath = $medias_dir_path . $uiid . '/' . $filename;
			
			mkdir($medias_dir_path . $uiid);
			
			if(move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) 
			{
				$mediasTable = new Medias();
				$row = $mediasTable->createRow(array(
					'uiid' => $uiid,
					'type' => $type,
					'original' => $filename
				)); 
				if($row->save())
				{
					print($uiid);
				}
				else 
				{
					print('Database Error');
				}
			} 
			else
			{
				print('Move Uploaded File Error');
			}
		}
		
	}