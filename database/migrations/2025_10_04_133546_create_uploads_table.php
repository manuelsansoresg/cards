<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('categorias', function (Blueprint $table) {
             $table->id();
            // nombre (varchar(100), Not Null)
            $table->string('nombre', 100);

            // estado (enum('activo', 'inactivo'), Not Null, Default: 'activo')
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
             $table->timestamps();
        });

        

        Schema::create('stars_settings', function (Blueprint $table) {
             $table->id();
            // stars_per_dollar (int, Not Null, Default: 10)
            $table->integer('stars_per_dollar')->default(10);
             // paypal_client_id (varchar(255), Not Null)
            $table->string('paypal_client_id', 255);

            // paypal_secret (varchar(255), Not Null)
            $table->string('paypal_secret', 255);

            // paypal_mode (enum('sandbox', 'live'), Not Null, Default: 'sandbox')
            $table->enum('paypal_mode', ['sandbox', 'live'])->default('sandbox');

            // header_title (varchar(100), Not Null, Default: 'Video Sellers Club')
            $table->string('header_title', 100)->default('Video Sellers Club');

            // header_image (varchar(255), Not Null, Default: 'uploads/images/default.jpg')
            $table->string('header_image', 255)->default('uploads/images/default.jpg');

            // mercadopago_public_key (varchar(255), Not Null)
            // Added comment for clarity in the migration file
            $table->string('mercadopago_public_key', 255)->comment('Public Key de MercadoPago para procesar pagos');

            // mercadopago_access_token (varchar(255), Not Null)
            // Added comment for clarity in the migration file
            $table->string('mercadopago_access_token', 255)->comment('Access Token de MercadoPago para procesar pagos');

            // mercadopago_mode (enum('sandbox', 'live'), Not Null, Default: 'sandbox')
            // Added comment for clarity in the migration file
            $table->enum('mercadopago_mode', ['sandbox', 'live'])
                  ->default('sandbox')
                  ->comment('Modo de operación de MercadoPago: sandbox para pruebas, live para producción');
            $table->timestamps();
            
        });



        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->index(); 
            // Note: Use 'foreignId' if you plan to define a foreign key relationship later: $table->foreignId('user_id')->constrained();

            // title (varchar(255), Not Null)
            $table->string('title', 255);

            // price (decimal(10,2), Not Null, Default: 0.00)
            $table->decimal('price', 10, 2)->default(0.00);
            // updated_at will be automatically added if you use $table->timestamps();
            // To be precise with the image, we might omit 'updated_at' unless required.

            // stars_cost (int, Not Null, Default: 1)
            $table->integer('stars_cost')->default(1);

            // is_free (tinyint(1), Not Null, Default: 0)
            $table->boolean('is_free')->default(0); 
            // 'boolean' maps to tinyint(1) in MySQL.

            // type (enum('video', 'image'), Not Null, Default: 'video')
            $table->enum('type', ['video', 'image'])->default('video');

            // categoria_id (int, Not Null, Index/Multiple Key)
            $table->integer('categoria_id')->unsigned()->index();
            // Note: Use 'foreignId' if you plan to define a foreign key relationship later: $table->foreignId('categoria_id')->constrained('categorias');

            $table->timestamps();
        });

        Schema::create('media_uploads', function (Blueprint $table) {
            // id (Primary Key, Auto Increment, Not Null)
            // Se utiliza 'id()' para el estándar de Laravel (BIGINT unsigned auto_increment)
            $table->id(); 

            // upload_id (int, Not Null, Index/Multiple Key)
            // Asumiendo que esta es una clave foránea que apunta a la tabla 'uploads'.
            $table->foreignId('upload_id')->constrained('uploads')->onDelete('cascade');
            // Alternativa si no quieres restricción: $table->integer('upload_id')->unsigned()->index();

            // file_path (varchar(255), Not Null)
            $table->string('file_path', 255);

            // file_type (enum('image', 'video'), Not Null, Index/Multiple Key)
            $table->enum('file_type', ['image', 'video'])->index();

            // sort_order (int, Not Null, Index/Multiple Key, Default: 0)
            $table->integer('sort_order')->default(0)->index();

           $table->timestamps();
        });

        Schema::create('carrito', function (Blueprint $table) {
            $table->id();
            // usuario_id (int, Not Null, Index/Multiple Key)
            // It's highly recommended to use foreignId for keys referencing other tables.
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            // If the 'users' table is not standard, use: $table->integer('usuario_id')->unsigned()->index();

            // archivo_id (int, Not Null, Index/Multiple Key)
            // Assuming this references the 'uploads' table you made earlier.
            $table->foreignId('archivo_id')->constrained('uploads')->onDelete('cascade');
            // If the 'uploads' table is not being constrained, use: $table->integer('archivo_id')->unsigned()->index();

            // cantidad (int, Not Null, Default: 1)
            $table->integer('cantidad')->default(1);
            $table->timestamps();
        
        });

        Schema::create('ordenes', function (Blueprint $table) {
            $table->id(); // ID de la Orden

            // Clave Foránea al usuario que realiza la compra
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');

            // ID único de la transacción de pago (PayPal/MercadoPago, etc.)
            $table->string('transaccion_id', 100)->unique()->comment('ID único de la transacción de pago');

            // El monto TOTAL de la orden
            $table->decimal('total_monto', 10, 2);

            // Estado de la orden
            $table->enum('estado', ['pendiente', 'aprobado', 'fallido'])->default('pendiente');

            // Método de pago utilizado
            $table->enum('metodo_pago', ['paypal', 'mercadopago']);

            // Email del cliente, si es diferente al del usuario registrado
            $table->string('email', 255)->nullable();

            // created_at y updated_at con la convención de Laravel
            $table->timestamps(); 
        });

        Schema::create('detalles_orden', function (Blueprint $table) {
            $table->id();

            // Clave Foránea a la tabla 'ordenes' (para agrupar los archivos)
            $table->foreignId('orden_id')
                  ->constrained('ordenes')
                  ->onDelete('cascade')
                  ->comment('Referencia a la orden principal');

            // Clave Foránea al archivo comprado (de la tabla 'uploads')
            $table->foreignId('archivo_id')
                  ->constrained('uploads')
                  ->onDelete('cascade')
                  ->comment('Referencia al archivo comprado');

            // Precio al que se vendió el archivo en el momento de la compra
            $table->decimal('precio_unitario', 10, 2)->comment('Precio del archivo al momento de la compra');

            // Cantidad comprada (generalmente 1 para archivos digitales)
            $table->integer('cantidad')->default(1);

            // Solo necesitamos la fecha de creación, ya que el updated_at se gestiona en la orden principal
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('stars_settings');
        Schema::dropIfExists('uploads');
        Schema::dropIfExists('media_uploads');
        Schema::dropIfExists('carrito');
        Schema::dropIfExists('ordenes');
        Schema::dropIfExists('detalles_orden');
    }
}
