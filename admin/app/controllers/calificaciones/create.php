<?php

include ('../../../app/config.php');

$id_docente = $_GET['id_docente'];
$id_estudiante = $_GET['id_estudiante'];
$id_materia = $_GET['id_materia'];
$nota1 = $_GET['nota1'];
$nota2 = $_GET['nota2'];
$nota3 = $_GET['nota3'];

//echo "id_docente: ".$id_docente."-id_estudiante: ".$id_estudiante."-id_materia: ".$id_materia."-".$nota1."-".$nota2."-".$nota3;

//////////////////// nota
$sql = "SELECT * FROM calificaciones WHERE docente_id='$id_docente' and estudiante_id='$id_estudiante' and materia_id='$id_materia'  ";
$query = $pdo->prepare($sql);
$query->execute();
$notas = $query->fetchAll(PDO::FETCH_ASSOC);
foreach ($notas as $nota){
    $id_calificacion = $nota['id_calificacion'];
}
if($notas){
    echo "si existe registro";
    $sentencia = $pdo->prepare('UPDATE calificaciones
    SET nota1=:nota1,nota2=:nota2,nota3=:nota3, fyh_actualizacion=:fyh_actualizacion WHERE id_calificacion=:id_calificacion');

    $sentencia->bindParam(':nota1',$nota1);
    $sentencia->bindParam(':nota2',$nota2);
    $sentencia->bindParam(':nota3',$nota3);
    $sentencia->bindParam('fyh_actualizacion',$fechaHora);
    $sentencia->bindParam('id_calificacion',$id_calificacion);
    $sentencia->execute();
}else{
    echo "no existe registro";

    $sentencia = $pdo->prepare('INSERT INTO calificaciones
            (docente_id, estudiante_id ,materia_id, nota1, nota2, nota3, fyh_creacion, estado)
    VALUES (:docente_id,:estudiante_id,:materia_id,:nota1,:nota2,:nota3,:fyh_creacion,:estado)');

    $sentencia->bindParam(':docente_id',$id_docente);
    $sentencia->bindParam(':estudiante_id',$id_estudiante);
    $sentencia->bindParam(':materia_id',$id_materia);
    $sentencia->bindParam(':nota1',$nota1);
    $sentencia->bindParam(':nota2',$nota2);
    $sentencia->bindParam(':nota3',$nota3);
    $sentencia->bindParam('fyh_creacion',$fechaHora);
    $sentencia->bindParam('estado',$estado_de_registro);
    $sentencia->execute();
}
//////////////////// nota

