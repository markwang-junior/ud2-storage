<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HelloWorldController extends Controller
{
    public function index(): JsonResponse
    {
        $files = Storage::disk('local')->files();

        return response()->json([
            'mensaje' => 'Listado de ficheros',
            'contenido' => $files,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'filename' => 'required|string',
            'content' => 'required|string',
        ]);

        $filename = $request->input('filename');
        $content = $request->input('content');

        if (Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'El archivo ya existe',
            ], 409);
        }

        Storage::disk('local')->put($filename, $content);

        return response()->json([
            'mensaje' => 'Guardado con éxito',
        ]);
    }

    public function show(string $filename): JsonResponse
    {
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'Archivo no encontrado',
            ], 404);
        }

        $content = Storage::disk('local')->get($filename);

        return response()->json([
            'mensaje' => 'Archivo leído con éxito',
            'contenido' => $content,
        ]);
    }

    public function update(Request $request, string $filename): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        if (!Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'El archivo no existe',
            ], 404);
        }

        $content = $request->input('content');
        Storage::disk('local')->put($filename, $content);

        return response()->json([
            'mensaje' => 'Actualizado con éxito',
        ]);
    }

    public function destroy(string $filename): JsonResponse
    {
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'El archivo no existe',
            ], 404);
        }

        Storage::disk('local')->delete($filename);

        return response()->json([
            'mensaje' => 'Eliminado con éxito',
        ]);
    }
}
