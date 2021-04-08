<?php

namespace App\Http\Controllers;

use App\Models\AlumnoProyecto;
use App\Models\Documento;
use App\Models\Proyecto;
use App\Models\Retroalimentacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DocumentoController extends Controller
{

  public function registrarDocumentoPractica(Request $request) {
    $request->validate([
      "documento" => "required",
      "tipo" => "required",
      "matricula" => "required",
      "alumno_id" => "required",
      "proyecto_id" => "required"
    ]);
    $filepath = $request->matricula."/".$request->tipo.".pdf";
    if (Storage::disk("documento-practica")->exists($filepath)) {
      throw ValidationException::withMessages([
        "documento" => ["El documento ya ha sido registrado"]
      ]);
    }
    $documento = Documento::create([
      "ruta" => $filepath,
      "estado" => "ENVIADO",
      "alumno_id" => $request->alumno_id,
      "proyecto_id" => $request->proyecto_id,
      "nombre" => $request->tipo,
    ]);
    Storage::disk("documento-practica")->put($filepath, file($request->documento));
    return response()->json($documento, 200);
  }

  public function registrarDocumentoServicio(Request $request) {
    $request->validate([
      "documento" => "required",
      "tipo" => "required",
      "matricula" => "required",
      "alumno_id" => "required",
      "proyecto_id" => "required"
    ]);
    $filepath = $request->matricula."/".$request->tipo.".pdf";
    if (Storage::disk("documento-servicio")->exists($filepath)) {
      throw ValidationException::withMessages([
        "documento" => ["El documento ya ha sido registrado"]
      ]);
    }
    $documento = Documento::create([
      "ruta" => $filepath,
      "estado" => "ENVIADO",
      "alumno_id" => $request->alumno_id,
      "proyecto_id" => $request->proyecto_id,
      "nombre" => $request->tipo,
    ]);
    Storage::disk("documento-servicio")->put($filepath, file($request->documento));
    return response()->json($documento, 200);
  }

  public function modificarDocumentoPractica(Request $request) {
    $request->validate([
      "documento" => "required",
      "ruta" => "required",
      "id" => "required"
    ]);
    if(Storage::disk("documento-practica")->exists($request->ruta)) {
      Documento::where("id", $request->id)->update(["estado" => "ENVIADO"]);
      $documento = Documento::find($request->id);
      $request->file('documento')->storeAs("documentos/practicas/S14017957", $documento->nombre.".pdf");
    } else {
      throw ValidationException::withMessages([
        "documento" => ["El documento no existe"]
      ]);
    }
  }

  public function modificarDocumentoServicio(Request $request) {
    $request->validate([
      "documento" => "required",
      "ruta" => "required",
      "id" => "required"
    ]);
    if(Storage::disk("documento-servicio")->exists($request->ruta)) {
      Documento::where("id", $request->id)->update(["estado" => "ENVIADO"]);
      $documento = Documento::find($request->id);
      $request->file('documento')->storeAs("documentos/servicio/S14017957", $documento->nombre.".pdf");
    } else {
      throw ValidationException::withMessages([
        "documento" => ["El documento no existe"]
      ]);
    }
  }

  public function descargarDocumentoPractica(Request $request) {
    $request->validate(["ruta" => "required", "nombre" => "required"]);
    if (Storage::disk("documento-practica")->missing($request->ruta)) {
      throw ValidationException::withMessages([
        "documento" => ["No existe el documento"]
      ]);
    }
    $headers = array("Content-Type: application/pdf");
    return Storage::download("documentos/practicas/".$request->ruta, $request->nombre.".pdf", $headers);
  }

  public function descargarDocumentoServicio(Request $request) {
    $request->validate(["ruta" => "required", "nombre" => "required"]);
    if (Storage::disk("documento-servicio")->missing($request->ruta)) {
      throw ValidationException::withMessages([
        "documento" => ["No existe el documento"]
      ]);
    }
    $headers = array("Content-Type: application/pdf");
    return Storage::download("documentos/servicio/".$request->ruta, $request->nombre.".pdf", $headers);
  }

  public function obtenerDocumentosAlumno(Request $request) {
    $request->validate(["alumno_id" => "required"]);
    return response()->json(
      DB::transaction(function () use ($request) {
        $alumnoProyecto = AlumnoProyecto::where("alumno_id", $request->alumno_id)->where("created_at", 
            AlumnoProyecto::where("alumno_id", $request->alumno_id)->max("created_at"))->first();
        $documento = Documento::where("alumno_id", $alumnoProyecto->alumno_id)
            ->where("proyecto_id", $alumnoProyecto->proyecto_id)
            ->get();
        return $respuesta = array("documentos" => $documento, "tipo" => $alumnoProyecto->tipo_proyecto);
      })
    , 200);
  }

  public function obtenerDocumentos(Request $request) {
    $request->validate(["id" => "required"]);
    return response()->json(
      DB::transaction(function () use ($request) {
        $user = User::with("alumno")->where("id", $request->id)->first();
        $lastProyect = DB::table("alumno_proyecto")->where("alumno_id", $user->alumno->id)->orderByRaw("created_at DESC")->limit(1)->first();
        $query = Documento::where("proyecto_id", $lastProyect->proyecto_id)->where("alumno_id", $user->alumno->id)->get();
        $alumnos = array();
        foreach ($query as $documento) {
          $notificacion = false;
          if ($documento == "RECHAZADO") {
            $notificacion = true;
          }
          $localArray = array(
            "id" => $documento->id,
            "nombre" => $documento->nombre,
            "ruta" => $documento->ruta,
            "tipo" => $documento->tipo,
            "estado" => $documento->estado,
            "alumno_id" => $documento->alumno_id,
            "proyecto_id" => $documento->proyecto_id,
            "notificacion" => $notificacion
          );
          array_push($alumnos, $localArray);
        }
        return $alumnos;
      })
    , 200);
  }

  public function modificarEstadoDocumento(Request $request) {
    $request->validate(["estado" => "required", "id" => "required"]);
    DB::transaction(function () use ($request) {
      date_default_timezone_set("America/Mexico_City");
      $fecha = date("Y-m-d H:i:s");
      if ($request->estado == "RECHAZADO") {
        Retroalimentacion::create([
          "mensaje" => $request->mensaje,
          "fecha_envio" => $fecha,
          "documento_id" => $request->id
        ]);
      }
      Documento::where("id", $request->id)->update(["estado" => $request->estado]);
    });
  }

  public function obtenerMensajes(Request $request) {
    $request->validate(["id" => "required"]);
    return response()->json(
      Retroalimentacion::where("documento_id", $request->id)->orderBy("fecha_envio")->get(),
      200
    );
  }

}
