<?php

namespace App\core;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class Initializer
{

    public function __construct()
    {
        $this->initializer();
    }

    private function initializer(): void
    {
        /** create essential database tables **/
        $this->create_essential_tables();
    }

    private function create_essential_tables(): void
    {
        /** create table users if not exist **/
        if (!Capsule::schema()->hasTable('users')) {
            Capsule::schema()->create('users', function (Blueprint $table) {
                $table->bigInteger('user_id', true, true);
                $table->string('user_login', 200)->index();
                $table->string('user_nicename', 100)->index();
                $table->string('user_email', 100)->index();
                $table->string('user_pass', 255);
                $table->tinyInteger("user_status")->default(0);
                $table->tinyInteger("mobile_verify")->default(0);
                $table->tinyInteger("email_verify")->default(0);
                $table->string("email_token", 255)->nullable();
                $table->string("mobile_token", 20)->nullable();
                $table->dateTime("mobile_token_exp")->nullable();
                $table->tinyInteger('mobile_token_attempts')->default(3);
                $table->timestamps();
            });
        }

        /** create table user meta if not exist **/
        if (!Capsule::schema()->hasTable('user_meta')) {
            Capsule::schema()->create('user_meta', function (Blueprint $table) {
                $table->bigInteger('umeta_id', true, true);
                $table->bigInteger('user_id')->index();
                $table->string('meta_key', 255)->index();
                $table->longText('meta_value');
                $table->timestamps();
            });
        }

        /** create table forms if not exist **/
        if (!Capsule::schema()->hasTable('forms')) {
            Capsule::schema()->create('forms', function (Blueprint $table) {
                $table->bigInteger('form_id', true, true);
                $table->bigInteger('user_id')->index();
                $table->tinyInteger('form_status')->default(0);
                $table->timestamps();
            });
        }

        /** create table form meta if not exist **/
        if (!Capsule::schema()->hasTable('form_meta')) {
            Capsule::schema()->create('form_meta', function (Blueprint $table) {
                $table->bigInteger('fmeta_id', true, true);
                $table->bigInteger('form_id')->index();
                $table->string('meta_key', 255)->index();
                $table->longText('meta_value');
                $table->timestamps();
            });
        }

        /** create table media if not exist **/
        if (!Capsule::schema()->hasTable("media")) {
            Capsule::schema()->create("media", function (Blueprint $table) {
                $table->bigInteger('media_id', true, true);
                $table->bigInteger('user_id')->index();
                $table->string('media_name', 250);
                $table->string('media_extension', 20);
                $table->longText('media_link');
                $table->timestamps();
            });
        }

        /** create table media meta if not exist **/
        if (!Capsule::schema()->hasTable('media_meta')) {
            Capsule::schema()->create('media_meta', function (Blueprint $table) {
                $table->bigInteger('mmeta_id', true, true);
                $table->bigInteger('media_id')->index();
                $table->string('meta_key', 255)->index();
                $table->longText('meta_value');
                $table->timestamps();
            });
        }

    }

}