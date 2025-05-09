<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    /* 'odoo_id' => $userData['id'],
            'password' => bcrypt($this->password), // Evita guardar contraseñas reales
            'price_list_id' => $userData['price_list'][0], // Asumiendo que tienes un campo price_list_id en tu tabla users
            'price_list_name */

    Schema::table('users', function (Blueprint $table) {
      //$table->integer('codigo_empleado')->nullable();
      $table->integer('odoo_id')->nullable();
      $table->integer('price_list_id')->nullable();
      $table->string('price_list_name')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('users', function (Blueprint $table) {
      //$table->dropColumn('codigo_empleado');
      $table->dropColumn('odoo_id');
      $table->dropColumn('price_list_id');
      $table->dropColumn('price_list_name');
    });
  }
};
