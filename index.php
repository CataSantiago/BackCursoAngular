<?php

require_once 'vendor/autoload.php';

$app = new \Slim\Slim();

if(!function_exists("get_magic_quotes_gpc")) {
    function get_magic_quotes_gpc() { return 0; }
}


$db = new mysqli('localhost', 'root','','curso_angular');

//Configuracion de cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

$app->get("/pruebas", function() use($app, $db){
	echo "hola mundo desde Slim PHP";
});

$app->get("/PROBANDO", function() use($app){
	echo "OTRO SEXTO";
});

//Listar todos los productos
$app->get('/productos', function() use($db,$app){
	$sql = 'SELECT * FROM productos ORDER BY id DESC;';
	$query = $db->query($sql);

	$productos = array();
	while ($producto = $query->fetch_assoc()){
		$productos[] = $producto;
	}

	$result = array(
			'status' => 'success',
			'code' => 200,
			'data' => $productos
		);

	echo json_encode($result);
});

//Devolver un solo producto
$app->get('/producto/:id',function($id) use($db,$app){
	$sql = 'SELECT * FROM productos WHERE id = '.$id;
	$query = $db->query($sql);


	$result = array(
		'status' => 'error',
		'code' => 404,
		'message' => 'Producto no disponible'
	);
	if($query->num_rows == 1){
		$producto = $query->fetch_assoc();

		$result = array(
		'status' => 'success',
		'code' => 200,
		'data' => $producto
	);
	}

	echo json_encode($result);
});

//Eliminar un producto
$app->get('/delete-producto/:id',function($id) use($db,$app){
	$sql = 'DELETE FROM productos WHERE id = '.$id;
	$query = $db->query($sql);

	if($query){
		$result = array(
		'status' => 'success',
		'code' => 200,
		'message' => 'El producto se ha eliminado correctamente'
	);
	}else{
		$result = array(
		'status' => 'error',
		'code' => 404,
		'message' => 'Producto no se elimino'
	);
	}

	echo json_encode($result);
});

//Actualizar un producto
$app->post('/update-producto/:id',function($id) use($db,$app){
	$json = $app->request->post('json');
	$data = json_decode($json,true);

	$query_Update = "UPDATE productos SET ".
			"nombre = '{$data["nombre"]}',".
			"descripcion = '{$data["descripcion"]}',";

	if(isset($data['image'])){
		$query_Update .= "imagen = '{$data["image"]}',";
	}

	$query_Update .=	"precio = '{$data["precio"]}' WHERE id = {$id}";
	
	$sql_Update = $db->query($query_Update);


	if($sql_Update){
		$result = array(
		'status' => 'success',
		'code' => 200,
		'message' => 'El producto se ha actualizado correctamente'
		);
	}else{
		$result = array(
		'status' => 'error',
		'code' => 404,
		'message' => 'Producto no se ha actualizado'
		);
	}	

	echo json_encode($result);
});

//Subir una imagen a un producto
$app->post('/upload-file', function() use($db, $app){
	$result = array(
		'status'	=> 'error',
		'code' 		=> 404,
		'message'	=> 'El archivo no ha podido subirse'
	);

	if(isset($_FILES['uploads'])){
		$piramideUploader = new PiramideUploader();

		$upload = $piramideUploader->upload('image', "uploads", "uploads", array('image/jpeg','image/png', 'image/gif'));
		$file = $piramideUploader->getInfoFile();
		$file_name = $file['complete_name'];

		if(isset($upload) && $upload["uploaded"] == false){
			$result = array(
				'status'	=> 'error',
				'code'		=> 404,
				'message'	=> 'El archivo no ha podido subirse'
			);
		}else{
			$result = array(
				'status' 	=> 'success',
				'code'		=> 200,
				'message' 	=> 'El archivo se ha subido',
				'filename'  => $file_name
			);
		}
	}

	echo json_encode($result);
});


//Guardar productos
$app ->post('/productos',function() use($app,$db){
	$json = $app->request->getBody();
	$data = json_decode($json, true);

	if(!isset($data['imagen'])){
		$data['imagen']=null;
	}

	if(!isset($data['descripcion'])){
		$data['descripcion']=null;
	}

	if(!isset($data['precio'])){
		$data['precio']=null;
	}

	if(!isset($data['nombre'])){
		$data['nombre']=null;
	}

	$query = "INSERT INTO productos VALUES(NULL,".
			 "'{$data['nombre']}',".
			 "'{$data['descripcion']}',".
			 "'{$data['precio']}',".
			 "'{$data['imagen']}'".")";

	$insert = $db->query($query);

	$result = array(
			'status' => 'error',
			'code' => 404,
			'menssage' => 'Producto NO se ha creado'
		);

	if($insert){
		$result = array(
			'status' => 'success',
			'code' => 200,
			'message' => 'Producto creado correctamente'
		);
	}

	echo json_encode($result);

});

$app->run();