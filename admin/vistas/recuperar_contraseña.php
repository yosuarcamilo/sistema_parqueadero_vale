<?php
require_once "../../controladores/recuperarContraseñaController.php";

// Obtener mensajes
$mensaje = isset($_SESSION['mensaje_error']) ? $_SESSION['mensaje_error'] : '';
$mensaje_exito = isset($_SESSION['mensaje_exito']) ? $_SESSION['mensaje_exito'] : '';
unset($_SESSION['mensaje_error']);
unset($_SESSION['mensaje_exito']);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Recuperar Contraseña - Parqueadero V.S</title>
  <link rel="stylesheet" type="text/css" href="../../css/login.css">
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
          <h1>PARQUEADERO V.S</h1>
        </div>
        <div class="formbg-outer">
          <div class="formbg">
            <div class="formbg-inner padding-horizontal--48">
              <span class="padding-bottom--15" style="font-size: 20px; font-weight: bold; text-align: center;">Recuperar Contraseña</span>
              <form method="POST" action="../../controladores/recuperarContraseñaController.php" autocomplete="off">
                <div class="field padding-bottom--24">
                  <label for="usuario">Usuario</label>
                  <input type="text" name="nombre_usuario" id="usuario" required>
                </div>
                <div class="field padding-bottom--24">
                  <label for="nueva_password">Nueva Contraseña</label>
                  <input type="password" name="nueva_contraseña" id="nueva_password" required>
                </div>
                <div class="field padding-bottom--24">
                  <label for="confirmar_password">Confirmar Contraseña</label>
                  <input type="password" name="confirmar_contraseña" id="confirmar_password" required>
                </div>
                <div class="field padding-bottom--24">
                  <input type="submit" name="cambiar_password" value="CAMBIAR CONTRASEÑA">
                </div>
                <div class="field padding-bottom--24">
                  <a href="../../index.php" style="color: #4cc8e7; text-decoration: none; font-size: 14px;">← Volver al inicio de sesión</a>
                </div>
                <?php if ($mensaje): ?>
                  <div class="error-message" style="animation: shake 0.5s ease-in-out; padding: 10px; margin-top: 10px; background-color: #ffebee; border: 1px solid #f44336; border-radius: 4px;"><?php echo htmlspecialchars($mensaje); ?></div>
                <?php endif; ?>
                <?php if ($mensaje_exito): ?>
                  <div style="padding: 10px; margin-top: 10px; background-color: #e8f5e9; border: 1px solid #4caf50; border-radius: 4px; color: #2e7d32;">
                    <?php echo htmlspecialchars($mensaje_exito); ?>
                  </div>
                <?php endif; ?>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>

