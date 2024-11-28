<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class CsvController extends Controller
{
    /**
     * Lista todos los ficheros CSV disponibles en el almacenamiento.
     *
     * @return JsonResponse Respuesta JSON con el listado de archivos CSV.
     */
    public function index(): JsonResponse
    {
        // Obtiene todos los archivos en el directorio storage/app.
        $files = Storage::files();

        // Filtra los archivos que tienen extensión .csv.
        $csvFiles = array_filter($files, fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'csv');

        // Devuelve un JSON con el listado de archivos CSV.
        return response()->json([
            'mensaje' => 'Listado de ficheros',
            'contenido' => array_values($csvFiles), // Limpia los índices para una respuesta más clara.
        ], 200);
    }

    /**
     * Crea un nuevo fichero CSV con el contenido proporcionado.
     *
     * @param Request $request Objeto de la solicitud con los datos necesarios.
     * @return JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function store(Request $request): JsonResponse
    {
        // Obtiene los parámetros del request.
        $filename = $request->input('filename');
        $content = $request->input('content');

        // Verifica que los parámetros sean válidos.
        if (!$filename || !$content) {
            return response()->json(['mensaje' => 'Faltan parámetros'], 422);
        }

        // Verifica si el archivo ya existe.
        if (Storage::exists($filename)) {
            return response()->json(['mensaje' => 'El fichero ya existe'], 409);
        }

        // Guarda el archivo con el contenido proporcionado.
        Storage::put($filename, $content);

        // Devuelve un mensaje de éxito.
        return response()->json(['mensaje' => 'Guardado con éxito'], 200);
    }

    /**
     * Muestra el contenido de un fichero CSV en formato JSON.
     *
     * @param string $id Nombre del fichero a mostrar.
     * @return JsonResponse Respuesta JSON con el contenido del archivo.
     */
    public function show(string $id)
    {
        // Construye la ruta completa del archivo.
        $path = "app/{$id}";

        // Verifica si el archivo existe en el almacenamiento.
        if (!Storage::exists($path)) {
            return response()->json(['mensaje' => 'Fichero no encontrado'], 404);
        }

        // Obtiene el contenido del archivo.
        $content = Storage::get($path);

        // Divide el contenido en líneas.
        $lines = explode("\n", trim($content));

        // Verifica que haya datos válidos en el archivo.
        if (count($lines) < 2) {
            return response()->json([
                'mensaje' => 'El fichero no contiene datos válidos',
                'contenido' => [],
            ]);
        }

        // Procesa la primera línea como encabezados.
        $headers = str_getcsv(array_shift($lines));

        // Combina los encabezados con las filas para generar datos estructurados.
        $data = array_map(fn($line) => array_combine($headers, str_getcsv($line)), $lines);

        // Devuelve el contenido del archivo en formato JSON.
        return response()->json([
            'mensaje' => 'Fichero leído con éxito',
            'contenido' => $data,
        ]);
    }

    /**
     * Actualiza el contenido de un fichero CSV existente.
     *
     * @param Request $request Objeto de la solicitud con los nuevos datos.
     * @param string $id Nombre del fichero a actualizar.
     * @return JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function update(Request $request, string $id)
    {
        // Construye la ruta completa del archivo.
        $path = "app/{$id}";

        // Verifica si el archivo existe.
        if (!Storage::exists($path)) {
            return response()->json(['mensaje' => 'Fichero no encontrado'], 404);
        }

        // Obtiene el contenido del request.
        $content = $request->input('content');

        // Verifica que el contenido sea válido.
        if (!$content || !is_string($content)) {
            return response()->json(['mensaje' => 'Contenido inválido'], 422);
        }

        // Sobrescribe el contenido del archivo.
        Storage::put($path, $content);

        // Devuelve un mensaje indicando que la actualización fue exitosa.
        return response()->json(['mensaje' => 'Fichero actualizado exitosamente']);
    }

    /**
     * Elimina un fichero CSV del almacenamiento.
     *
     * @param string $id Nombre del fichero a eliminar.
     * @return JsonResponse Respuesta JSON indicando el resultado de la operación.
     */
    public function destroy(string $id)
    {
        // Construye la ruta completa del archivo.
        $path = "app/{$id}";

        // Verifica si el archivo existe.
        if (!Storage::exists($path)) {
            return response()->json(['mensaje' => 'Fichero no encontrado'], 404);
        }

        // Elimina el archivo del almacenamiento.
        Storage::delete($path);

        // Devuelve un mensaje indicando que el archivo fue eliminado.
        return response()->json(['mensaje' => 'Fichero eliminado exitosamente']);
    }
}
