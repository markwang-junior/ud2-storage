<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CsvTest extends TestCase
{
    public function test_index_returns_valid_csv_files()
    {
        Storage::fake('local');

        Storage::put('app/valid.csv', 'header1,header2\nvalue1,value2');
        Storage::put('app/invalid.txt', 'This is not a CSV file');

        $response = $this->get('/api/csv');

        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Operación exitosa',
                     'contenido' => ['valid.csv']
                 ]);
    }

    public function test_store_creates_new_csv_file()
    {
        Storage::fake('local');

        $data = [
            'filename' => 'newfile.csv',
            'content' => "header1,header2\nvalue1,value2"
        ];

        $response = $this->post('/api/csv', $data);

        $response->assertStatus(201)
                 ->assertJson(['mensaje' => 'Fichero guardado exitosamente']);

        Storage::assertExists('app/newfile.csv');
    }

    public function test_show_returns_file_content()
    {
        Storage::fake('local');

        Storage::put('app/existingfile.csv', "header1,header2\nvalue1,value2");

        $response = $this->get('/api/csv/existingfile.csv');

        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Fichero leído con éxito',
                     'contenido' => [
                         ['header1' => 'value1', 'header2' => 'value2']
                     ]
                 ]);
    }

    public function test_update_modifies_existing_file()
    {
        Storage::fake('local');

        Storage::put('app/existingfile.csv', "header1,header2\nvalue1,value2");

        $data = [
            'filename' => 'existingfile.csv',
            'content' => "header1,header2\nnewvalue1,newvalue2"
        ];

        $response = $this->put('/api/csv/existingfile.csv', $data);

        $response->assertStatus(200)
                 ->assertJson(['mensaje' => 'Fichero actualizado exitosamente']);

        Storage::assertExists('app/existingfile.csv');
        $this->assertEquals("header1,header2\nnewvalue1,newvalue2", Storage::get('app/existingfile.csv'));
    }

    public function test_destroy_deletes_existing_file()
    {
        Storage::fake('local');

        Storage::put('app/existingfile.csv', "header1,header2\nvalue1,value2");

        $response = $this->delete('/api/csv/existingfile.csv');

        $response->assertStatus(200)
                 ->assertJson(['mensaje' => 'Fichero eliminado exitosamente']);

        Storage::assertMissing('app/existingfile.csv');
    }

}