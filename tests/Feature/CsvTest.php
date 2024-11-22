<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CsvTest extends TestCase
{
    // Test para el método index
    public function test_index_returns_valid_csv_files()
    {
        Storage::fake('local');

        // Creamos algunos ficheros en el almacenamiento fake
        Storage::put('app/valid.csv', "header1,header2\nvalue1,value2");
        Storage::put('app/invalid.txt', 'Este no es un archivo CSV válido');

        // Hacemos una solicitud GET al endpoint index
        $response = $this->get('/api/csv');

        // Comprobamos que el estado es 200 y el JSON tiene el resultado esperado
        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Operación exitosa',
                     'contenido' => ['valid.csv']
                 ]);
    }

    // Test para el método store
    public function test_store_creates_new_csv_file()
    {
        Storage::fake('local');

        $data = [
            'filename' => 'newfile.csv',
            'content' => "header1,header2\nvalue1,value2"
        ];

        // Hacemos una solicitud POST para crear el archivo CSV
        $response = $this->post('/api/csv', $data);

        // Comprobamos que el estado es 200 y el archivo se creó correctamente
        $response->assertStatus(200)
                 ->assertJson(['mensaje' => 'Fichero guardado exitosamente']);

        Storage::assertExists('app/newfile.csv');
    }

    // Test para el método update
    public function test_update_modifies_existing_csv_file()
    {
        Storage::fake('local');

        Storage::put('app/existingfile.csv', "header1,header2\nvalue1,value2");

        $data = [
            'filename' => 'existingfile.csv',
            'content' => "header1,header2\nnew_value1,new_value2"
        ];

        // Hacemos una solicitud PUT para actualizar el archivo CSV
        $response = $this->put('/api/csv/existingfile.csv', $data);

        // Comprobamos que el estado es 200 y el archivo se actualizó correctamente
        $response->assertStatus(200)
                 ->assertJson(['mensaje' => 'Fichero actualizado exitosamente']);

        // Comprobamos que el archivo actualizado contiene el nuevo contenido
        Storage::assertExists('app/existingfile.csv');
        $this->assertEquals("header1,header2\nnew_value1,new_value2", Storage::get('app/existingfile.csv'));
    }

    // Test para el método destroy
    public function test_destroy_deletes_existing_file()
    {
        Storage::fake('local');

        Storage::put('app/existingfile.csv', "header1,header2\nvalue1,value2");

        // Hacemos una solicitud DELETE para eliminar el archivo CSV
        $response = $this->delete('/api/csv/existingfile.csv');

        // Comprobamos que el estado es 200 y el archivo se eliminó correctamente
        $response->assertStatus(200)
                 ->assertJson(['mensaje' => 'Fichero eliminado exitosamente']);

        // Verificamos que el archivo ya no existe
        Storage::assertMissing('app/existingfile.csv');
    }

    // Test para el método show (el test que ya te proporcionaron)
    public function test_show_returns_file_content()
    {
        Storage::fake('local');

        Storage::put('app/existingfile.csv', 'header1,header2\nvalue1,value2');

        // Hacemos una solicitud GET al endpoint para mostrar el contenido del archivo CSV
        $response = $this->get('/api/csv/existingfile.csv');

        // Comprobamos que el estado es 200 y que el contenido es correcto
        $response->assertStatus(200)
         ->assertJson([
             'mensaje' => 'Fichero leído con éxito',
             'contenido' => [
                 ['header1' => 'value1', 'header2' => 'value2']
             ]
         ]);
    }
}
