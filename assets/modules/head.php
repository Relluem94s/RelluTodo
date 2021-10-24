<head>
    <title>RelluTodo</title>
    <meta charset="utf-8">
    <?php 
    
    $cssLinks = array(
        "node_modules/bootstrap/dist/css/bootstrap.min.css",
        "node_modules/bootstrap/dist/css/bootstrap.css",
        "node_modules/@fortawesome/fontawesome-free/css/all.css",
        "assets/css/style.css"
    );
    
    foreach($cssLinks as $key => $link){
        echo '<link rel="stylesheet" href="' . $link . '">';
    }
    
    $jsLinks = array(
        "node_modules/jquery/dist/jquery.min.js",
        "node_modules/angular/angular.min.js",
        "node_modules/bootstrap/dist/js/bootstrap.min.js",
        "assets/js/todo_app.js"
    );
    
    foreach($jsLinks as $key => $link){
        echo '<script src="' . $link . '"></script>';
    }
    
    ?>

    <link rel="icon" href="assets/img/rellutodo_icon.png" type="image/x-icon">
    <link rel="shortcut icon" href="assets/img/rellutodo_icon.png" type="image/x-icon">
</head>
