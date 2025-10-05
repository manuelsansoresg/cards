<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('reactions', function (Blueprint $table) {
            // ID (Primary Key, Auto Increment, Not Null)
            $table->id(); 

            // user_id (int, Not Null, Foreign Key)
            $table->foreignId('user_id')
                  ->constrained('users') // Asume la tabla 'users'
                  ->onDelete('cascade');

            // media_id (int, Not Null, Foreign Key)
            // Usamos 'upload_id' para mantener la convención con la tabla 'uploads'
            $table->foreignId('upload_id') 
                  ->constrained('uploads') // Asume la tabla 'uploads' de migraciones previas
                  ->onDelete('cascade');
            
            // reaction (varchar(10), Not Null)
            // Puedes usar este campo para almacenar 'like', 'love', 'haha', etc.
            $table->string('reaction', 10); 

            // Agregando una restricción UNIQUE para asegurar que un usuario solo pueda 
            // reaccionar una vez a un mismo upload (opcional, pero buena práctica)
            $table->unique(['user_id', 'upload_id']);

            // Timestamps (created_at y updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reactions');
    }
}
