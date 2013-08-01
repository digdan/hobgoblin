<?php
	class control_book {
		static function HGinit() {

			HG::map("GET","/books", "control_book#books");
			HG::map("GET","/book/edit/[h:id]", "control_book#edit", "book_edit"); //Display Edit

			HG::map("POST","/book/edit/[h:id]", "control_book#update"); //Save updates, then display edit

			HG::map("GET","/book", "control_book#load"); //Load list
			HG::map("GET","/book/[h:id]", "control_book#load"); //Load Book
			HG::map("GET","/book/cover/[h:id].jpg", "control_book#coverDisplay");
			HG::map("GET","/book/cover/thumb/[h:id].jpg", "control_book#coverDisplayThumb");


			HG::map("POST","/book", "control_book#update"); //Create
			HG::map("POST","/book/[h:uuid]", "control_book#update"); //Update
			HG::map("POST","/book/cover/[h:id]", "control_book#coverUpdate"); //Update Cover

			HG::map("DELETE","/book/[h:uuid]", "control_book#remove"); //Remove
		}


		function books($param=NULL) {
			global $config;
			Session::mustAuth();
			$books = R::find("books"," user_id = :user_id", array("user_id"=>Session::user()));

			HG::chain()->
				v("books",$books)->
				v("reason",array())->
				v("meta.title","List Books")->
				display("/books.php",true);
		}


		function edit($params=NULL) {
			global $config;
			Session::mustAuth();
			$user = R::load('users',Session::user());
			if ($book = R::load("books",hexdec($params["id"]))) {

				if ( ! R::areRelated($book,$user)) HG::force(401);

				$nodes = $book->ownNode;

				HG::chain()->
					v("book",$book)->
					v("reason",'')->
					v("nodes",$nodes)->
					v("meta.title","Edit Book")->
					display("/book-edit.php",true);
			} else {
				HG::force(404);
			}
		}

		function load($params=NULL) { //List and/or Load
			if (isset($params["id"])) {
				$b = R::load('books',hexdec($params["id"]));
				if ($b) {
					$bdata = $b->export(); //Export Object into Array
					//Load Node headers
					Request::ok(true);
					Request::data("book",$bdata);
				} else {
					Request::ok(false);
				}
			} else { //List all owned by session user
				$page = "100";

				$results = Search::main(array(
					array(
						'key'=>'author',
						'value'=>Session::user(true),
						'type'=>'book'
					)
				), $page);
				Request::ok(true);
				$out = array();
				if (is_array($results)) {
					foreach($results as $k=>$book) {
						$b = new book($book["uuid"]);
						$out[] = $b->export();
					}
				} else {
					$out = array();
				}
				Request::data("books",$out);
			}
		}

		function update($params=NULL) {
			if (Validate::run(
				array(
					"title"=>array(
						"required"=>TRUE,
						"min"=>1,
						"max"=>64
					),
					"title"=>array(
						"min"=>1,
						"max"=>200
					),
					"description"=>array(
						"required"=>TRUE,
						"min"=>1,
						"max"=>65536
					),
					"auth"=>true
				)
			)) {
				if (isset($params["id"])) {
					$book = R::load('books',hexdec($params["id"]));
					if ( ! in_array( Session::user() , $book->ownUser )) HG::force(401);
					//if (Session::user() != $book->user_id) HG::force(401);
				} else {
					$book = R::dispense('books');
				}

				//$book->import($_POST,'title,summary,description');
				Request::populate($book , array( 'title','description','summary' ), 'html,xss' );
				$user = R::load('users',Session::user());
				$user->ownBook[] = $book;
				$book->sharedAuthor[ Session::user() ] = $user;
				$id = R::store($book);
			}
			header("Location: ".HG::url("book_edit",array("id"=>$id)));
			die();
		}

		function coverUpdate($params=NULL) {
			global $config;
			$file = NULL;
			$data = array();

			if (isset($_FILES["photoimg"])) $file = $_FILES["photoimg"]["tmp_name"];
			$c = R::getAll("SELECT `data` FROM books WHERE id = ".hexdec($params["id"]));
			if ((count($c) > 0) and (strlen($c[0]['data']) > 0)) $data = unserialize(gzinflate($c[0]['data']));

			if ( ! is_null($file) ) {
				$fp = fopen($file,"r");
				$cover = new Imagick();
				$cover->readImageFile($fp);
				fclose($fp);

				$height=$cover->getImageHeight();
				$width=$cover->getImageWidth();

				if ($height < $width) { // 600x800 (3:4)
					$cover->scaleImage(800,0);
				} else {
					$cover->scaleImage(0,600);
				}

				$thumb = clone $cover;
				$thumb->cropThumbnailImage(160,160); // 160x160 width for thumb;
				$thumb->setImagePage(0, 0, 0, 0);
				$cover_token = md5($cover->getImageBlob());
				$data["cover"] = $cover_array = array(
					'token'=>$cover_token,
					'full'=>$cover->getImageBlob(),
					'thumb'=>$thumb->getImageBlob()
				);

				$new_data = gzdeflate(serialize($data));
				$q = "UPDATE books SET `data`=:new_data WHERE id = ".hexdec($params["id"]);

				R::exec(
					$q,
					array("new_data"=>$new_data)
				);
				echo json_encode(array("ok"=>true));
				die();
			}
			echo json_encode(array("ok"=>false));
		}

		function coverDisplay($params=NULL) { //Display Book Cover
			header("Content-Type: image/jpeg");
			$c = R::getAll("SELECT `data` FROM books WHERE id = ".hexdec($params["id"]));
			if ((count($c) > 0) and (strlen($c[0]['data']) > 0)) $data = unserialize(gzinflate($c[0]['data']));

			if (isset($data["cover"])) {
				echo $data["cover"]["full"];
				die();
			}
		}

		function coverDisplayThumb($params=NULL) { //Display Book Cover Thumb
			header("Content-Type: image/jpeg");
			$c = R::getAll("SELECT `data` FROM books WHERE id = ".hexdec($params["id"]));
			if ((count($c) > 0) and (strlen($c[0]['data']) > 0)) $data = unserialize(gzinflate($c[0]['data']));
			//header("Content-Type: image/jpeg");
			if (isset($data["cover"])) {
				echo $data["cover"]["thumb"];
				die();
			}
		}

		function remove($params=NULL) {
			//TODO Remove Book
			// Remove all images, nodes, chapters, reviews, comments
			// Remove Book Record
			if (isset($params["uuid"])) {
				$b = new book($params["uuid"]);
				$b->delete();
				Request::ok(true);
			} else {
				return false;
				Request::ok(false);
			}
		}

	}