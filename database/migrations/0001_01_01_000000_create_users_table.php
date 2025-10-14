<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::table('regions')->insert([
            ['code' => 'magazyn', 'name' => 'Magazyn', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'sklep', 'name' => 'Sklep', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'garmaz', 'name' => 'Garmaż', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'piekarnia', 'name' => 'Piekarnia', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['pracownik','ksiegowy','kierownik','admin'])->default('pracownik');
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
        
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });



        //Tabele
        //////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////


        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::table('units')->insert([
            ['code' => 'szt', 'name' => 'sztuka', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'kg',  'name' => 'kilogram', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'l',   'name' => 'litr', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'opak','name' => 'opakowanie', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('id_abaco')->nullable()->index();
            $table->string('name', 255)->collation('Latin1_General_100_CI_AS_SC_UTF8');
            $table->timestamps();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
        });

        // EAN codes table
        Schema::create('barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('barcode', 13);
            $table->timestamps();
        });
        

        Schema::create('product_prices_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('price', 15, 2);
            $table->timestamp('changed_at')->useCurrent();
        });


        Schema::create('faktury', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('data_wystawienia');
            $table->date('data_sprzedazy')->nullable();
            $table->text('notes')->nullable();

            // Dodanie regionu
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();

            $table->timestamps();
        });


        Schema::create('faktury_produkty', function (Blueprint $table) {
            $table->id();

            // powiązanie z fakturą
            $table->foreignId('faktura_id')->constrained('faktury')->cascadeOnDelete();

            // opcjonalne powiązanie z istniejącym produktem
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            // dane fakturowe (zawsze muszą zostać)
            $table->string('name'); // nazwa produktu w momencie faktury
            $table->decimal('price_net', 15, 2); // cena netto – obowiązkowa
            $table->decimal('price_gross', 15, 2)->nullable(); // cena brutto – może być null
            $table->decimal('vat', 5, 2)->nullable(); // VAT w procentach – może być null
            $table->decimal('quantity', 15, 2)->default(1);
            $table->string('unit'); // jednostka, np. "kg", "szt"
            $table->string('barcode', 13)->nullable();

            $table->timestamps();

            $table->index(['faktura_id']);
            $table->index(['product_id']);
        });


        Schema::create('produkt_skany', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('region_id')->constrained('regions');
            $table->decimal('price_history', 15, 2);
            $table->decimal('quantity', 15, 2)->default(1);
            $table->decimal('used_quantity', 15, 2)->default(0);
            $table->timestamp('scanned_at')->useCurrent();
            $table->string('barcode', 13)->nullable();
            
            $table->index(['product_id', 'region_id']);
            $table->index(['user_id', 'scanned_at']);
        });

        Schema::create('spis_z_natury', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('regions');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['region_id', 'user_id']);
        });

        Schema::create('spis_produkty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spis_id')->constrained('spis_z_natury')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('name');          // nazwa produktu
            $table->decimal('price', 15, 2); // cena produktu
            $table->decimal('quantity', 15, 2)->default(1);
            $table->string('unit');           // jednostka, np. "kg", "szt"
            $table->string('barcode', 13)->nullable();
            $table->timestamp('added_at')->useCurrent();

            $table->index(['spis_id']);
            $table->index(['user_id', 'added_at']);
        });

        Schema::create('spis_produkty_tmp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spis_id');
            $table->foreignId('user_id');
            $table->foreignId('product_id');
            $table->foreignId('region_id');
            $table->foreignId('produkt_skany_id');
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->decimal('quantity', 12, 2);
            $table->string('unit')->nullable();
            $table->string('barcode')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamp('added_at')->nullable();
            $table->timestamps();

        });


        Schema::create('produkty_filtr_tmp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('region_id')->constrained('regions');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('produkt_skany_id')->constrained('produkt_skany');
            $table->string('name');
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('quantity', 15, 2)->default(0);
            $table->string('unit')->nullable();
            $table->string('barcode', 50)->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'region_id']);
        });


        Schema::create('imported_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions');
            $table->string('dostawca')->nullable();          
            $table->string('artykul')->nullable();           
            $table->decimal('ilosc', 12, 2)->nullable();     
            $table->decimal('cena_netto', 12, 2)->nullable(); 
            $table->decimal('cena_brutto', 12, 2)->nullable(); 
            $table->decimal('wartosc_netto', 12, 2)->nullable(); 
            $table->decimal('wartosc_brutto', 12, 2)->nullable(); 
            $table->decimal('co_to', 12, 2)->nullable();
            $table->string('vat', 10)->nullable();    
            $table->decimal('co_to_dwa', 12, 2)->nullable();       
            $table->string('ean', 50)->nullable();          
            $table->string('kod', 50)->nullable();           
            $table->string('powod')->nullable();           
            $table->timestamp('imported_at')->useCurrent();
        });








    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imported_records');
        Schema::dropIfExists('spis_produkty');
        Schema::dropIfExists('spis_z_natury');
        Schema::dropIfExists('produkt_skany');
        Schema::dropIfExists('produkty_filtr_tmp');
        Schema::dropIfExists('spis_produkty_tmp');
        Schema::dropIfExists('barcodes');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('users');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');

    }
};
