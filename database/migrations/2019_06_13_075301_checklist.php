<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CheckList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklist', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('object_domain')->nullable();
            $table->string('object_id')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_completed')->default(0);
            $table->dateTime('completed_at')->nullable();
            $table->integer('updated_by')->nullable();
            $table->dateTime('due')->nullable();
            $table->integer('due_interval')->nullable();
            $table->string('due_unit')->nullable();
            $table->integer('urgency')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
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
        Schema::drop('checklist');        
    }
}
