<?php
require_once "controladores/loginController.php";

// Obtener mensaje de error si existe
$mensaje = isset($_SESSION['mensaje_error']) ? $_SESSION['mensaje_error'] : '';
unset($_SESSION['mensaje_error']);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>parqueadero vale</title>
  <link rel="stylesheet" type="text/css" href="css/login.css">
</head>

<body>

  <div class="login-root">
    <div class="box-root flex-flex flex-direction--column" style="min-height: 100vh;flex-grow: 1;">
      <div class="loginbackground box-background--white padding-top--64">
        <div class="loginbackground-gridContainer">
          <div class="box-root flex-flex" style="grid-area: top / start / 8 / end;">
            <div class="box-root" style="background-image: linear-gradient(white 0%, rgb(247, 250, 252) 33%); flex-grow: 1;">
            </div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 4 / 2 / auto / 5;">
            <div class="box-root box-divider--light-all-2 animationLeftRight tans3s" style="flex-grow: 1;"></div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 7 / start / auto / 4;">
            <div class="box-root box-background--blue animationLeftRight" style="flex-grow: 1;"></div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 8 / 4 / auto / 6;">
            <div class="box-root box-background--gray100 animationLeftRight tans3s" style="flex-grow: 1;"></div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 2 / 15 / auto / end;">
            <div class="box-root box-background--cyan200 animationRightLeft tans4s" style="flex-grow: 1;"></div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 3 / 14 / auto / end;">
            <div class="box-root box-background--blue animationRightLeft" style="flex-grow: 1;"></div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 4 / 17 / auto / 20;">
            <div class="box-root box-background--gray100 animationRightLeft tans4s" style="flex-grow: 1;"></div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 5 / 14 / auto / 17;">
            <div class="box-root box-divider--light-all-2 animationRightLeft tans3s" style="flex-grow: 1;"></div>
          </div>
        </div>
      </div>
      <div class="box-root padding-top--24 flex-flex flex-direction--column" style="flex-grow: 1; z-index: 9;">
        <div class="box-root padding-top--48 padding-bottom--24 flex-flex flex-justifyContent--center">
          <h1>PARQUEADERO  V.S</h1>
        </div>
        <div class="formbg-outer">
          <div class="formbg">
            <div class="formbg-inner padding-horizontal--48">
              <span class="padding-bottom--15" style="font-size: 20px; font-weight: bold; text-align: center;">Iniciar sesión</span>
              <form method="POST" action="controladores/loginController.php" autocomplete="off">
                <div class="field padding-bottom--24">
                  <label for="usuario">Usuario</label>
                  <input type="text" name="nombre_usuario" id="usuario" required>
                </div>
                <div class="field padding-bottom--24">
                  <div class="grid--50-50">
                    <label for="password">Contraseña</label>
                    <div class="reset-pass">
                    </div>
                  </div>
                  <input type="password" name="contraseña" id="password" required>
                </div>
                <div class="field padding-bottom--24">
                  <input type="submit" name="submit" value="INGRESAR">
                </div>
                <a href="admin/vistas/recuperar_contraseña.php" style="font-size: 14px; color: #4cc8e7; text-decoration: none;">¿Olvidaste tu contraseña?</a>
                <?php if ($mensaje): ?>
                  <div class="error-message" style="animation: shake 0.5s ease-in-out; padding: 10px; margin-top: 10px; background-color: #ffebee; border: 1px solid #f44336; border-radius: 4px;"><?php echo htmlspecialchars($mensaje); ?></div>
                <?php endif; ?>
              </form>
            </div>
          </div>
          <div class="footer-link padding-top--24">
            <div class="listing padding-top--24 padding-bottom--24 flex-flex center-center">
              
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
