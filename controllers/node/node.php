<?php
	class control_node {
		static function HGinit() {
			HG::map("GET","/nodes", "control_node#listNodes"); //Load Node List ( All by User )
			HG::map("POST","/node", "control_node#update"); //create
			HG::map("POST|GET","/nodes/[h:book_id]", "control_node#listNodes"); //Load Node List ( All by Book )
			HG::map("POST|GET","/node/[h:node_id]", "control_node#edit"); //Edit existing Node
			HG::map("POST|GET","/node/create/[h:book_id]", "control_node#create"); // Create node for book
			HG::map("POST|GET","/node/remove", "control_node#remove"); //Remove Node
		}

		function edit($params=NULL) {
			$reason = "";
			$n = R::load('nodes', hexdec($params["node_id"]) );
			$b = R::load('books', $n->book_id );
			Session::mustAuth();
			if (Session::user() != $n->user_id) HG::force(401); //Owner only
			HG::chain()->
				v("breadcrumbs",array(
					"My Books"=>"/books",
					"{$b->title}"=>"/book/edit/".$b->id,
					$n->title=>""
				))->
				v("node",$n)->
				v("book",$b)->
				v("node_hid",dechex($n->id))->
				v("book_hid",dechex($n->book_id))->
				v("meta.title","Edit Node - {$n->title} - {$b->title}")->
				v("reason",$reason)->
				display("node-edit.php",true);
		}

		function update($params=NULL) {
			if ( ! isset($params["book_id"])) { //No UUID, Create
				if (Validate::run(
					array(
						"title"=>array(
							"required"=>TRUE,
							"min"=>1,
							"max"=>256
						),
						"slug"=>array(
							"required"=>TRUE,
							"min"=>1,
							"max"=>256
						),
						"auth"=>true
					)
				)) {
					$n = new node();
					Request::filterHTML(array('title','slug','content'));
					Request::populate($n,array(
						'title','slug','content'
					));
					$n->author = Session::user(true);
					$n->owner = $n->author;
					if ($n->isOwner()) {
						$n->update(); //Create Object
						Response::ok(true,"Node created");
					} else {
						Response::ok(false,'Not node owner');
					}
				}
			} else { //Update
				$n = new node(hexdec($params["book_id"]));
				Request::filterHTML(array('title','slug','content'));
				Request::populate($n,array(
					'title','slug','content','visible'
				));
				if ($n->update()) {
					Response::ok(true,"Node updated");
				} else {
					Response::ok(false,"Error updating node");
				}
			}
		}

		/**
		 * @param null $params - UUID of Book ( option )
		 */
		function listNodes($params=NULL) {
			if ( ! isset($params["book_id"] )) { //Book ID not provided, display all
				$nodes = R::find("nodes"," user_id = :user_id", array("user_id"=>Session::user()));
				HG::v("nodes",$nodes);
			} else { //List all nodes for book
				$nodes = R::find("nodes","  book_id = :book_id", array("book_id"=>hexdec($params["book_id"])) );
				HG::v("nodes",$nodes);
			}
		}

		function load($params=NULL) {
			$b = new book(hexdec($params["book_id"]));
			Response::ok(false);
			if ($b) {
				$ndata = $b->export();
				Response::ok(true);
				HG::v("node",$ndata);
			}
		}

		function create($params=NULL) {
			$b = R::load('books', hexdec($params["book_id"]) );
			Session::mustAuth();

			if (Session::user() != $b->user_id) HG::force(404); //Owner only

			if (Request::r("cmd")) { //Posted
				//Create Node
				$node = R::dispense('nodes');

				$user = R::load('users',Session::user());
				$node->ownUser = $user;

				$node->created = time();
				$node->title = Request::r('title');
				$node->slug = Request::r('slug');
				$node->outbound = "";
				$node->content = Request::r('node_content');
				$node->details = serialize( array() );

				$new_id = R::store($node);

				//Add relation to book
				$nodes = $b->nodes;
				$nodes[$new_id] = $node;
				$b->ownNode = $nodes;
				R::store($b);

				header("Location: /node/".dechex($new_id));
			}

			HG::chain()->
			v("breadcrumbs",array(
				"My Books"=>"/books",
				"{$b->title}"=>"/book/edit/".$b->id,
				"New Node"=>""
			))->
			v("book",$b)->
			v("node", R::dispense("nodes"))->
			v("book_hid",$params["book_id"])->
			v("meta.title","Create Node : {$b->title}")->
			v("reason",'')->
			display("node-edit.php",true);
		}
	}