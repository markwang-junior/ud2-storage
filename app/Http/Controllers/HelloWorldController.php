<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HelloWorldController extends Controller
{
    /**
     * Lista todos los ficheros de la carpeta storage/app.
     *
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     * - contenido: Un array con los nombres de los ficheros.
     */
    public function index(): JsonResponse
    {
        // Lista todos los ficheros en el almacenamiento local
        $files = Storage::disk('local')->files();

        return response()->json([
            'mensaje' => 'Listado de ficheros',
            'contenido' => $files,
        ]);
    }

    /**
     * Recibe por parámetro el nombre de fichero y el contenido. Devuelve un JSON con el resultado de la operación.
     * Si el fichero ya existe, devuelve un 409.
     *
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @param content Contenido del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */
    public function store(Request $request): JsonResponse
    {
        // Valida que los parámetros 'filename' y 'content' sean proporcionados y sean cadenas
        $request->validate([
            'filename' => 'required|string',
            'content' => 'required|string',
        ]);

        // Toma el nombre del archivo y el contenido de la solicitud
        $filename = $request->input('filename');
        $content = $request->input('content');

        // Verifica si el archivo ya existe en el almacenamiento
        if (Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'El archivo ya existe',
            ], 409);
        }

        // Guarda el archivo con el contenido proporcionado
        Storage::disk('local')->put($filename, $content);

        return response()->json([
            'mensaje' => 'Guardado con éxito',
        ]);
    }

    /**
     * Recibe por parámetro el nombre de fichero y devuelve un JSON con su contenido.
     *
     * @param name Parámetro con el nombre del fichero.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     * - contenido: El contenido del fichero si se ha leído con éxito.
     */
    public function show(string $filename): JsonResponse
    {
        // Verifica si el archivo existe en el almacenamiento
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'Archivo no encontrado',
            ], 404);
        }

        // Obtiene el contenido del archivo
        $content = Storage::disk('local')->get($filename);

        return response()->json([
            'mensaje' => 'Archivo leído con éxito',
            'contenido' => $content,
        ]);
    }

    /**
     * Recibe por parámetro el nombre de fichero, el contenido y actualiza el fichero.
     * Devuelve un JSON con el resultado de la operación.
     * Si el fichero no existe devuelve un 404.
     *
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @param content Contenido del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */
    public function update(Request $request, string $filename): JsonResponse
    {
        // Valida que el parámetro 'content' sea proporcionado y sea una cadena
        $request->validate([
            'content' => 'required|string',
        ]);

        // Verifica si el archivo existe en el almacenamiento
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'El archivo no existe',
            ], 404);
        }

        // Toma el nuevo contenido de la solicitud
        $content = $request->input('content');
        
        // Actualiza el archivo con el nuevo contenido
        Storage::disk('local')->put($filename, $content);

        return response()->json([
            'mensaje' => 'Actualizado con éxito',
        ]);
    }

    /**
     * Recibe por parámetro el nombre de fichero y lo elimina.
     * Si el fichero no existe devuelve un 404.
     *
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */
    public function destroy(string $filename): JsonResponse
    {
        // Verifica si el archivo existe en el almacenamiento
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'El archivo no existe',
            ], 404);
        }

        // Elimina el archivo del almacenamiento
        Storage::disk('local')->delete($filename);

        return response()->json([
            'mensaje' => 'Eliminado con éxito',
        ]);
    }
}
