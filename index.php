<?php

  $conn = @new mysqli('localhost', 'root', 'root', 'simple-tdl');
  session_start();

  $database = $conn->query("SHOW TABLES");
  if($database->num_rows == 0){
    $conn->query("CREATE TABLE ids( id MEDIUMTEXT );");
    $conn->query("CREATE TABLE list( nu INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, id MEDIUMTEXT, todo MEDIUMTEXT, status INT(11) )");
  }
  
  if($_SERVER['REQUEST_METHOD'] == 'POST'){

    if($_POST['method'] == 'in'){
      $in = $conn->query("SELECT * FROM ids WHERE id = '$_POST[id]'");
      if($in->num_rows == 1){
        setcookie('id', $_POST['id'], time() + (86400 * 30 * 12), "/");
      } else{
        $_SESSION['in'] = 'This ID does not exist';
      }
    }

    if($_POST['method'] == 'up'){
      $up = $conn->query("SELECT * FROM ids WHERE id = '$_POST[id]'");
      if($up->num_rows == 0){
        $conn->query("INSERT INTO ids VALUES ('$_POST[id]')");
        setcookie('id', $_POST['id'], time() + (86400 * 30 * 12), "/");
      } else{
        $_SESSION['up'] = 'This ID is not aviable';
      }
    }

    if($_POST['method'] == 'out'){ setcookie('id', null, -1, '/');  }

    if($_POST['method'] == 'del'){
      $conn->query("DELETE FROM ids WHERE id = '$_COOKIE[id]'");
      $conn->query("DELETE FROM list WHERE id = '$_COOKIE[id]'");
      setcookie('id', null, -1, '/'); 
    }

    if($_POST['method'] == 'done'){ $conn->query("UPDATE list set status = '1' WHERE nu = '$_POST[id]'"); }

    if($_POST['method'] == 'delete'){ $conn->query("DELETE FROM list WHERE nu = '$_POST[id]'"); }

    if($_POST['method'] == 'todo'){ $conn->query("INSERT INTO list (id, todo, status) VALUES ('$_COOKIE[id]', '$_POST[todo]', '0')"); $_SESSION['todo'] = 'Done!'; }

    header('Location: index.php');
    die();

  }

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="fontawesome.css">
    <link rel="stylesheet" href="bootstrap.css">
    <link rel="stylesheet" href="style.css">
    <title>To Do List | Mohammed Waleed</title>
  </head>
  <body>

    <header>
      <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="h2 text-light" href="#">To Do List</a>
      </nav>
      <div class="clearfix"></div>
    </header>
    
    <main class="container bg-dark text-light">
      <?php
      
      if(isset($_COOKIE['id'])){

        setcookie('id', $_COOKIE['id'], time() + (86400 * 30 * 12), "/");
        $todo = $conn->query("SELECT todo FROM list WHERE id = '$_COOKIE[id]'");
        $done = $conn->query("SELECT todo FROM list WHERE id = '$_COOKIE[id]' AND status = '1'");

        echo '
        <script>
          var today = new Date();
          var date = today.getDate()+"/"+(today.getMonth()+1)+"/"+today.getFullYear();
        </script>
        <div class="h3 float-right"><'.$done->num_rows.'/'.$todo->num_rows.'>
          <form class="d-inline" method="post"><input type="hidden" name="method" value="out"><button type="submit" class="btn btn-primary"><i class="fas fa-sign-out-alt"></i></button></form>
          <form class="d-inline" method="post"><input type="hidden" name="method" value="del"><button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i></button></form>
        </div>
        <div class="h3">ID: #'.$_COOKIE['id'].' &nbsp; @<script>document.write(date);</script></div><div class="clearfix"></div><hr>
        <div class="list">
          <form method="post">
            <input type="hidden" name="method" value="todo">
            <label class="h5">Your Todo: </label> <input type="text" name="todo" required>
            <button type="submit" class="btn btn-success">Add</button>
            <label class="h5 text-success">'.@$_SESSION['todo'].'</label><hr>
          </form>
          ';
          $i = 1; $todos = $conn->query("SELECT nu, todo FROM list WHERE status = '0' AND id = '$_COOKIE[id]' ORDER BY nu");
          while($todo = $todos->fetch_assoc()){
            echo '
            <div class="todo h5">
              '.$i.'. '.$todo['todo'].'
              <form method="post" class="d-inline float-right">
                <input type="hidden" name="method" value="delete"> <input type="hidden" name="id" value="'.$todo['nu'].'">
                <button class="btn btn-danger"><i class="fas fa-trash-alt"></i></button>
              </form>
              <form method="post" class="d-inline float-right">
                <input type="hidden" name="method" value="done"> <input type="hidden" name="id" value="'.$todo['nu'].'">
                <button class="btn btn-success"><i class="fas fa-check-square"></i></button>
              </form>
              <div class="clearfix"></div>
            </div>
            '; $i++;
          }

          $i = 1; $todos = $conn->query("SELECT nu, todo FROM list WHERE status = '1' AND id = '$_COOKIE[id]' ORDER BY nu");
          while($todo = $todos->fetch_assoc()){
            echo '
            <div class="todo h5">
              <i class="fas fa-check-square text-success"></i> '.$todo['todo'].'
              <form method="post" class="d-inline float-right">
                <input type="hidden" name="method" value="delete"> <input type="hidden" name="id" value="'.$todo['nu'].'">
                <button class="btn btn-danger"><i class="fas fa-trash-alt"></i></button>
              </form>
              <div class="clearfix"></div>
            </div>
            '; $i++;
          }
          echo'
          

          
        </div>
        ';

      } else{
        echo '
        <form method="post">
          <input type="hidden" name="method" value="in">
          <div class="h3 text-center">Sign In</div>
          <label class="h5">Your ID: </label> <input type="text" name="id" required>
          <button type="submit" class="btn btn-success">Sign in</button>
          <label class="h5 text-danger">'.@$_SESSION['in'].'</label>
        </form>
        <hr>
        <div class="clearfix forms"></div>
        <hr>
        <form method="post">
          <input type="hidden" name="method" value="up">
          <div class="h3 text-center">Sign Up</div>
          <label class="h5">Your New ID: </label> <input type="text" name="id" required>
          <button type="submit" class="btn btn-success">Sign up</button>
          <label class="h5 text-danger">'.@$_SESSION['up'].'</label>
        </form>
        ';
      }

      ?>
    </main>

    <footer class="container-fluid bg-dark text-light">
      <div class="copyright">Made With <i class="fas fa-heart text-danger"></i> By Mohammed Waleed | © 2019 All Rights Reserved | 
      <?php $ids = $conn->query("SELECT * FROM ids"); $list = $conn->query("SELECT * FROM list"); echo $ids->num_rows.'/'.$list->num_rows; ?>
      </div>
      <div class="social">
        <a href="https://www.facebook.com/mohamed.w.rady/" target="_blank" class="fab fa-facebook facebook"></a>
        <a href="http://m.me/mohamed.w.rady/" target="_blank" class="fab fa-facebook-messenger messenger"></a>
        <a href="https://www.instagram.com/mohamed.w_r/" target="_blank" class="fab fa-instagram insta"></a>
      </div>
    </footer>

  </body>
</html>
<?php unset($_SESSION['in']); unset($_SESSION['up']); unset($_SESSION['todo']); ?>