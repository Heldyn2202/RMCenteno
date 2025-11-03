<?php

 */
include ('../../../app/config.php');

$id_rol = $_GET['rol_id'];
$permiso_id = $_GET['permiso_id'];

$sentencia = $pdo->prepare('INSERT INTO roles_permisos
(rol_id,permiso_id,fyh_creacion, estado)
VALUES ( :rol_id,:permiso_id,:fyh_creacion,:estado)');

$sentencia->bindParam(':rol_id',$id_rol);
$sentencia->bindParam(':permiso_id',$permiso_id);
$sentencia->bindParam('fyh_creacion',$fechaHora);
$sentencia->bindParam('estado',$estado_de_registro);
$sentencia->execute();

?>
    <div class="row">
        <table class="table table-bordered table-sm table-striped table-hover" id="tabla_res<?=$id_rol;?>">
            <tr>
                <th style="text-align: center;background-color: #dbcd59">Nro</th>
                <th style="text-align: center;background-color: #dbcd59">Rol</th>
                <th style="text-align: center;background-color: #dbcd59">Permiso</th>
                <th style="text-align: center;background-color: #dbcd59">Acción</th>
            </tr>
            <?php
            $contador = 0;
            $sql_roles_permisos = "SELECT * FROM roles_permisos as rolper 
                       INNER JOIN permisos as per ON per.id_permiso = rolper.permiso_id 
                       INNER JOIN roles as rol ON rol.id_rol = rolper.rol_id 
                       where rolper.estado = '1' ORDER BY per.nombre_url ASC ";
            $query_roles_permisos = $pdo->prepare($sql_roles_permisos);
            $query_roles_permisos->execute();
            $roles_permisos = $query_roles_permisos->fetchAll(PDO::FETCH_ASSOC);

            foreach ($roles_permisos as $roles_permiso){

                if($id_rol == $roles_permiso['rol_id']){
                    $id_rol_permiso = $roles_permiso['id_rol_permiso'];
                    $contador = $contador + 1; ?>
                    <tr>
                        <td><center><?=$contador;?></center></td>
                        <td><center><?=$roles_permiso['nombre_rol'];?></center></td>
                        <td><?=$roles_permiso['nombre_url'];?></td>
                        <td>
                            <form action="<?=APP_URL;?>/app/controllers/roles/delete_rol_permiso.php" onclick="preguntar<?=$id_rol_permiso;?>(event)"
                                  method="post" id="miFormulario<?=$id_rol_permiso;?>">
                                <input type="text" name="id_rol_permiso" value="<?=$id_rol_permiso;?>" hidden>
                                <button type="submit" class="btn btn-danger btn-sm" style=""><i class="bi bi-trash"></i></button>
                            </form>
                            <script>
                                function preguntar<?=$id_rol_permiso;?>(event) {
                                    event.preventDefault();
                                    Swal.fire({
                                        title: 'Eliminar registro',
                                        text: '¿Desea eliminar este registro?',
                                        icon: 'question',
                                        showDenyButton: true,
                                        confirmButtonText: 'Eliminar',
                                        confirmButtonColor: '#a5161d',
                                        denyButtonColor: '#270a0a',
                                        denyButtonText: 'Cancelar',
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            var form = $('#miFormulario<?=$id_rol_permiso;?>');
                                            form.submit();
                                        }
                                    });
                                }
                            </script>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
        </table>
    </div>
<?php
/*
if($sentencia->execute()){
    echo 'success';
    session_start();
    $_SESSION['mensaje'] = "Se registro el permiso de la manera correcta en la base de datos";
    $_SESSION['icono'] = "success";
    header('Location:'.APP_URL."/admin/roles/permisos.php");
//header('Location:' .$URL.'/');
}else{
    echo 'error al registrar a la base de datos';
    session_start();
    $_SESSION['mensaje'] = "Error no se pudo registrar en la base datos, comuniquese con el administrador";
    $_SESSION['icono'] = "error";
    ?><script>window.history.back();</script><?php
}*/