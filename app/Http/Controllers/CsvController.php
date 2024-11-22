<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class CsvController extends Controller
{
    /**
     * Lista todos los ficheros CSV de la carpeta storage/app.
     *
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     * - contenido: Un array con los nombres de los ficheros.
     */
    public function index(): JsonResponse
    {
        $files = Storage::files('app');
        $csvFiles = [];

        foreach ($files as $file) {
            if (str_ends_with($file, '.csv')) {
                $csvFiles[] = basename($file);
            }
        }

        return response()->json([
            'mensaje' => 'Operación exitosa',
            'contenido' => $csvFiles
        ]);
    }

    /**
     * Recibe por parámetro el nombre de fichero y el contenido CSV y crea un nuevo fichero con ese nombre y contenido en storage/app. 
     * Devuelve un JSON con el resultado de la operación.
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
        $request->validate([
            'filename' => 'required|string',
            'content' => 'required|string',
        ]);

        $filename = $request->input('filename');
        $content = $request->input('content');

        if (Storage::exists("app/$filename")) {
            return response()->json(['mensaje' => 'El fichero ya existe'], 409);
        }

        Storage::put("app/$filename", $content);

        return response()->json(['mensaje' => 'Fichero guardado exitosamente']);
    }

    /**
     * Recibe por parámetro el nombre de un fichero CSV el nombre de fichero y devuelve un JSON con su contenido.
     * Si el fichero no existe devuelve un 404.
     * Hay que hacer uso lo visto en la presentación CSV to JSON.
     *
     * @param name Parámetro con el nombre del fichero CSV.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     * - contenido: El contenido del fichero si se ha leído con éxito.
     */
    public function show(string $id): JsonResponse
{
    // Verificar si el archivo existe
    if (!Storage::exists("app/$id")) {
        return response()->json(['mensaje' => 'El fichero no existe'], 404);
    }

    // Obtener el contenido del archivo CSV
    $content = Storage::get("app/$id");

    // Dividir el contenido en líneas
    $lines = array_map('trim', explode("\n", $content));

    // Verificar si el archivo tiene al menos una línea de encabezado y una línea de datos
    if (count($lines) < 2) {
        return response()->json(['mensaje' => 'Contenido del archivo no es suficiente para leer'], 415);
    }

    // Obtener encabezados
    $headers = str_getcsv($lines[0]);

    // Procesar las líneas restantes como datos
    $data = [];
    foreach (array_slice($lines, 1) as $line) {
        if (!empty($line)) {
            $values = str_getcsv($line);
            // Verificar si el número de columnas coincide con los encabezados
            if (count($values) === count($headers)) {
                $data[] = array_combine($headers, $values);
            }
        }
    }

    // Verificar si se encontraron datos válidos
    if (empty($data)) {
        return response()->json(['mensaje' => 'Contenido no es válido o no se encontraron datos'], 415);
    }

    // Retornar la respuesta con los datos procesados
    return response()->json([
        'mensaje' => 'Fichero leído con éxito',
        'contenido' => $data
    ], 200);
}




    /**
     * Recibe por parámetro el nombre de fichero, el contenido CSV y actualiza el fichero CSV. 
     * Devuelve un JSON con el resultado de la operación.
     * Si el fichero no existe devuelve un 404.
     * Si el contenido no es un JSON válido, devuelve un 415.
     * 
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @param content Contenido del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        if (!Storage::exists("app/$id")) {
            return response()->json(['mensaje' => 'El fichero no existe'], 404);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $content = $request->input('content');

        Storage::put("app/$id", $content);

        return response()->json(['mensaje' => 'Fichero actualizado exitosamente']);
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
    public function destroy(string $id): JsonResponse
    {
        if (!Storage::exists("app/$id")) {
            return response()->json(['mensaje' => 'El fichero no existe'], 404);
        }

        Storage::delete("app/$id");

        return response()->json(['mensaje' => 'Fichero eliminado exitosamente']);
    }
}
